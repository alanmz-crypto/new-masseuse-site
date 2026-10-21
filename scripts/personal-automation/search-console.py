#!/home/lauer/butsaba-contact-tool/.venv/bin/python
"""Read-only Search Console reports for Thai Solitude using Butsaba's account."""
import argparse
from datetime import date, datetime, timedelta
import json
import os
from pathlib import Path
import sys
import tempfile
from urllib.parse import quote, urlparse
from zoneinfo import ZoneInfo

from google.auth.transport.requests import AuthorizedSession, Request
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import InstalledAppFlow

ACCOUNT = 'butsabad@gmail.com'
CLIENT = Path('/home/lauer/butsaba-contact-tool/credentials.json')
DIRECTORY = Path('/home/lauer/.config/butsaba-search-console')
TOKEN = DIRECTORY / 'token.json'
SCOPES = ['openid', 'https://www.googleapis.com/auth/userinfo.email',
          'https://www.googleapis.com/auth/webmasters.readonly']
PROPERTIES = ('sc-domain:thaisolitude.com', 'https://thaisolitude.com/',
              'https://www.thaisolitude.com/', 'http://thaisolitude.com/',
              'http://www.thaisolitude.com/')
BASE = 'https://www.googleapis.com/webmasters/v3'


def private_write(path, contents):
    path.parent.mkdir(mode=0o700, parents=True, exist_ok=True)
    if path.parent.stat().st_mode & 0o077:
        raise RuntimeError('Credential directory must be private (mode 700).')
    fd, temporary = tempfile.mkstemp(prefix='.oauth-', dir=path.parent)
    try:
        with os.fdopen(fd, 'w') as handle:
            handle.write(contents)
            handle.flush()
            os.fsync(handle.fileno())
        os.replace(temporary, path)
    finally:
        if os.path.exists(temporary):
            os.unlink(temporary)


def request(session, method, url, **kwargs):
    response = session.request(method, url, timeout=45, **kwargs)
    if not response.ok:
        try:
            error = response.json().get('error', {})
            reasons = [item['reason'] for item in error.get('details', []) if 'reason' in item]
        except (ValueError, AttributeError):
            reasons = []
        if 'SERVICE_DISABLED' in reasons:
            raise RuntimeError('Enable Search Console API: https://console.cloud.google.com/apis/library/searchconsole.googleapis.com?project=temporal-wares-512003-m9')
        raise RuntimeError(f'Google API returned HTTP {response.status_code}; reasons={reasons}.')
    return response.json()


def identity(session):
    profile = request(session, 'GET', 'https://openidconnect.googleapis.com/v1/userinfo')
    if profile.get('email', '').lower() != ACCOUNT or profile.get('email_verified') is not True:
        raise RuntimeError('The verified Google account is not butsabad@gmail.com; refusing to use it.')


def sites(session):
    data = request(session, 'GET', BASE + '/sites')
    entries = {entry['siteUrl']: entry for entry in data.get('siteEntry', [])}
    return [entries[url] for url in PROPERTIES if url in entries and entries[url].get('permissionLevel') != 'siteUnverifiedUser']


def pick_site(session):
    available = sites(session)
    if not available:
        raise RuntimeError('Authorization works, but this account has no verified access to a Thai Solitude property. Add or share the property at https://search.google.com/search-console')
    return available[0]['siteUrl']


def load_session():
    if not TOKEN.exists():
        raise RuntimeError('Run authorize first.')
    if TOKEN.stat().st_mode & 0o077:
        raise RuntimeError('Token file must be private (mode 600).')
    creds = Credentials.from_authorized_user_file(str(TOKEN))
    if set(creds.scopes or []) != set(SCOPES):
        raise RuntimeError('Stored token scopes differ from the reviewed read-only connection.')
    if not creds.valid:
        creds.refresh(Request())
        private_write(TOKEN, creds.to_json())
    session = AuthorizedSession(creds)
    identity(session)
    return session


