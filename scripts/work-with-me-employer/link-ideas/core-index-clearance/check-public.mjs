import { runIndexClearanceGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'https://staging39.thaisolitude.com') {
  console.log('INVALID core-index-clearance: exact staging39 origin only; no extra arguments.');
  process.exitCode = 2;
} else process.exitCode = await runIndexClearanceGates(process.argv[2], 'public');
