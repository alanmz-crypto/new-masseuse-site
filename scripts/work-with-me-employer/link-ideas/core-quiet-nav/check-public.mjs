import { runQuietNavGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'https://staging39.thaisolitude.com') {
  console.log('INVALID core-quiet-nav: exact https://staging39.thaisolitude.com only; no extra arguments.');
  process.exitCode = 2;
} else process.exitCode = await runQuietNavGates(process.argv[2], 'public');
