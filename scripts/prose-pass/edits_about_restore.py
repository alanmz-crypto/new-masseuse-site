"""Follow-up to edits.py (Ryan, 2026-10-13): restore the About page's "Learn About
Thai Solitude" paragraph and its three accordion items to their original wording.
They were already first person; the pass turned her statements of commitment and
values into instructions to the reader, and the section lost its sense.
"""
from edits import PAGES as _PASS

_RESTORE = (
    "Thai Solitude is my independent practice,",
    "\"question\":\"My Commitment to Your Wellness\"",
    "\"question\":\"Massage as Part of Everyday Care\"",
    "\"question\":\"Values That Guide Every Session\"",
)

PAGES = {298: [(new, old) for old, new in _PASS[298] if old.startswith(_RESTORE)]}
assert len(PAGES[298]) == len(_RESTORE), "expected exactly four restores"
