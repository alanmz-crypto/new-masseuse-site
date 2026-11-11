"""Run the shared guarded push harness for the exact v10→title-resize lane."""
from pathlib import Path
import runpy

runpy.run_path(
    str(Path(__file__).resolve().parents[1] / 'core-input-focus' / 'test-push.py'),
    init_globals={'CORE_PUSH_PROFILE': 'title-resize'},
    run_name='__main__',
)
