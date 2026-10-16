"""Undo edits_about_intro.py (Ryan, 2026-10-13: "not what I needed; use your edit here again")."""
from edits_about_intro import PAGES as _P

PAGES = {pid: [(new, old) for old, new in edits] for pid, edits in _P.items()}
