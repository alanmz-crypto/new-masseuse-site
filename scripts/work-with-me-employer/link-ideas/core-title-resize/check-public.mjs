import { runTitleResizeGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'https://staging39.thaisolitude.com') {
  console.log('INVALID core-title-resize: exact https://staging39.thaisolitude.com only; no extra arguments.');
  process.exitCode = 2;
} else process.exitCode = await runTitleResizeGates(process.argv[2], 'public');
