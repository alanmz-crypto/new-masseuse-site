#!/usr/bin/env python3
"""Host-only access to an isolated rehearsal container; no routable listener."""
import ipaddress
import selectors
import signal
import socket
import socketserver
import sys
import time

LISTEN = ("127.0.0.1", 8090)
PRIVATE = tuple(ipaddress.IPv4Network(value) for value in
                ("10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16"))
BUFFER_LIMIT = 256 * 1024
IDLE_SECONDS = 60


def target_address(arguments):
    if len(arguments) != 1:
        raise ValueError("exactly one private Docker IPv4 address is required")
    address = ipaddress.IPv4Address(arguments[0])
    if str(address) != arguments[0] or not any(address in network for network in PRIVATE):
        raise ValueError("target must be a canonical RFC1918 IPv4 address")
    return str(address), 80


def forward(client, target, idle_seconds=IDLE_SECONDS):
    """Forward both directions with backpressure and independent TCP half-close."""
    with socket.create_connection(target, timeout=5) as upstream, selectors.DefaultSelector() as selector:
        peers = (client, upstream)
        other = {client: upstream, upstream: client}
        pending = {client: bytearray(), upstream: bytearray()}
        readable = set(peers)
        ended = set()
        for peer in peers:
            peer.setblocking(False)
        activity = time.monotonic()
        while readable or any(pending.values()):
            for peer in peers:
                events = (selectors.EVENT_READ if peer in readable and
                          len(pending[other[peer]]) < BUFFER_LIMIT else 0)
                if pending[peer]:
                    events |= selectors.EVENT_WRITE
                try:
                    selector.get_key(peer)
                except KeyError:
                    if events:
                        selector.register(peer, events)
                else:
                    if events:
                        selector.modify(peer, events)
                    else:
                        selector.unregister(peer)
            remaining = idle_seconds - (time.monotonic() - activity)
            if remaining <= 0:
                return
            events = selector.select(remaining)
            if not events:
                return
            for key, flags in events:
                peer = key.fileobj
                if flags & selectors.EVENT_READ:
                    try:
                        data = peer.recv(min(65536, BUFFER_LIMIT - len(pending[other[peer]])))
                    except BlockingIOError:
                        data = None
                    if data:
                        pending[other[peer]].extend(data)
                        activity = time.monotonic()
                    elif data == b"":
                        readable.remove(peer)
                if flags & selectors.EVENT_WRITE:
                    try:
                        sent = peer.send(pending[peer])
                    except BlockingIOError:
                        sent = 0
                    if sent:
                        del pending[peer][:sent]
                        activity = time.monotonic()
            for source in peers:
                destination = other[source]
                if source not in readable and not pending[destination] and destination not in ended:
                    destination.shutdown(socket.SHUT_WR)
                    ended.add(destination)


class RelayHandler(socketserver.BaseRequestHandler):
    def handle(self):
        try:
            forward(self.request, self.server.target, self.server.idle_seconds)
        except OSError:
            # Browser/startup gates report unavailable/reset connections. Do not
            # print request bodies, headers or copied-site content in CI logs.
            pass


class RelayServer(socketserver.ThreadingTCPServer):
    daemon_threads = True
    allow_reuse_address = True

    def __init__(self, address, target, idle_seconds=IDLE_SECONDS):
        # Explicit endpoint arguments are an import-only synthetic-test seam.
        # The production CLI below fixes listener, destination port and timeout.
        self.target = target
        self.idle_seconds = idle_seconds
        super().__init__(address, RelayHandler)


def main(arguments=None):
    try:
        target = target_address(sys.argv[1:] if arguments is None else arguments)
        with RelayServer(LISTEN, target) as server:
            def stop(_signal, _frame):
                raise SystemExit(0)
            signal.signal(signal.SIGTERM, stop)
            print(f"READY rehearsal loopback 127.0.0.1:8090 -> {target[0]}:80", flush=True)
            try:
                server.serve_forever(poll_interval=0.2)
            except KeyboardInterrupt:
                pass
        return 0
    except (ValueError, OSError) as error:
        print(f"Rehearsal loopback refused: {error}", file=sys.stderr)
        return 2


if __name__ == "__main__":
    raise SystemExit(main())
