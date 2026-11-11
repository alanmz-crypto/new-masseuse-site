"""Exercise the real staging-only quiet-nav rehearsal with fixed local doubles."""
from pathlib import Path
import runpy

runpy.run_path(
    str(Path(__file__).resolve().parents[1] / 'core-neighbours' / 'test-rehearsal.py'),
    init_globals={'CORE_REHEARSAL_PROFILE': 'quiet-nav'},
    run_name='__main__',
)
