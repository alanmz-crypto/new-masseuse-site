#!/usr/bin/env python3
"""Make the four tinted section pictures for the Chair Massage page from Ryan's corporate illustration (../corporate/images/corporate-group-massage.webp).

    python3 -I -B make-tints.py          writes images/chair-massage-<tone>.webp (green, teal, yellow, red), 512x416 like the source
    python3 -I -B make-tints.py --check  exit 1 if a committed picture is not what this script makes from the source

Ryan, 2026-10-30 (cloud session): asked how to picture the page when the repo has no chair-massage illustration; he chose "Recolor your corporate illustration" (hero = the
illustration already in the media library; the four section pictures = tints of it, like the Therapeutic page's green/teal/yellow/red variants of its own hero).
Each tint is a duotone: the illustration's lightness mapped onto a dark-to-light ramp of one hue. Pillow only (installed on the practice and CI images); nothing is fetched.
"""
import hashlib
import os
import sys

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, '..', 'corporate', 'images', 'corporate-group-massage.webp')
SRC_SHA256 = '764a7ef428cbd2985156b584116f8bc196dd5c3a900e2426ff4f49f8582a747e'
OUT = os.path.join(HERE, 'images')
# tone -> (dark end, light end) of the ramp, as RGB
TONES = {
    'green':  ((22, 58, 44), (214, 236, 222)),
    'teal':   ((20, 52, 72), (210, 234, 240)),
    'yellow': ((92, 72, 18), (248, 238, 196)),
    'red':    ((96, 34, 34), (246, 220, 214)),
}


def duotone(im, dark, light):
    g = im.convert('L')
    lut = []
    for c in range(3):
        lut += [round(dark[c] + (light[c] - dark[c]) * v / 255) for v in range(256)]
    return Image.merge('RGB', [g.point(lut[c * 256:(c + 1) * 256]) for c in range(3)])


def make(tone):
    with open(SRC, 'rb') as f:
        if hashlib.sha256(f.read()).hexdigest() != SRC_SHA256:
            raise SystemExit('the source picture is not the reviewed file')
    im = Image.open(SRC).convert('RGB')
    out = duotone(im, *TONES[tone])
    buf = os.path.join(OUT, 'chair-massage-%s.webp' % tone)
    out.save(buf, 'WEBP', quality=88, method=6)
    return buf


if __name__ == '__main__':
    os.makedirs(OUT, exist_ok=True)
    if '--check' in sys.argv:
        import tempfile
        bad = 0
        for tone in TONES:
            want = os.path.join(OUT, 'chair-massage-%s.webp' % tone)
            with tempfile.TemporaryDirectory() as td:
                OUT_SAVE, OUT = OUT, td
                got = make(tone)
                OUT = OUT_SAVE
                same = os.path.isfile(want) and open(want, 'rb').read() == open(got, 'rb').read()
            print(('PASS ' if same else 'FAIL ') + os.path.basename(want))
            bad += 0 if same else 1
        sys.exit(1 if bad else 0)
    for tone in TONES:
        p = make(tone)
        print('%s  %d bytes  sha256 %s' % (os.path.relpath(p, HERE), os.path.getsize(p), hashlib.sha256(open(p, 'rb').read()).hexdigest()))
