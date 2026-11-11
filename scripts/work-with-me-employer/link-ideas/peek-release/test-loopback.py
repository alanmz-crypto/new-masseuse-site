#!/usr/bin/env python3
"""Synthetic local TCP tests; no WordPress, Docker, site or external requests."""
import concurrent.futures
import importlib.util
import os
from pathlib import Path
import select
import socket
import struct
import subprocess
import sys
import threading
import time
import unittest

ROOT = Path(__file__).resolve().parents[4]
SOURCE = ROOT / "scripts/ci/rehearsal-loopback.py"
spec = importlib.util.spec_from_file_location("rehearsal_loopback", SOURCE)
relay = importlib.util.module_from_spec(spec)
spec.loader.exec_module(relay)


def receive_all(connection):
    result = bytearray()
    while True:
        data = connection.recv(65536)
        if not data:
            return bytes(result)
        result.extend(data)


def send_fragments(connection, data):
    sizes = (1, 17, 4093, 65536, 3, 8191)
    offset = 0
    while offset < len(data):
        for size in sizes:
            connection.sendall(data[offset:offset + size])
            offset += size
            if offset >= len(data):
                break


class Backend:
    def __init__(self, handler):
        self.listener = socket.socket()
        self.listener.bind(("127.0.0.1", 0))
        self.listener.listen()
        self.listener.settimeout(5)
        self.address = self.listener.getsockname()
        self.error = None
        self.done = threading.Event()
        def run():
            try:
                with self.listener.accept()[0] as connection:
                    connection.settimeout(5)
                    handler(connection)
            except BaseException as error:
                self.error = error
            finally:
                self.listener.close()
                self.done.set()
        self.thread = threading.Thread(target=run, daemon=True)
        self.thread.start()

    def finish(self):
        if not self.done.wait(7):
            raise AssertionError("synthetic backend did not finish")
        if self.error:
            raise self.error


class RunningRelay:
    def __init__(self, target, idle=2):
        self.server = relay.RelayServer(("127.0.0.1", 0), target, idle)
        self.address = self.server.server_address
        self.thread = threading.Thread(target=self.server.serve_forever,
                                       kwargs={"poll_interval": 0.02}, daemon=True)
        self.thread.start()

    def close(self):
        self.server.shutdown()
        self.server.server_close()
        self.thread.join(2)


