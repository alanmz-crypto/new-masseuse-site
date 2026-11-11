"""Run the shared decisive-cleanup fixture for the exact title-resize lane."""
from pathlib import Path
import runpy

runpy.run_path(
    str(Path(__file__).resolve().parents[1] / 'peek-release' / 'test-cleanup.py'),
    init_globals={'CLEANUP_PROFILE': 'title-resize'},
)
