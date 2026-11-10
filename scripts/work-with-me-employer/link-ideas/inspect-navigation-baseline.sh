#!/usr/bin/env bash
# Read-only stored navigation baseline; S_TARGET=live|staging39, no arguments.
# `wp config get`/`core version` run before wp_load; `db query` runs after
# wp_config_load without wp_load. Deliberately do not use `db prefix`, whose
# command hook DOES load WordPress. No WordPress,
# MU-plugin, regular plugin, theme, persistent-cache or page-render bootstrap.
# --skip-plugins also excludes the SG Security shutdown exit-code bug. --exec
# disables PHP logging before these CLI commands; no site log is written.
# Paths are derived from stored page hierarchy/permalink settings, NOT plugin
# filters, redirects or rendered evidence. No HTTP, installer, purge or upload.
# SQL reads share one InnoDB REPEATABLE READ / READ ONLY consistent snapshot.
# Only temporary files outside web roots and runner artifacts are written.
# shellcheck disable=SC2016
set -euo pipefail
[[ $# -eq 0 ]] || { echo 'No arguments accepted.' >&2; exit 1; }
target=${S_TARGET:-}
case "$target" in live) want=thaisolitude.com ;; staging39) want=staging39.thaisolitude.com ;; *) echo 'S_TARGET must be live or staging39.' >&2; exit 1 ;; esac
here=$(cd "$(dirname "$0")" && pwd)
creds=${S26_CREDS:-$here/../../../remote_execution.txt}
[[ -f "$creds" ]] || { echo 'Missing connection file.' >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user=${user%%@*}
[[ -n "$host" && "$port" =~ ^[0-9]+$ && -n "$user" ]] || { echo 'Incomplete connection fields.' >&2; exit 1; }
[[ -n ${SSH_AUTH_SOCK:-} && -S "$SSH_AUTH_SOCK" ]] || { echo 'No ssh-agent socket.' >&2; exit 2; }
[[ -n ${RUNNER_TEMP:-} && "$RUNNER_TEMP" = /* && -d "$RUNNER_TEMP" ]] || { echo 'An absolute existing RUNNER_TEMP is required.' >&2; exit 1; }
umask 077
work=$(mktemp -d "$RUNNER_TEMP/navigation-baseline-private-XXXXXX")
trap 'rm -rf "$work"' EXIT
out="$RUNNER_TEMP/ops-out/navigation-baseline-$target"

read -r -d '' REMOTE <<'REMOTE' || true
set -euo pipefail
case ${1:-} in thaisolitude.com|staging39.thaisolitude.com) want=$1 ;; *) echo 'Remote origin refused.' >&2; exit 1 ;; esac
wp() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
umask 077
temp=$(mktemp -d); trap 'rm -rf "$temp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$temp/configs"
doc=''; prefix=''
while IFS= read -r cfg; do
  candidate=$(dirname "$cfg")
  p=$(wp --path="$candidate" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$p" =~ ^[A-Za-z0-9_]+$ ]] || continue
  h=$(wp --path="$candidate" db query "SELECT option_value FROM ${p}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  if [[ "$h" == "http://$want" || "$h" == "https://$want" || "$h" == "http://$want/" || "$h" == "https://$want/" ]]; then
    [[ -z "$doc" ]] || { echo 'Multiple exact origin matches; refused.' >&2; exit 1; }
    doc=$candidate; prefix=$p
  fi
done < "$temp/configs"
[[ -n "$doc" ]] || { echo 'No exact requested origin found.' >&2; exit 1; }
version=$(wp --path="$doc" core version)
[[ "$version" =~ ^[0-9][0-9A-Za-z.+-]*$ ]] || { echo 'Unexpected core version.' >&2; exit 1; }

# All predicates are fixed text plus the strictly validated table prefix.
peek="(p.post_type='wpcode' AND (LEFT(p.post_title,9)='TSPeekNav' OR LEFT(p.post_title,10)='TSPeekData' OR LOCATE('ts-peek-nav-runtime',p.post_content)>0 OR LOCATE('// ts-peek-nav-data:',p.post_content)>0))"
snips="((p.ID IN (1232,1338) AND p.post_type='wpcode') OR $peek)"
css="(p.post_type='custom_css' AND p.post_status='publish' AND p.post_name=(SELECT option_value FROM ${prefix}options WHERE option_name='stylesheet'))"
pages="(p.post_type='page' AND p.post_status='publish' AND p.post_name IN ('thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage'))"
selected="($snips OR $css OR $pages)"
owners="SELECT p.ID FROM ${prefix}posts p WHERE ($snips OR $css) AND p.post_password=''"
page_ids="SELECT p.ID FROM ${prefix}posts p WHERE $pages AND p.post_password=''"
tt_ids="SELECT term_taxonomy_id FROM ${prefix}term_relationships WHERE object_id IN ($owners)"
page_tt_ids="SELECT term_taxonomy_id FROM ${prefix}term_relationships WHERE object_id IN ($page_ids)"
term_ids="SELECT term_id FROM ${prefix}term_taxonomy WHERE term_taxonomy_id IN ($tt_ids)"
page_term_ids="SELECT term_id FROM ${prefix}term_taxonomy WHERE term_taxonomy_id IN ($page_tt_ids)"
meta_keys="'_wpcode_auto_insert','_wpcode_priority','_wpcode_note','_wpcode_shortcode_attributes'"
css_keys="'_edit_lock','_edit_last','_wp_old_slug'"

cat > "$temp/capture.sql" <<SQL
SET NAMES utf8mb4;
SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
SET SESSION TRANSACTION READ ONLY;
SET SESSION group_concat_max_len=1048576;
START TRANSACTION WITH CONSISTENT SNAPSHOT;
-- MariaDB retains tx_*; MySQL 8 uses transaction_*. Only these fixed variable
-- names enter PREPARE, so the unselected (possibly removed) alias is not parsed.
SET @navigation_read_only=IF(LOCATE('MariaDB',VERSION())>0,'@@session.tx_read_only','@@session.transaction_read_only');
SET @navigation_isolation=IF(LOCATE('MariaDB',VERSION())>0,'@@session.tx_isolation','@@session.transaction_isolation');
SET @navigation_state_sql=CONCAT('SELECT JSON_OBJECT(''kind'',''runtime'',''core_version'',''$version'',''database_version'',VERSION(),''captured_utc'',UTC_TIMESTAMP(),''read_only'',CAST(',@navigation_read_only,' AS UNSIGNED),''isolation'',',@navigation_isolation,')');
PREPARE navigation_state FROM @navigation_state_sql;
EXECUTE navigation_state;
DEALLOCATE PREPARE navigation_state;
SELECT JSON_OBJECT('kind','engine','table',SUBSTRING(TABLE_NAME,LENGTH('$prefix')+1),'engine',ENGINE) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('${prefix}posts','${prefix}postmeta','${prefix}options','${prefix}term_relationships','${prefix}term_taxonomy','${prefix}terms','${prefix}termmeta') ORDER BY TABLE_NAME;
SELECT JSON_OBJECT('kind','column','table',SUBSTRING(TABLE_NAME,LENGTH('$prefix')+1),'name',COLUMN_NAME,'ordinal',ORDINAL_POSITION,'type',COLUMN_TYPE,'nullable',IS_NULLABLE) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('${prefix}posts','${prefix}postmeta','${prefix}options','${prefix}term_relationships','${prefix}term_taxonomy','${prefix}terms','${prefix}termmeta') ORDER BY TABLE_NAME,ORDINAL_POSITION;
SELECT JSON_OBJECT('kind','refusal','reason','selected post has a password','id',p.ID) FROM ${prefix}posts p WHERE $selected AND p.post_password<>'';
SELECT JSON_OBJECT('kind','page_parent','id',ID,'parent',post_parent,'slug',post_name,'status',post_status) FROM ${prefix}posts WHERE post_type='page' ORDER BY ID;
SELECT JSON_OBJECT('kind','page','id',ID,'parent',post_parent,'slug',post_name,'title',post_title,'status',post_status,'modified_gmt',post_modified_gmt,'password_protected',post_password<>'','content_bytes',OCTET_LENGTH(post_content),'content_sha256',SHA2(post_content,256),'host_normalized_sha256',SHA2(REPLACE(REPLACE(REPLACE(REPLACE(post_content,'https://staging39.thaisolitude.com','HOST'),'http://staging39.thaisolitude.com','HOST'),'https://thaisolitude.com','HOST'),'http://thaisolitude.com','HOST'),256)) FROM ${prefix}posts WHERE post_type='page' AND post_status='publish' ORDER BY ID;
SELECT JSON_OBJECT('kind','snippet','id',p.ID,'title',p.post_title,'status',p.post_status,'modified_gmt',p.post_modified_gmt,'code_bytes',OCTET_LENGTH(p.post_content),'code_sha256',SHA2(p.post_content,256),'peek_candidate',IF($peek,1,0)) FROM ${prefix}posts p WHERE p.post_type='wpcode' ORDER BY p.ID;
SQL

# Build column-complete SQL rows from the actual schema. Every cell is HEX of
# its raw SQL byte representation (including serialized meta/options); NULL is
# distinct. Schema order is captured. Hashed-only rows never export their cells.
emit_rows() {
  local kind=$1 table=$2 predicate=$3 order=$4 hashed=${5:-0}
  local expression="CONCAT(QUOTE(COLUMN_NAME),',HEX(CAST(\`',REPLACE(COLUMN_NAME,'\`','\`\`'),'\` AS BINARY))')"
  local fields="CONCAT('JSON_OBJECT(',GROUP_CONCAT($expression ORDER BY ORDINAL_POSITION SEPARATOR ','),')')"
  local body="CONCAT('JSON_OBJECT(''kind'',''$kind'',''cells'',', $fields, ')')"
  local key="CAST(\`$order\` AS CHAR)" ordering="\`$order\`"
  if [[ "$table" == term_relationships ]]; then
    key="CONCAT(CAST(\`object_id\` AS CHAR),'':'',CAST(\`term_taxonomy_id\` AS CHAR))"
    ordering='`object_id`,`term_taxonomy_id`'
  fi
  if [[ "$hashed" == 1 ]]; then
    body="CONCAT('JSON_OBJECT(''kind'',''$kind'',''key'',$key,''sha256'',SHA2(', $fields, ',256))')"
  fi
  cat >> "$temp/capture.sql" <<SQL
SELECT CONCAT('SELECT ', $body, ' FROM \`${prefix}${table}\` WHERE $predicate ORDER BY $ordering;') INTO @navigation_export FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='${prefix}${table}';
PREPARE navigation_statement FROM @navigation_export;
EXECUTE navigation_statement;
DEALLOCATE PREPARE navigation_statement;
SQL
}
# Double SQL quotation inside predicates: these strings become SQL string
# literals in the PREPARE builder, never shell code or user-supplied SQL.
quoted() { printf '%s' "$1" | sed "s/'/''/g"; }
emit_rows option options "$(quoted "option_name IN ('home','siteurl','template','stylesheet','astra-settings','permalink_structure','show_on_front','page_on_front','page_for_posts')")" option_id
emit_rows post posts "$(quoted "ID IN (SELECT p.ID FROM ${prefix}posts p WHERE $selected AND p.post_password='')")" ID
emit_rows meta postmeta "$(quoted "post_id IN ($owners) AND ((post_id IN (SELECT p.ID FROM ${prefix}posts p WHERE $snips) AND meta_key IN ($meta_keys)) OR (post_id IN (SELECT p.ID FROM ${prefix}posts p WHERE $css) AND meta_key IN ($css_keys)))")" meta_id
emit_rows owner_meta_hash postmeta "$(quoted "post_id IN ($owners)")" meta_id 1
emit_rows relationship term_relationships "$(quoted "object_id IN ($owners)")" object_id
emit_rows taxonomy term_taxonomy "$(quoted "term_taxonomy_id IN ($tt_ids)")" term_taxonomy_id
emit_rows term terms "$(quoted "term_id IN ($term_ids)")" term_id
emit_rows owner_termmeta_hash termmeta "$(quoted "term_id IN ($term_ids)")" meta_id 1
emit_rows page_meta_hash postmeta "$(quoted "post_id IN ($page_ids)")" meta_id 1
emit_rows page_relationship_hash term_relationships "$(quoted "object_id IN ($page_ids)")" object_id 1
emit_rows page_taxonomy_hash term_taxonomy "$(quoted "term_taxonomy_id IN ($page_tt_ids)")" term_taxonomy_id 1
emit_rows page_term_hash terms "$(quoted "term_id IN ($page_term_ids)")" term_id 1
emit_rows page_termmeta_hash termmeta "$(quoted "term_id IN ($page_term_ids)")" meta_id 1
cat >> "$temp/capture.sql" <<SQL
SELECT JSON_OBJECT('kind','meta_key','id',meta_id,'post_id',post_id,'key',meta_key,'bytes',OCTET_LENGTH(meta_value),'value_sha256',SHA2(meta_value,256)) FROM ${prefix}postmeta WHERE post_id IN ($owners) ORDER BY meta_id;
SET @navigation_state_sql=CONCAT('SELECT JSON_OBJECT(''kind'',''complete'',''read_only'',CAST(',@navigation_read_only,' AS UNSIGNED))');
PREPARE navigation_state FROM @navigation_state_sql;
EXECUTE navigation_state;
DEALLOCATE PREPARE navigation_state;
ROLLBACK;
SQL
wp --path="$doc" db query --skip-column-names --batch --raw < "$temp/capture.sql"
REMOTE
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" <<<"$REMOTE" > "$work/rows.jsonl"

python3 - "$work/rows.jsonl" "$out" "$target" <<'PY'
import datetime, hashlib, json, os, pathlib, re, sys, urllib.parse
source, out, target = pathlib.Path(sys.argv[1]), pathlib.Path(sys.argv[2]), sys.argv[3]
want = {'live': 'thaisolitude.com', 'staging39': 'staging39.thaisolitude.com'}[target]
def fail(message): raise ValueError(message)
def sha(value): return hashlib.sha256(value).hexdigest()
def encoded(value): return json.dumps(value, ensure_ascii=False, separators=(',', ':'), sort_keys=True).encode()
def decode(row):
    cells = row['cells']
    if not isinstance(cells, dict) or not cells: fail('Missing raw SQL cells')
    result = {}
    for key, value in cells.items():
        if value is None: result[key] = None; continue
        if not isinstance(value, str) or not re.fullmatch(r'(?:[0-9A-F]{2})*', value): fail('Malformed raw SQL cell')
        result[key] = bytes.fromhex(value).decode('utf-8')
    return result
rows = [json.loads(line) for line in source.read_text().splitlines() if line.strip()]
kinds = {}
for row in rows:
    if not isinstance(row, dict) or not isinstance(row.get('kind'), str): fail('Malformed export row')
    kinds.setdefault(row['kind'], []).append(row)
runtime = kinds.get('runtime', [])
if len(runtime) != 1 or runtime[0]['read_only'] != 1 or runtime[0]['isolation'] != 'REPEATABLE-READ' or kinds.get('complete') != [{'kind': 'complete', 'read_only': 1}]: fail('Incomplete read-only consistent snapshot')
engines = {row['table']: row['engine'] for row in kinds.get('engine', [])}
if set(engines) != {'posts','postmeta','options','term_relationships','term_taxonomy','terms','termmeta'} or any(value != 'InnoDB' for value in engines.values()): fail('All captured tables must be InnoDB')
issues = [f"{row['reason']}: #{row['id']}" for row in kinds.get('refusal', [])]
options = {}
for row in kinds.get('option', []):
    value = decode(row); name = value['option_name']
    if name in options: fail('Duplicate captured option')
    options[name] = {'raw_hex': row['cells'], 'value': value['option_value'], 'value_sha256': sha(bytes.fromhex(row['cells']['option_value']))}
expected_options = {'home','siteurl','template','stylesheet','astra-settings','permalink_structure','show_on_front','page_on_front','page_for_posts'}
if set(options) != expected_options: fail('Required option missing or unrequested option returned')
values = {name: data['value'] for name, data in options.items()}
for name in ['home','siteurl']:
    if values[name] not in [f'http://{want}', f'https://{want}', f'http://{want}/', f'https://{want}/']: fail('Captured origin changed or unsupported')
if values['template'] != 'astra' or values['stylesheet'] != 'astra': fail('Expected Astra parent theme')
structure = values['permalink_structure']
if structure not in ['', '/%postname%/', '/index.php/%postname%/']: fail('Unsupported stored permalink structure')
if values['show_on_front'] not in ['page', 'posts']: fail('Unsupported front-page setting')
front = int(values['page_on_front'])
parents = {int(row['id']): row for row in kinds.get('page_parent', [])}
if len(parents) != len(kinds.get('page_parent', [])): fail('Duplicate page parent identity')
def path_for(page):
    if values['show_on_front'] == 'page' and page['id'] == front: return '/'
    if not structure: return '/?page_id=' + str(page['id'])
    parts, seen, current = [], set(), page['id']
    while current:
        if current in seen or current not in parents: fail('Missing or cyclic page ancestor')
        seen.add(current); row = parents[current]; slug = row['slug']
        if not isinstance(slug, str) or not re.fullmatch(r'(?:[A-Za-z0-9_-]|%[0-9A-Fa-f]{2})+', slug): fail('Unsupported stored page slug')
        parts.append(slug); current = int(row['parent'])
    return ('/index.php/' if structure.startswith('/index.php/') else '/') + '/'.join(reversed(parts)) + '/'
pages = kinds.get('page', [])
if not pages: fail('No published page inventory')
if values['show_on_front'] == 'page' and front not in {page['id'] for page in pages}: fail('Static front page is not published')
paths = set()
for page in pages:
    if page['status'] != 'publish' or page['id'] not in parents: fail('Invalid published page identity')
    page['path'] = path_for(page)
    if page['path'] in paths: fail('Duplicate stored page path')
    paths.add(page['path'])
    page['normalized_permalink'] = 'HOST' + page['path']
    page.pop('kind')
records = {}
for row in kinds.get('post', []):
    post = decode(row); identity = int(post['ID'])
    if identity in records or post['post_password'] != '': fail('Duplicate or password-protected raw post')
    records[identity] = {'raw_hex': row['cells'], 'post': post, 'state_sha256': sha(encoded(row['cells']))}
for identity, title in [(1232, 'A11yPageJumpLinks'), (1338, 'TSSectionGuide v1')]:
    post = records.get(identity, {}).get('post', {})
    if post.get('post_type') != 'wpcode' or post.get('post_title') != title: issues.append(f'Required navigation identity/title differs: #{identity}')
roles = {'core': [], **{slug: [] for slug in ['thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage']}}
for identity, record in records.items():
    post = record['post']
    if post['post_type'] != 'wpcode': continue
    code, title = post['post_content'], post['post_title']
    if title.startswith('TSPeekNav') or 'ts-peek-nav-runtime' in code: roles['core'].append(identity)
    slugs = set(re.findall(r'// ts-peek-nav-data:([a-z0-9-]+)', code))
    match = re.match(r'^TSPeekData ([a-z0-9-]+)(?: |$)', title)
    if match: slugs.add(match[1])
    for slug in slugs:
        if slug not in roles: issues.append(f'Unexpected Peek data slug on #{identity}')
        else: roles[slug].append(identity)
    if title.startswith('TSPeekData') and not slugs: issues.append(f'Unrecognized Peek data identity: #{identity}')
for role, ids in roles.items():
    if len(ids) > 1: issues.append(f'Duplicate Peek ownership: {role} {ids}')
css_ids = [identity for identity, record in records.items() if record['post']['post_type'] == 'custom_css']
if len(css_ids) != 1: issues.append('Expected one active Additional CSS record')
service_ids = {record['post']['post_name']: identity for identity, record in records.items() if record['post']['post_type'] == 'page'}
service_records = [record for record in records.values() if record['post']['post_type'] == 'page']
if len(service_records) != 4 or len(service_ids) != len(service_records) or set(service_ids) != set(roles) - {'core'}: issues.append('Exactly four unique published service-page witnesses required')
exported_meta = {int(decode(row)['meta_id']) for row in kinds.get('meta', [])}
for row in kinds.get('meta_key', []):
    if row['id'] not in exported_meta: issues.append(f"Unexpected metadata refused: post#{row['post_id']} key={row['key']} sha256={row['value_sha256']}")
for key in ['post','meta','relationship','taxonomy','term','option']:
    for row in kinds.get(key, []):
        table = {'post':'posts','meta':'postmeta','relationship':'term_relationships','taxonomy':'term_taxonomy','term':'terms','option':'options'}[key]
        columns = {column['name'] for column in kinds.get('column', []) if column['table'] == table}
        if set(row['cells']) != columns: fail('Raw SQL row is not column-complete')
witnesses = {}
for kind in ['owner_meta_hash','owner_termmeta_hash','page_meta_hash','page_relationship_hash','page_taxonomy_hash','page_term_hash','page_termmeta_hash']:
    data = kinds.get(kind, [])
    for row in data:
        if not re.fullmatch('[0-9a-f]{64}', row.get('sha256','')): fail('Malformed row witness hash')
    data = sorted(data, key=lambda row: (row['key'], row['sha256']))
    witnesses[kind] = {'count': len(data), 'rows': data, 'ordered_rows_sha256': sha(encoded(data))}
snapshot = {'schema': 1, 'target': target, 'host': want, 'runtime': runtime[0],
    'scope': {'wordpress_bootstrap': False, 'http_requests': 0, 'database_writes': 0, 'route_provenance': 'unfiltered stored page hierarchy and core permalink/front-page settings; no plugin redirects or rendered route claim', 'host_normalization': 'both HTTP/HTTPS live and staging39 origins replaced with literal HOST', 'raw_encoding': 'raw_hex contains every SQL column as HEX(CAST(value AS BINARY)); SQL NULL stays null; no unserialization', 'row_hashes': 'SQL SHA2(JSON_OBJECT(column,HEX(CAST(value AS BINARY))) in captured ordinal column order; witness rows sort by key then sha256; outer hash uses sorted-key compact UTF-8 JSON'},
    'engines': engines, 'columns': kinds.get('column', []), 'options': options, 'published_pages': pages,
    'page_parent_identities': kinds.get('page_parent', []), 'snippet_metadata': kinds.get('snippet', []), 'peek_ownership': roles,
    'records': records, 'additional_css_ids': css_ids, 'service_page_ids': service_ids,
    'raw_meta': kinds.get('meta', []), 'meta_inventory': kinds.get('meta_key', []), 'raw_relationships': kinds.get('relationship', []),
    'raw_taxonomies': kinds.get('taxonomy', []), 'raw_terms': kinds.get('term', []), 'hash_witnesses': witnesses, 'issues': issues}
out.mkdir(parents=True, exist_ok=False)
body = encoded(snapshot) + b'\n'; path = out/'baseline.json'; path.write_bytes(body); path.chmod(0o600)
manifest = {'schema':1,'target':target,'file':'baseline.json','bytes':len(body),'sha256':sha(body),'published_pages':len(pages),'snippets':len(kinds.get('snippet',[])),'full_records':len(records),'issues':len(issues)}
manifest_path = out/'manifest.json'; manifest_path.write_bytes(encoded(manifest)+b'\n'); manifest_path.chmod(0o600)
print('NAVIGATION-BASELINE ' + json.dumps(manifest, separators=(',',':')))
# The checksum-verified artifact is canonical. Large encoded console copies
# delay publication and can be changed by GitHub's secret masking.
print('Stored navigation: ' + json.dumps({'peek_ownership':roles,'additional_css_ids':css_ids,'service_page_ids':service_ids},separators=(',',':')))
print('Published routes: ' + json.dumps([page['path'] for page in pages],separators=(',',':')))
for issue in issues: print('FAIL baseline: ' + issue)
print('Artifact: ' + str(path))
sys.exit(1 if issues else 0)
PY
