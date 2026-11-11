import { runIndexClearanceGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'http://localhost:8090') {
  console.log('INVALID core-index-clearance: exact isolated localhost:8090 only; no extra arguments.');
  process.exitCode = 2;
} else process.exitCode = await runIndexClearanceGates(process.argv[2], 'rehearsal');
