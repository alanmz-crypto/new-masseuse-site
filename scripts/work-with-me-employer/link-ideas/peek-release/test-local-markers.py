#!/usr/bin/env python3
"""Parse actual PHP model-emitted local markers; never rewrite marker fields."""
import importlib.util
from pathlib import Path
import sys

here=Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('release_evidence',here/'rehearsal-evidence.py')
evidence=importlib.util.module_from_spec(spec);spec.loader.exec_module(evidence)
root=Path(sys.argv[1]); manifest=str(root/'manifest.json')
evidence.binding(root/'applied.log',manifest,'applied')
evidence.binding(root/'original.log',manifest,'original')
evidence.main(['warm',str(root/'preflight.log'),str(root/'preflight.log'),manifest])
evidence.main(['original',str(root/'applied.log'),str(root/'original.log'),manifest])
print('PASS actual PHP-emitted local preflight/apply/rollback markers satisfy the unchanged rehearsal parser.')
