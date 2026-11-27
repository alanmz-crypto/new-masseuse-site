"""Exercise install-v2.php through WP-CLI against a disposable WordPress stack.

Run `rehearsal-up.sh DIR` first, then `python3 rehearse.py DIR`. The stack never holds real site data.
Every refusal case must leave the snippet and the v2 asset folder exactly as they were.
The stack ends back on the v1 snippet.
"""
import hashlib
import json
import subprocess
import sys
import time
from concurrent.futures import ThreadPoolExecutor
from io import BytesIO
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

HOST = 'thaisolitude.com'
V1 = 'f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566'
ASSET_DIR = '/var/www/html/wp-content/ts-peek/v2'
PLUGIN = '/var/www/html/wp-content/plugins/ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php'
TITLE = 'TSPeekNav visitor release v1'
STACK = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else None


def sha(data):
    return hashlib.sha256(data).hexdigest()


def compose(*args, stdin=None):
    return subprocess.run(['docker', 'compose', *args], cwd=STACK, capture_output=True, text=True, input=stdin)


def root(command):
    """Run a shell command as root inside the WordPress container."""
    result = compose('exec', '-T', 'wordpress', 'sh', '-c', command)
    assert result.returncode == 0, result.stderr
    return result.stdout


def wp(*args, backup=True, extra_env=()):
    env = ['-e', 'WWM_BACKUP_DIR=/backups'] if backup else []
    env += [item for pair in extra_env for item in ('-e', pair)]
    result = compose('run', '--rm', '-T', *env, 'wpcli', 'wp', *args, '--user=1')
    text = result.stdout + '\n' + result.stderr
    return result.returncode, '\n'.join(l for l in text.splitlines() if l.strip() and not l.lstrip().startswith('Container ')).strip()


def php(code):
    rc, out = wp('eval', code)
    assert rc == 0, out
    return out.split('\n')[-1]


def installer(mode, archive, *extra, backup=True, extra_env=()):
    return wp('eval-file', '/pkg/install-v2.php', mode, f'/archives/{archive}', *extra, backup=backup, extra_env=extra_env)


def fetch(path):
    # Always connect to the disposable container's own loopback. Never follow
    # redirects to the production hostname, even on a broken fixture.
    result = subprocess.run(['docker', 'compose', 'exec', '-T', 'wordpress', 'curl',
                             '-fsS', '--max-redirs', '0', '-H', f'Host: {HOST}',
                             'http://127.0.0.1' + path], cwd=STACK, capture_output=True, check=True)
    return result.stdout


def state():
    """Stored code hash, post fingerprint and the hash of each v2 asset (None when absent)."""
    code = php('echo hash("sha256", get_post(1343)->post_content);')
    fingerprint = php(
        'echo json_encode(array(get_post_meta(1343), get_post(1343)->post_status, get_post(1343)->post_title, '
        'wp_get_object_terms(1343, "wpcode_location", array("fields"=>"slugs")), wp_get_object_terms(1343, "wpcode_type", array("fields"=>"slugs"))));')
    assets = {}
    for name in ('navigation.js', 'navigation.css'):
        out = root(f'if [ -e {ASSET_DIR}/{name} ]; then sha256sum {ASSET_DIR}/{name}; else echo none; fi').split()[0]
        assets[name] = None if out == 'none' else out
    return code, fingerprint, assets


def check(name, condition, detail=''):
    if not condition:
        raise AssertionError(f'FAIL {name}: {detail}')
    print(f'PASS {name}')


def make_archive(name, edit=None):
    entries = {}
    with ZipFile(STACK / 'archives/peek-v2.zip') as source:
        for item in source.namelist():
            entries[item] = source.read(item)
    if edit:
        edit(entries)
    buffer = BytesIO()
    with ZipFile(buffer, 'w', ZIP_DEFLATED) as target:
        for item, data in entries.items():
            target.writestr(item, data)
    (STACK / 'archives' / name).write_bytes(buffer.getvalue())


