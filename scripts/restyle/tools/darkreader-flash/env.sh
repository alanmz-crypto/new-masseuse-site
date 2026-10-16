#!/usr/bin/env bash
# Throwaway test browser for the Thai Solitude white-flash work (2026-10-04, Claude Fabel).
# A virtual X display (:77) plus Chromium on a scratch profile with an unpacked copy of the
# Dark Reader build installed in Ryan's Chrome (default settings = Ryan's synced settings:
# dynamic theme, enabled for all sites). Never touches Ryan's real Chrome profiles.
#   ./env.sh up dr      # Chromium + Dark Reader, OS dark scheme   (for dark-mode flash tests)
#   ./env.sh up plain   # Chromium without extensions              (harness.py --light, lightcheck.py)
#   ./env.sh down       # stop both (by PID file, so it can't match the calling shell)
# Outputs, profiles and the Dark Reader copy live in $TS_FLASH_WORK (default /tmp/ts-flash-work).
WORK="${TS_FLASH_WORK:-/tmp/ts-flash-work}"; mkdir -p "$WORK"
case "$1" in
  up)
    KIND="${2:-dr}"; EXT=""
    if [ "$KIND" = dr ]; then
      if [ ! -f "$WORK/darkreader/manifest.json" ]; then
        SRC=$(ls -d "${DR_SRC:-$HOME/.config/google-chrome/Profile 4/Extensions/eimadpbcbfnmbkopoojfekhnkhdbieeh}"/*/ | sort -V | tail -1)
        cp -r "$SRC" "$WORK/darkreader" && rm -rf "$WORK/darkreader/_metadata"   # Chrome refuses unpacked dirs with _metadata
      fi
      EXT="--load-extension=$WORK/darkreader"
    fi
    if [ ! -s "$WORK/xvfb.pid" ] || ! kill -0 "$(cat "$WORK/xvfb.pid")" 2>/dev/null; then
      setsid nohup Xvfb :77 -screen 0 1300x900x24 -nolisten tcp >"$WORK/xvfb.log" 2>&1 & echo $! >"$WORK/xvfb.pid"; sleep 1
    fi
    # Chromium, not Google Chrome: branded Chrome ignores --load-extension since v137.
    env -u WAYLAND_DISPLAY DISPLAY=:77 setsid nohup /usr/bin/chromium --user-data-dir="$WORK/prof-$KIND" \
      --no-first-run --no-default-browser-check --disable-features=DisableLoadExtensionCommandLineSwitch $EXT \
      --force-dark-mode --ozone-platform=x11 --kiosk --window-position=0,0 --window-size=1300,900 \
      --remote-debugging-port=9333 --password-store=basic --disable-background-networking --disable-sync \
      about:blank >"$WORK/chromium.log" 2>&1 & echo $! >"$WORK/chromium.pid"
    for i in $(seq 1 40); do curl -s http://127.0.0.1:9333/json/version >/dev/null && break; sleep 0.25; done
    sleep 2
    # Dark Reader opens its help page on first install; close it so the harness drives about:blank.
    python3 - <<'PY'
import json, urllib.request
L = lambda: json.load(urllib.request.urlopen('http://127.0.0.1:9333/json/list'))
for t in L():
    if t['type'] == 'page' and 'darkreader.org' in t['url']: urllib.request.urlopen(f"http://127.0.0.1:9333/json/close/{t['id']}")
for t in L():
    if t['type'] == 'page': urllib.request.urlopen(f"http://127.0.0.1:9333/json/activate/{t['id']}"); print('ready:', t['url'])
PY
    ;;
  down)
    for f in chromium xvfb; do [ -s "$WORK/$f.pid" ] && kill "$(cat "$WORK/$f.pid")" 2>/dev/null; rm -f "$WORK/$f.pid"; sleep 0.5; done; echo stopped ;;
  *) echo "usage: $0 up [dr|plain] | down"; exit 2 ;;
esac