def authorize():
    flow = InstalledAppFlow.from_client_secrets_file(str(CLIENT), SCOPES)
    creds = flow.run_local_server(port=0, timeout_seconds=600, prompt='consent', login_hint=ACCOUNT,
                                 success_message='Google authorization received. Return to the chat for verification.')
    if not creds.refresh_token:
        raise RuntimeError('No refresh token returned; existing configuration preserved.')
    lifetime = flow.oauth2session.token.get('refresh_token_expires_in')
    if lifetime is not None and int(lifetime) <= 604800:
        raise RuntimeError('Google returned a refresh token limited to seven days or less; check Audience publishing status.')
    session = AuthorizedSession(creds)
    identity(session)
    creds.refresh(Request())
    identity(session)
    available = sites(session)
    if TOKEN.exists():
        backup = DIRECTORY / ('token-before-' + datetime.now().strftime('%Y%m%d-%H%M%S') + '.json')
        if backup.exists():
            raise RuntimeError('Rollback filename already exists; retry later.')
        private_write(backup, TOKEN.read_text())
    private_write(TOKEN, creds.to_json())
    print(json.dumps({'account': ACCOUNT, 'authorization': 'saved', 'refresh_test': 'passed',
                      'access': 'read-only', 'thai_solitude_properties': available}, indent=2))
    if not available:
        print('NEXT: add or share the Thai Solitude property at https://search.google.com/search-console', file=sys.stderr)


def iso_date(value):
    try:
        return date.fromisoformat(value)
    except ValueError as error:
        raise argparse.ArgumentTypeError('Use YYYY-MM-DD.') from error


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    sub = parser.add_subparsers(dest='command', required=True)
    sub.add_parser('authorize', help='Authorize read-only Search Console access and basic account identity.')
    sub.add_parser('status', help='Verify the account and list only accessible Thai Solitude properties.')
    report = sub.add_parser('report', help='Read final search performance data; defaults to 28 days ending three days ago.')
    report.add_argument('--start', type=iso_date)
    report.add_argument('--end', type=iso_date)
    report.add_argument('--dimension', choices=('date', 'page', 'query'), default='date')
    sub.add_parser('sitemaps', help='Read the property’s submitted sitemap status.')
    inspect = sub.add_parser('inspect', help='Read Google’s indexed status for one Thai Solitude URL.')
    inspect.add_argument('url')
    args = parser.parse_args()
    if args.command == 'authorize':
        authorize()
        return
    session = load_session()
    if args.command == 'status':
        print(json.dumps({'account': ACCOUNT, 'access': 'read-only', 'thai_solitude_properties': sites(session)}, indent=2))
        return
    site = pick_site(session)
    encoded = quote(site, safe='')
    if args.command == 'report':
        end = args.end or (datetime.now(ZoneInfo('America/Los_Angeles')).date() - timedelta(days=3))
        start = args.start or (end - timedelta(days=27))
        if start > end:
            raise RuntimeError('Start date must not follow end date.')
        data = request(session, 'POST', f'{BASE}/sites/{encoded}/searchAnalytics/query', json={
            'startDate': start.isoformat(), 'endDate': end.isoformat(), 'dimensions': [args.dimension],
            'rowLimit': 250, 'dataState': 'final', 'type': 'web'})
        result = {'property': site, 'start': start.isoformat(), 'end': end.isoformat(),
                  'dimension': args.dimension, 'row_limit': 250, 'data': data}
    elif args.command == 'sitemaps':
        result = {'property': site, 'data': request(session, 'GET', f'{BASE}/sites/{encoded}/sitemaps')}
    else:
        url = urlparse(args.url)
        if url.scheme not in ('http', 'https') or url.hostname not in ('thaisolitude.com', 'www.thaisolitude.com') or url.username or url.password or url.port:
            raise RuntimeError('URL inspection is limited to ordinary Thai Solitude web URLs.')
        result = request(session, 'POST', 'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect',
                         json={'inspectionUrl': args.url, 'siteUrl': site, 'languageCode': 'en-US'})
    print(json.dumps(result, indent=2))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(str(error) if isinstance(error, RuntimeError) else f'Connection stopped: {type(error).__name__}', file=sys.stderr)
        raise SystemExit(1)

# ref: ./privacy-tools.html — disclosed read-only website reporting and account verification
# ref: /home/lauer/butsaba-contact-tool/credentials.json — existing Production desktop OAuth client; credentials remain outside this repository
