import { runQuietNavGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'http://localhost:8090') {
  console.log('INVALID core-quiet-nav: exact http://localhost:8090 only; no extra arguments.');
  process.exitCode = 2;
} else process.exitCode = await runQuietNavGates(process.argv[2], 'rehearsal');
