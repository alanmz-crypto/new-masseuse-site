"""Blink check from the screen recording: per action, header-bar and full-page mean luminance per frame vs the steady state before the action."""
import json, sys, subprocess, os
import numpy as np
SP = os.environ.get('TS_FLASH_WORK', '/tmp/ts-flash-work'); os.makedirs(SP, exist_ok=True); label = sys.argv[1]
d = json.load(open(f'{SP}/trace-{label}.json')); vid = f'{SP}/rec-{label}.mkv'
pts = [float(p.strip(',')) for p in subprocess.run(['ffprobe', '-v', 'error', '-select_streams', 'v', '-show_entries', 'frame=pts_time', '-of', 'csv=p=0', vid], capture_output=True, text=True).stdout.split() if p.strip(',')]
raw = subprocess.run(['ffmpeg', '-v', 'error', '-i', vid, '-vf', 'crop=1284:900:0:0,format=gray', '-f', 'rawvideo', '-'], capture_output=True).stdout
fr = np.frombuffer(raw, np.uint8).reshape(-1, 900, 1284)
# header strip without the menu-link band (hover/current colours legitimately change there)
hdr = np.concatenate([fr[:, 4:86, 0:560], fr[:, 4:86, 1080:1284]], axis=2).astype(float)
head_mean = hdr.mean(axis=(1, 2)); head_dark = (hdr < 120).mean(axis=(1, 2)); head_white = (hdr > 250).mean(axis=(1, 2))
page_mean = fr[:, 120:880, :].astype(float).mean(axis=(1, 2))
print(f'== {label}: {len(fr)} frames')
for a in d['actions']:
    pre = [i for i, p in enumerate(pts) if a['t'] - 0.25 <= p < a['t'] - 0.02]
    win = [i for i, p in enumerate(pts) if a['t'] - 0.02 <= p <= a['t'] + 1.5]
    h0, p0 = head_mean[pre].mean(), page_mean[pre].mean()
    hd = max(abs(head_mean[i] - h0) for i in win); dk = max(head_dark[i] for i in win); wh = max(head_white[i] for i in win)
    pv = page_mean[win]; steps = np.abs(np.diff(pv)); 
    print(f"  {a['kind']:6s} {str(a['arg']):9s} css={a.get('css')}: header steady={h0:6.1f} maxdev={hd:5.1f} max-dark-px={dk:.3f} max-white-px={wh:.3f} | page {p0:6.1f} min={pv.min():6.1f} max={pv.max():6.1f} biggest frame-step={steps.max():5.1f}")
