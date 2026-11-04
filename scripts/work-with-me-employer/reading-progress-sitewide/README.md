# Reading progress on every public page

Ryan's follow-up on November 4, 2026: “You can put that on every page.”
This extends the existing three-pixel gold scroll-progress indicator from the
four research pages to every public page rendered by the site's theme, including
Home, About, Services, Corporate Massage, For Practices, Contact, Privacy Policy
and Accessibility. The body selector has no page IDs, routes or citation
dependency, so future public pages also receive it.

The indicator uses the existing light/dark color token, takes no layout space,
does not intercept pointers or add keyboard stops, and adds no JavaScript or
asset requests. It remains absent for reduced motion, forced colors, printing,
unsupported browsers and logged-in views with the admin toolbar. A page without
a scroll range has an inactive timeline and an empty indicator.

## Integration and rollback

The previous `reading-progress/` CSS and installer are preserved unchanged.
`reading-progress-v1.css` here is a frozen copy of the exact deployed CSS.
The new installer verifies hashes of both payloads, then either replaces that
exact v1 block once or appends the v2 block when no indicator exists. Partial,
changed or duplicate blocks are refused. Repeat apply does nothing.

Before writing, it saves and verifies a private snapshot of the complete current
Additional CSS. Rollback verifies the snapshot identity, site, CSS post, payload
hashes and complete applied bytes before restoring the exact previous CSS. Thus
on current staging it restores the four-page version. Later CSS edits block the
rollback. To remove the original indicator too, roll back this update first and
then use the original package's original snapshot.

Only `localhost:8090` and the exact HTTP/HTTPS staging38 origins are allowed.
Production is not enabled. The push script takes a fresh DB export, verifies its
encrypted offsite copy, applies the update, then copies the exact rollback
snapshot offsite before checking all twelve public routes. Page content, booking
links, SEO, snippets and site settings are not edited.

## Workflow

Use unique request IDs with the existing Actions workflow:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer reading-progress-sitewide/install-reading-progress-sitewide.php
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh --args '--apply'
```

Use the exact snapshot printed by apply for rollback:

```sh
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh --args '--rollback $HOME/private-backups/reading-progress-sitewide-before-TIMESTAMP-SUFFIX.json'
```

The original [feature rationale and browser compatibility references](../reading-progress/README.md)
still apply. Verification of this sitewide update is recorded after its rehearsal
and staging run. Git source changes alone do not update WordPress or production.