def edit_manifest(**changes):
    def edit(entries):
        manifest = json.loads(entries['manifest.json'])
        manifest.update(changes)
        entries['manifest.json'] = (json.dumps(manifest, indent=2) + '\n').encode()
    return edit


def refuses(name, archive, mode='apply', *extra, expect, backup=True, extra_env=()):
    before = state()
    rc, out = installer(mode, archive, *extra, backup=backup, extra_env=extra_env)
    check(f'{name}: refused', rc != 0 and expect in out, out)
    check(f'{name}: nothing changed', state() == before)


def apply_with_contenders():
    # Hold an actual apply at its snippet lookup. A competing writer must
    # already be excluded here, even if it selected a separate backup folder.
    archives = STACK / 'archives'
    release = archives / 'continue-apply'
    entered = archives / 'apply-at-lookup'
    release.unlink(missing_ok=True)
    entered.unlink(missing_ok=True)
    (archives / 'pause-apply.php').write_text("""<?php
WP_CLI::add_hook('after_wp_load', static function () {
    add_action('pre_get_posts', static function ($query) {
        if ($query->get('title') !== 'TSPeekNav visitor release v1') { return; }
        touch('/archives/apply-at-lookup');
        $end = microtime(true) + 60;
        while (!file_exists('/archives/continue-apply')) {
            if (microtime(true) > $end) { WP_CLI::error('Timed out waiting for competitor checks'); }
            usleep(50000);
        }
    });
});
""")
    with ThreadPoolExecutor(max_workers=1) as worker:
        first = worker.submit(wp, '--require=/archives/pause-apply.php', 'eval-file',
                              '/pkg/install-v2.php', 'apply', '/archives/good.zip')
        try:
            deadline = time.monotonic() + 45
            while not entered.exists():
                if first.done() or time.monotonic() > deadline:
                    raise AssertionError('First apply did not reach the snippet lookup')
                time.sleep(0.05)
            before = state()
            for mode in ('apply', 'rollback'):
                rc, out = installer(mode, 'good.zip', backup=False, extra_env=('WWM_BACKUP_DIR=/archives',))
                check(f'concurrent {mode} refused before reading site state', rc != 0 and 'site lock' in out, out)
            check('contenders left the snippet and assets unchanged', state() == before)
            check('contenders created no snapshot', not list(archives.glob('peek-v2-before-*.json')))
        finally:
            release.touch()
        return first.result()


