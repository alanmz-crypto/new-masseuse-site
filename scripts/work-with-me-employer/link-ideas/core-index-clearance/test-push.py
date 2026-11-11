"""Run the shared guarded push harness against the exact v8→frozen-v9 index-clearance lane.

The historical input-focus entry keeps its independent v6→v7 profile. Both run
real Bash with local command doubles; neither contacts a site or models PHP as
real WordPress verification.
"""
from pathlib import Path
import runpy

runpy.run_path(
    str(Path(__file__).resolve().parents[1] / 'core-input-focus' / 'test-push.py'),
    init_globals={'CORE_PUSH_PROFILE': 'index-clearance'},
    run_name='__main__',
)
