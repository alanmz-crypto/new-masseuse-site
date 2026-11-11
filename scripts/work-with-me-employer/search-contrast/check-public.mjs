import { runSearchContrast } from './check-gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'https://staging39.thaisolitude.com') {
  console.log('INVALID Search contrast public: exact staging39 only.'); process.exitCode = 2;
} else process.exitCode = await runSearchContrast(process.argv[2], 'public');