def main():
    start = state()
    check('baseline is v1 with no v2 assets', start[0] == V1 and start[2] == {'navigation.js': None, 'navigation.css': None}, start)
    make_archive('good.zip')
    with ZipFile(STACK / 'archives/good.zip') as archive:
        manifest = json.loads(archive.read('manifest.json'))
    after = manifest['replacement_code_sha256']
    rc, out = installer('preflight', 'good.zip')
    check('preflight passes on the exact baseline', rc == 0 and 'state=ready' in out and 'navigation.js=absent' in out, out)

    # Archive and manifest.
    make_archive('t1.zip', lambda e: e.__setitem__('wpcode/after.txt', e['wpcode/after.txt'] + b'\n// x\n'))
    refuses('tampered replacement code', 't1.zip', expect='does not match the manifest hash')
    make_archive('t2.zip', lambda e: e.__setitem__('wp-content/ts-peek/v2/navigation.js', e['wp-content/ts-peek/v2/navigation.js'] + b'//'))
    refuses('tampered asset', 't2.zip', expect='does not match the manifest hash')
    make_archive('t3.zip', lambda e: e.__setitem__('wp-content/evil.php', b'<?php'))
    refuses('extra archive file', 't3.zip', expect='exactly the expected files')
    make_archive('t4.zip', edit_manifest(host='staging40.thaisolitude.com'))
    refuses('manifest for another host', 't4.zip', expect='Manifest host differs')
    make_archive('t5.zip', edit_manifest(spectra_version='2.20.6'))
    refuses('manifest for another Spectra version', 't5.zip', expect='Manifest spectra_version differs')
    make_archive('t6.zip', edit_manifest(snippet_id=1344))
    refuses('manifest for another snippet id', 't6.zip', expect='Manifest snippet_id differs')
    make_archive('t7.zip', edit_manifest(snippet_title='Other'))
    refuses('manifest for another snippet title', 't7.zip', expect='Manifest snippet_title differs')
    make_archive('t8.zip', edit_manifest(start_code_sha256='0' * 64))
    refuses('manifest with another start hash', 't8.zip', expect='Manifest start_code_sha256 differs')

    def other_start(entries):
        entries['wpcode/before.txt'] += b'// other\n'
        manifest = json.loads(entries['manifest.json'])
        manifest['sha256']['wpcode/before.txt'] = manifest['start_code_sha256'] = sha(entries['wpcode/before.txt'])
        entries['manifest.json'] = (json.dumps(manifest, indent=2) + '\n').encode()
    make_archive('t9.zip', other_start)
    refuses('self-consistent archive with another start code', 't9.zip', expect='Manifest start_code_sha256 differs')
    make_archive('t10.zip', lambda e: e.__setitem__('manifest.json', json.dumps({k: v for k, v in json.loads(e['manifest.json']).items() if k != 'start_code_sha256'}).encode()))
    refuses('manifest without the start hash', 't10.zip', expect='unexpected fields')

    for member in ('wpcode/after.txt', 'wp-content/ts-peek/v2/navigation.js', 'wp-content/ts-peek/v2/navigation.css'):
        def rehash(entries):
            entries[member] += b' '
            m = json.loads(entries['manifest.json'])
            m['sha256'][member] = sha(entries[member])
            if member == 'wpcode/after.txt':
                m['replacement_code_sha256'] = sha(entries[member])
            entries['manifest.json'] = json.dumps(m).encode()
        make_archive('self-consistent.zip', rehash)
        refuses('self-consistent replacement ' + member, 'self-consistent.zip', expect='staging-approved release')

    php('update_post_meta(1339, "_wpcode_auto_insert", 0);')
    refuses('inactive companion', 'good.zip', expect='Companion snippet differs')
    php('update_post_meta(1339, "_wpcode_auto_insert", 1);')
    php('$cache=get_option("wpcode_snippets"); foreach ($cache["everywhere"] as &$entry) { if ((int)$entry["id"]===1343) $entry["code"].=" "; } update_option("wpcode_snippets",$cache);')
    refuses('stale navigation cache', 'good.zip', expect='Stored and cached navigation code differ')
    php('wpcode()->cache->cache_all_loaded_snippets();')
    # Site state: the archive is good and the site is made wrong, then repaired.
    original = root(f'cat {PLUGIN}')
    root(f'cp {PLUGIN} /tmp/ts40-spectra-original.php')
    try:
        root(f"sed -i '/^[[:space:]]*\\* Version:/s/2\\.20\\.7/2.20.6/' {PLUGIN}")
        refuses('active Spectra 2.20.6', 'good.zip', expect='Active Spectra is 2.20.6')
    finally:
        root(f"mv /tmp/ts40-spectra-original.php {PLUGIN}")
    check('Spectra plugin file restored', root(f'cat {PLUGIN}') == original)
    wp('plugin', 'deactivate', 'ultimate-addons-for-gutenberg')
    refuses('Spectra inactive', 'good.zip', expect='Spectra is not active')
    wp('plugin', 'activate', 'ultimate-addons-for-gutenberg')
    # wp-config evaluates WORDPRESS_CONFIG_EXTRA from the environment on every request, so override it for this run only.
    other = "define('WP_HOME','http://other.example.com'); define('WP_SITEURL','http://other.example.com');"
    refuses('wrong configured home host', 'good.zip', expect='Refusing target', extra_env=(f'WORDPRESS_CONFIG_EXTRA={other}',))
    for option in ('home', 'siteurl'):
        def stored_url(url):
            php(f'global $wpdb; $wpdb->update($wpdb->options, ["option_value"=>"{url}"], ["option_name"=>"{option}"]);')
        try:
            stored_url('http://staging40.thaisolitude.com')
            refuses(f'wrong stored {option} hidden by configuration', 'good.zip', expect='Refusing target')
        finally:
            stored_url('http://thaisolitude.com')
    wp('theme', 'activate', 'twentytwentyfive')
    refuses('Astra not active', 'good.zip', expect='Active theme is not Astra')
    wp('theme', 'activate', 'astra')

    php('wp_update_post(wp_slash(array("ID"=>1343,"post_content"=>get_post(1343)->post_content . "\\n// edited\\n")));')
    edited = state()
    check('edit really changed the code', edited[0] != V1)
    refuses('independently edited snippet (apply)', 'good.zip', expect='neither the start nor the replacement code')
    refuses('independently edited snippet (rollback)', 'good.zip', 'rollback', '/backups/none.json', expect='neither the start nor the replacement code')
    php('wp_update_post(wp_slash(array("ID"=>1343,"post_content"=>file_get_contents("/archives/before.txt"))));')
    check('manual repair restored the v1 hash', state() == start)

    php('wp_update_post(array("ID"=>1343,"post_title"=>"Renamed"));')
    refuses('renamed snippet', 'good.zip', expect='Snippet identity check failed')
    php(f'wp_update_post(array("ID"=>1343,"post_title"=>"{TITLE}"));')
    php(f'wp_insert_post(array("post_type"=>"wpcode","post_status"=>"draft","post_title"=>"{TITLE}","post_content"=>"x"));')
    refuses('duplicate snippet title', 'good.zip', expect='Snippet identity check failed')
    php(f'foreach (get_posts(array("post_type"=>"wpcode","post_status"=>"any","title"=>"{TITLE}","exclude"=>array(1343),"numberposts"=>-1)) as $p) wp_delete_post($p->ID, true);')
    php('wp_update_post(array("ID"=>1343,"post_status"=>"draft"));')
    refuses('unpublished snippet', 'good.zip', expect='Snippet identity check failed')
    php('wp_update_post(array("ID"=>1343,"post_status"=>"publish"));')
    check('status repair restored the fingerprint', state() == start)

    root(f'mkdir -p {ASSET_DIR} && echo stray > {ASSET_DIR}/navigation.css')
    refuses('different file already at a v2 URL', 'good.zip', expect='is never overwritten')
    root('rm -rf /var/www/html/wp-content/ts-peek')
    refuses('no backup directory', 'good.zip', expect='WWM_BACKUP_DIR', backup=False)
    check('still the clean baseline', state() == start)

    # The real Spectra save hook clears metadata on unrelated pages identified
    # by this option. Use a real page so each write proves it leaves that data alone.
    real_spectra = php('echo class_exists("UAGB_Helper") ? "yes" : "no";') == 'yes'
    if (STACK / 'archives/real-spectra').exists():
        check('real Spectra helper loaded', real_spectra)
    if real_spectra:
        page_id = int(php('echo wp_insert_post(["post_type"=>"page","post_title"=>"Asset sentinel"]);'))
        php(f'update_option("_uagb_fse_uniqids", [{page_id}]); update_post_meta({page_id}, "_uag_page_assets", "preserve");')

    # Apply.
    rc, out = apply_with_contenders()
    check('apply succeeds', rc == 0 and 'Applied v2' in out, out)
    if real_spectra:
        check('apply preserved Spectra page assets', php(f'echo get_post_meta({page_id}, "_uag_page_assets", true) ?: "absent";') == 'preserve')
    snapshot = out.split('Rollback snapshot: ')[1].strip()
    code, fingerprint, assets = state()
    check('stored code is the replacement hash', code == after, code)
    check('assets on disk match the manifest', assets == {n: manifest['sha256'][f'wp-content/ts-peek/v2/{n}'] for n in assets}, assets)
    check('post fields, meta and terms unchanged by apply', fingerprint == start[1])
    check('assets served byte-identically over HTTP', all(sha(fetch(f'/wp-content/ts-peek/v2/{n}')) == h for n, h in assets.items()))
    html = fetch('/').decode()
    check('page references v2 assets and carries the runtime', '/ts-peek/v2/navigation.js' in html and '/ts-peek/v2/navigation.css' in html and 'id="ts-peek-nav-runtime"' in html)
    rc, out = installer('apply', 'good.zip')
    check('second apply is a verified no-op', rc == 0 and 'already applied' in out and state()[0] == after, out)

    # Rollback refusals.
    text = Path(STACK / 'backups' / Path(snapshot).name).read_text()
    (STACK / 'backups/foreign.json').write_text(text.replace('"manifest_sha256":"', '"manifest_sha256":"0'))
    (STACK / 'backups/other-snippet.json').write_text(text.replace('"snippet_id":1343', '"snippet_id":1'))
    (STACK / 'backups/other-host.json').write_text(text.replace(HOST, 'staging40.thaisolitude.com'))
    (STACK / 'backups/broken.json').write_text('{')
    incomplete = json.loads(text)
    del incomplete['snippet_fingerprint']
    (STACK / 'backups/no-fingerprint.json').write_text(json.dumps(incomplete))
    for name, file, expect in (
        ('missing fingerprint', '/backups/no-fingerprint.json', 'fingerprint does not match'),
        ('missing snapshot', '/backups/missing.json', 'Snapshot is missing'),
        ('damaged snapshot', '/backups/broken.json', 'Snapshot is missing or damaged'),
        ('snapshot from another archive', '/backups/foreign.json', 'manifest_sha256 does not match'),
        ('snapshot for another snippet', '/backups/other-snippet.json', 'snippet_id does not match'),
        ('snapshot from another host', '/backups/other-host.json', 'does not match this site'),
    ):
        refuses(f'rollback with {name}', 'good.zip', 'rollback', file, expect=expect)
    make_archive('r1.zip', edit_manifest(host='staging40.thaisolitude.com'))
    refuses('rollback with an archive for another host', 'r1.zip', 'rollback', snapshot, expect='Manifest host differs')
    root(f'echo drift >> {ASSET_DIR}/navigation.js')
    refuses('rollback while an asset differs from the manifest', 'good.zip', 'rollback', snapshot, expect='is never overwritten')
    root(f'truncate -s -6 {ASSET_DIR}/navigation.js')
    check('asset drift repaired', state()[2] == assets)

    # Editing the note leaves the code hash valid. A refused rollback must
    # leave both the saved code and the public runtime on the current version.
    php('update_post_meta(1343, "_wpcode_note", "changed after apply");')
    refuses('independent snippet note edit', 'good.zip', 'rollback', snapshot, expect='fingerprint does not match')
    check('refused rollback still serves v2', '/ts-peek/v2/navigation.js' in fetch('/').decode())
    php('update_post_meta(1343, "_wpcode_note", "seeded");')

    # Exact rollback.
    rc, out = installer('rollback', 'good.zip', snapshot)
    check('rollback succeeds', rc == 0 and 'Rolled back' in out, out)
    if real_spectra:
        check('rollback preserved Spectra page assets', php(f'echo get_post_meta({page_id}, "_uag_page_assets", true) ?: "absent";') == 'preserve')
    after_rb = state()
    check('exact v1 bytes and fields restored', after_rb[0] == V1 and after_rb[1] == start[1], after_rb)
    check('v2 assets kept for cached pages', after_rb[2] == assets)
    html = fetch('/').decode()
    check('page is back to the inline v1 runtime', '/ts-peek/v2/' not in html and 'ts-peek' in html)
    rc, out = installer('rollback', 'good.zip', snapshot)
    check('second rollback is a verified no-op', rc == 0 and 'Nothing to roll back' in out and state() == after_rb, out)
    php('update_post_meta(1343, "_wpcode_note", "changed after rollback");')
    refuses('repeat rollback after note edit', 'good.zip', 'rollback', snapshot, expect='fingerprint does not match')
    php('update_post_meta(1343, "_wpcode_note", "seeded");')
    # Re-apply onto the kept (exact) assets, then roll back again to leave the stack on v1.
    rc, out = installer('apply', 'good.zip')
    check('re-apply onto kept assets succeeds', rc == 0 and state()[0] == after, out)
    snapshot2 = out.split('Rollback snapshot: ')[1].strip()
    rc, out = installer('rollback', 'good.zip', snapshot2)
    check('final rollback restores v1', rc == 0 and state()[0] == V1 and state()[1] == start[1], out)
    # Make the snapshot directory read-only after the assets-published record
    # exists and the snippet is saved. The previous snapshot must still work.
    php('if (!mkdir("/backups/final-write-test", 0700)) WP_CLI::error("Could not create fixture directory");')
    (STACK / 'archives/fail-final-write.php').write_text("""<?php
WP_CLI::add_hook('after_wp_load', static function () {
    add_action('save_post_wpcode', static function ($id) {
        if (1343 === $id && !chmod('/backups/final-write-test', 0500)) {
            WP_CLI::error('Could not make fixture directory read-only');
        }
    });
});
""")
    try:
        rc, out = wp('--require=/archives/fail-final-write.php', 'eval-file', '/pkg/install-v2.php',
                     'apply', '/archives/good.zip', backup=False,
                     extra_env=('WWM_BACKUP_DIR=/backups/final-write-test',))
        check('final snapshot write failure reports active v2 and exits nonzero',
              rc != 0 and 'V2 is active, but the final snapshot update could not be verified' in out, out)
        check('v2 is still serving after final snapshot failure',
              state() == (after, start[1], assets) and '/ts-peek/v2/navigation.js' in fetch('/').decode())
    finally:
        php('if (!chmod("/backups/final-write-test", 0700)) WP_CLI::error("Could not restore fixture permissions");')
    snapshot_path = php('$paths = glob("/backups/final-write-test/peek-v2-before-*.json"); '
                        'if (count($paths) !== 1) WP_CLI::error("Expected one snapshot"); echo $paths[0];')
    check('error identifies the preserved snapshot', 'rollback snapshot: ' + snapshot_path in out)
    phase = php(f'echo json_decode(file_get_contents("{snapshot_path}"), true)["phase"];')
    check('previous snapshot phase survived the failure', phase == 'assets-published', phase)
    rc, out = installer('rollback', 'good.zip', snapshot_path)
    check('preserved snapshot restores v1 after final write failure',
          rc == 0 and state() == after_rb and '/ts-peek/v2/' not in fetch('/').decode(), out)
    # Start with empty asset URLs, then make the v2 save fail its byte read-back.
    root(f'rm {ASSET_DIR}/navigation.css {ASSET_DIR}/navigation.js')
    (STACK / 'archives/change-v2-save.php').write_text("""<?php
WP_CLI::add_hook('after_wp_load', static function () {
    add_filter('wp_insert_post_data', static function ($data, $post) {
        if ((int) ($post['ID'] ?? 0) === 1343 && strpos($data['post_content'], '/ts-peek/v2/') !== false) {
            $data['post_content'] .= ' // fixture drift';
        }
        return $data;
    }, 99, 2);
});
""")
    rc, out = wp('--require=/archives/change-v2-save.php', 'eval-file', '/pkg/install-v2.php',
                 'apply', '/archives/good.zip')
    check('failed v2 read-back restored v1 and retained newly published assets',
          rc != 0 and 'v2 assets retained' in out and state() == after_rb, out)
    check('retained asset URLs serve exact bytes after failed apply',
          all(sha(fetch('/wp-content/ts-peek/v2/' + name)) == digest for name, digest in assets.items()))
    print('\nALL REHEARSAL CHECKS PASSED')


if __name__ == '__main__':
    if not STACK:
        sys.exit(__doc__)
    main()