class LoopbackTests(unittest.TestCase):
    def test_production_address_rules(self):
        for address in ("10.0.0.2", "172.16.0.2", "172.31.255.254", "192.168.0.2"):
            with self.subTest(accepted=address):
                self.assertEqual(relay.target_address([address]), (address, 80))
        invalid = ([], ["10.0.0.2", "80"], ["--help"], ["localhost"], ["127.0.0.1"],
                   ["0.0.0.0"], ["169.254.1.2"], ["224.0.0.1"], ["255.255.255.255"],
                   ["240.0.0.1"], ["8.8.8.8"], ["172.15.1.1"], ["172.32.1.1"],
                   ["192.0.2.1"], ["100.64.0.1"], ["::1"], ["::ffff:10.0.0.2"],
                   ["10.0.0.2:80"], ["http://10.0.0.2"], ["010.0.0.2"], ["10.0.0.2 "],
                   [" 10.0.0.2"], ["10.0.0.2\n"], ["10.0.0.256"])
        for arguments in invalid:
            with self.subTest(refused=arguments):
                self.assertRaises(ValueError, relay.target_address, arguments)
                result = subprocess.run([sys.executable, "-I", "-B", str(SOURCE), *arguments],
                                        capture_output=True, timeout=3)
                self.assertEqual(result.returncode, 2)
                self.assertNotIn(b"READY", result.stdout)
        self.assertEqual(relay.LISTEN, ("127.0.0.1", 8090))
        self.assertTrue(relay.RelayServer.daemon_threads)

    def test_production_bind_collision_and_clean_stop(self):
        with socket.socket() as occupied:
            occupied.bind(relay.LISTEN)
            occupied.listen()
            result = subprocess.run([sys.executable, "-I", "-B", str(SOURCE), "172.20.0.2"],
                                    capture_output=True, timeout=3)
            self.assertEqual(result.returncode, 2)
            self.assertNotIn(b"READY", result.stdout)
        process = subprocess.Popen([sys.executable, "-I", "-B", str(SOURCE), "172.20.0.2"],
                                   stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        try:
            self.assertTrue(select.select([process.stdout], [], [], 3)[0])
            self.assertEqual(process.stdout.readline(),
                             b"READY rehearsal loopback 127.0.0.1:8090 -> 172.20.0.2:80\n")
            process.terminate()
            self.assertEqual(process.wait(3), 0)
            self.assertEqual(process.stderr.read(), b"")
        finally:
            if process.poll() is None:
                process.kill()
                process.wait(3)
            process.stdout.close()
            process.stderr.close()
        with socket.socket() as again:
            again.bind(relay.LISTEN)

    def test_large_fragmented_request_then_half_closed_response(self):
        request = bytes(range(256)) * 16384 + b"\x00END\xff"
        response = os.urandom(2 * 1024 * 1024 + 79)
        observed = []
        def backend(connection):
            observed.append(receive_all(connection))
            send_fragments(connection, response)
            connection.shutdown(socket.SHUT_WR)
        back = Backend(backend)
        running = RunningRelay(back.address)
        try:
            with socket.create_connection(running.address, timeout=5) as client:
                send_fragments(client, request)
                client.shutdown(socket.SHUT_WR)
                self.assertEqual(receive_all(client), response)
            back.finish()
            self.assertEqual(observed, [request])
        finally:
            running.close()

    def test_simultaneous_full_duplex_with_backpressure(self):
        request = os.urandom(3 * 1024 * 1024 + 11)
        response = os.urandom(3 * 1024 * 1024 + 23)
        observed = []
        def backend(connection):
            def send():
                send_fragments(connection, response)
                connection.shutdown(socket.SHUT_WR)
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                writer = pool.submit(send)
                observed.append(receive_all(connection))
                writer.result(timeout=5)
        back = Backend(backend)
        running = RunningRelay(back.address)
        try:
            with socket.create_connection(running.address, timeout=5) as client:
                def send():
                    send_fragments(client, request)
                    client.shutdown(socket.SHUT_WR)
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
                    writer = pool.submit(send)
                    self.assertEqual(receive_all(client), response)
                    writer.result(timeout=5)
            back.finish()
            self.assertEqual(observed, [request])
        finally:
            running.close()

    def test_backend_half_close_still_accepts_client_bytes(self):
        request = os.urandom(512 * 1024)
        observed = []
        def backend(connection):
            connection.sendall(b"backend finished sending")
            connection.shutdown(socket.SHUT_WR)
            observed.append(receive_all(connection))
        back = Backend(backend)
        running = RunningRelay(back.address)
        try:
            with socket.create_connection(running.address, timeout=5) as client:
                self.assertEqual(receive_all(client), b"backend finished sending")
                send_fragments(client, request)
                client.shutdown(socket.SHUT_WR)
            back.finish()
            self.assertEqual(observed, [request])
        finally:
            running.close()

    def test_idle_expiry_and_unavailable_backend(self):
        closed = threading.Event()
        def backend(connection):
            self.assertEqual(connection.recv(1), b"")
            closed.set()
        back = Backend(backend)
        running = RunningRelay(back.address, idle=0.15)
        try:
            with socket.create_connection(running.address, timeout=3) as client:
                started = time.monotonic()
                self.assertEqual(client.recv(1), b"")
                self.assertLess(time.monotonic() - started, 2)
            back.finish()
            self.assertTrue(closed.is_set())
        finally:
            running.close()
        with socket.socket() as unused:
            unused.bind(("127.0.0.1", 0))
            address = unused.getsockname()
        running = RunningRelay(address)
        try:
            for _ in range(2):
                with socket.create_connection(running.address, timeout=3) as client:
                    self.assertEqual(client.recv(1), b"")
            self.assertTrue(running.thread.is_alive())
        finally:
            running.close()

    def test_backend_reset_does_not_stop_listener(self):
        def backend(connection):
            connection.setsockopt(socket.SOL_SOCKET, socket.SO_LINGER, struct.pack("ii", 1, 0))
        back = Backend(backend)
        running = RunningRelay(back.address)
        try:
            with socket.create_connection(running.address, timeout=3) as client:
                try:
                    self.assertEqual(client.recv(1), b"")
                except ConnectionResetError:
                    pass
            back.finish()
            self.assertTrue(running.thread.is_alive())
        finally:
            running.close()


if __name__ == "__main__":
    unittest.main(verbosity=2)
