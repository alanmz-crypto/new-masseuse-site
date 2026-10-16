"""Summarise per-frame header colours (from the in-page trace) and screen-capture brightness of the header bar."""
import json, sys, subprocess, os, re
import numpy as np
SP = os.environ.get('TS_FLASH_WORK', '/tmp/ts-flash-work'); os.makedirs(SP, exist_ok=True); label = sys.argv[1]
d = json.load(open(f'{SP}/trace-{label}.json'))
def lum(c):
    m = re.findall(r'[\d.]+', c or ''); 
    if len(m) < 3: return None
    r, g, b = map(float, m[:3]); a = float(m[3]) if len(m) > 3 else 1
    return round((0.2126 * r + 0.7152 * g + 0.0722 * b) * a + 255 * (1 - a) * 0) if a > 0 else 0
print(f"== {label} env={d['env']}")
for a in d['actions']:
    tr = a['trace']
    if not tr: print(a['kind'], a['arg'], 'NO TRACE'); continue
    fr = tr['frames']
    bright = [f for f in fr if (lum(f[1]) or 0) > 150 or (lum(f[2]) or 0) > 150]
    dark_text = [f for f in fr if f[5] != -1 and f[5] > 0 and (lum(f[3]) or 255) < 90]
    print(f"-- {a['kind']:6s} {str(a['arg']):9s} css={a.get('css')} nav={tr['nav']} events={tr['events']} frames={len(fr)} first={fr[0][:8] if fr else None}")
    print(f"   frames with LIGHT header bar/menu-link bg: {len(bright)}  (first {bright[0][0] if bright else '-'}ms .. last {bright[-1][0] if bright else '-'}ms)")
    print(f"   frames with DARK menu text while fallback active: {len(dark_text)}")
    seen = None
    for f in fr:
        key = (f[1], f[2], f[3], f[5] > 0)
        if key != seen: print('     ', f); seen = key
# screen capture analysis
vid = f'{SP}/rec-{label}.mkv'
if os.path.exists(vid):
    x, y, w, h = [int(v) for v in d['env']['bar']]; y0 = max(0, y); h = min(h, 900 - y0)
    probe = subprocess.run(['ffprobe', '-v', 'error', '-select_streams', 'v', '-show_entries', 'frame=pts_time', '-of', 'csv=p=0', vid], capture_output=True, text=True).stdout.split()
    pts = [float(p.strip(",")) for p in probe if p.strip(",")]
    raw = subprocess.run(['ffmpeg', '-v', 'error', '-i', vid, '-vf', f'crop={w}:{h}:{x}:{y0},format=gray', '-f', 'rawvideo', '-'], capture_output=True).stdout
    fr = np.frombuffer(raw, np.uint8).reshape(-1, h, w)
    frac = (fr > 200).mean(axis=(1, 2)); mean = fr.mean(axis=(1, 2))
    base = frac[:10].mean()
    print(f"== screen: {len(fr)} frames, header bar crop {w}x{h}@{x},{y0}; baseline bright fraction {base:.3f}")
    for a in d['actions']:
        idx = [i for i, p in enumerate(pts) if a['t'] - 0.05 <= p <= a['t'] + 1.6]
        hot = [(round((pts[i] - a['t']) * 1000), round(float(frac[i]), 3)) for i in idx if frac[i] > base + 0.15]
        print(f"   {a['kind']:6s} {str(a['arg']):9s}: {len(idx)} frames in window, {len(hot)} with bright header; {hot[:12]}")
