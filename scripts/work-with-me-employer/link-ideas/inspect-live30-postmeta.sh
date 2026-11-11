#!/usr/bin/env bash
# One-off LIVE #30 evidence: only the five reported postmeta IDs, hashed values.
# Config/db CLI commands do not load WordPress, MU plugins, plugins or themes.
# No HTTP, installer, purge, upload, source/journal read or database write.
# shellcheck disable=SC2016
set -euo pipefail
[[ $# -eq 0 && ${S_TARGET:-} = live ]] || { echo 'Requires S_TARGET=live and no arguments.' >&2; exit 1; }
here=$(cd "$(dirname "$0")" && pwd)
creds=${S26_CREDS:-$here/../../../remote_execution.txt}
[[ -f "$creds" ]] || { echo 'Missing connection file.' >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user=${user%%@*}
[[ -n "$host" && "$port" =~ ^[0-9]+$ && -n "$user" ]] || { echo 'Incomplete connection fields.' >&2; exit 1; }
[[ -n ${SSH_AUTH_SOCK:-} && -S "$SSH_AUTH_SOCK" ]] || { echo 'No ssh-agent socket.' >&2; exit 2; }
read -r -d '' REMOTE <<'REMOTE' || true
set -euo pipefail
[[ $# -eq 0 ]] || exit 1
wp() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
umask 077
temp=$(mktemp -d); trap 'rm -rf "$temp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$temp/configs"
doc=''; prefix=''
while IFS= read -r cfg; do
  candidate=$(cd -- "$(dirname "$cfg")" && pwd -P) || continue
  p=$(wp --path="$candidate" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$p" =~ ^[A-Za-z0-9_]+$ ]] || continue
  h=$(wp --path="$candidate" db query "SELECT option_value FROM ${p}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  if [[ "$h" = 'http://thaisolitude.com' ]]; then
    [[ -z "$doc" ]] || { echo 'Multiple exact live origin matches; refused.' >&2; exit 1; }
    doc=$candidate; prefix=$p
  fi
done < "$temp/configs"
[[ -n "$doc" ]] || { echo 'No exact live origin found.' >&2; exit 1; }
# Live's captured database is MySQL 8.4. Unsupported transaction variables fail
# closed. All query text is fixed except the strictly validated table prefix.
cat > "$temp/capture.sql" <<SQL
SET NAMES utf8mb4;
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
SET SESSION TRANSACTION READ ONLY;
START TRANSACTION WITH CONSISTENT SNAPSHOT;
SELECT JSON_OBJECT('kind','runtime','captured_utc',UTC_TIMESTAMP(),'read_only',CAST(@@session.transaction_read_only AS UNSIGNED),'isolation',@@session.transaction_isolation);
SELECT JSON_OBJECT('kind','engine','table',SUBSTRING(TABLE_NAME,LENGTH('$prefix')+1),'engine',ENGINE) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('${prefix}postmeta','${prefix}options') ORDER BY TABLE_NAME;
SELECT JSON_OBJECT('kind','origin','exact',COUNT(*)=1 AND SUM(BINARY option_value=BINARY 'http://thaisolitude.com')=1) FROM ${prefix}options WHERE option_name='home';
SELECT JSON_OBJECT('kind','postmeta','meta_id',wanted.meta_id,'present',m.meta_id IS NOT NULL,'post_id',m.post_id,'meta_key_sha256',SHA2(CAST(m.meta_key AS BINARY),256),'meta_value_bytes',OCTET_LENGTH(m.meta_value),'meta_value_sha256',SHA2(CAST(m.meta_value AS BINARY),256)) FROM (SELECT 4701 AS meta_id UNION ALL SELECT 4702 UNION ALL SELECT 5089 UNION ALL SELECT 5090 UNION ALL SELECT 5091) wanted LEFT JOIN ${prefix}postmeta m ON m.meta_id=wanted.meta_id ORDER BY wanted.meta_id;
SELECT JSON_OBJECT('kind','complete','read_only',CAST(@@session.transaction_read_only AS UNSIGNED));
ROLLBACK;
SQL
wp --path="$doc" db query --skip-column-names --batch --raw < "$temp/capture.sql"
REMOTE
read -r -d '' CHECK <<'PY' || true
import json,re,sys
try:
    rows=[json.loads(line) for line in sys.stdin if line.strip()]
    assert len(rows)==10
    runtime=rows[0]
    assert set(runtime)=={'kind','captured_utc','read_only','isolation'}
    assert runtime['kind']=='runtime' and runtime['read_only']==1 and runtime['isolation']=='REPEATABLE-READ'
    assert re.fullmatch(r'\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d+)?',runtime['captured_utc'])
    assert {r['table']:r['engine'] for r in rows[1:3]}=={'options':'InnoDB','postmeta':'InnoDB'}
    assert all(set(r)=={'kind','table','engine'} and r['kind']=='engine' for r in rows[1:3])
    assert rows[3]=={'kind':'origin','exact':1}
    assert rows[-1]=={'kind':'complete','read_only':1}
    data=rows[4:9]
    assert [r['meta_id'] for r in data]==[4701,4702,5089,5090,5091]
    fields={'kind','meta_id','present','post_id','meta_key_sha256','meta_value_bytes','meta_value_sha256'}
    for row in data:
        assert set(row)==fields and row['kind']=='postmeta' and type(row['meta_id']) is int
        assert type(row['present']) is int and row['present'] in (0,1)
        if row['present']:
            assert type(row['post_id']) is int and row['post_id']>0
            assert row['meta_key_sha256'] is None or re.fullmatch('[a-f0-9]{64}',row['meta_key_sha256'])
            if row['meta_value_bytes'] is None:
                assert row['meta_value_sha256'] is None
            else:
                assert type(row['meta_value_bytes']) is int and row['meta_value_bytes']>=0
                assert re.fullmatch('[a-f0-9]{64}',row['meta_value_sha256'])
        else:
            assert all(row[k] is None for k in fields-{'kind','meta_id','present'})
except Exception:
    raise SystemExit('LIVE30 postmeta capture incomplete or invalid; no values disclosed.')
print('LIVE30-POSTMETA '+json.dumps({'captured_utc':runtime['captured_utc'],'read_only':True,'wordpress_bootstrap':False,'requested':5,'found':sum(r['present'] for r in data)},separators=(',',':')))
for row in data:
    print('LIVE30-POSTMETA-ROW '+json.dumps(row,separators=(',',':')))
PY
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s <<<"$REMOTE" | python3 -c "$CHECK"
