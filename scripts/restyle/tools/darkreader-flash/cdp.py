import asyncio, json, urllib.request, websockets
class CDP:
    def __init__(self, ws):
        self.ws, self.n, self.pending, self.handlers = ws, 0, {}, {}
    @classmethod
    async def page(cls, port=9333):
        tl = json.load(urllib.request.urlopen(f'http://127.0.0.1:{port}/json/list'))
        pages = [t for t in tl if t['type'] == 'page' and not t['url'].startswith('chrome-extension')]
        ws = await websockets.connect(pages[0]['webSocketDebuggerUrl'], max_size=2**28)
        c = cls(ws); c.task = asyncio.create_task(c._reader()); c.target = pages[0]; return c
    async def _reader(self):
        async for raw in self.ws:
            m = json.loads(raw)
            if 'id' in m:
                f = self.pending.pop(m['id'], None)
                if f and not f.done(): f.set_result(m)
            else:
                for h in self.handlers.get(m['method'], []):
                    asyncio.create_task(h(m.get('params', {})))
    def on(self, method, fn): self.handlers.setdefault(method, []).append(fn)
    async def call(self, method, params=None):
        self.n += 1; f = asyncio.get_event_loop().create_future(); self.pending[self.n] = f
        await self.ws.send(json.dumps({'id': self.n, 'method': method, 'params': params or {}}))
        m = await asyncio.wait_for(f, 30)
        if 'error' in m: raise RuntimeError(f"{method}: {m['error']}")
        return m['result']
    async def js(self, expr):
        r = await self.call('Runtime.evaluate', {'expression': expr, 'returnByValue': True, 'awaitPromise': True})
        return r['result'].get('value')
