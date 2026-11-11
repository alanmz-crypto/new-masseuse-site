import { runSearchContrast } from './check-gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'http://localhost:8090') {
  console.log('INVALID Search contrast rehearsal: exact isolated localhost:8090 only.'); process.exitCode = 2;
} else process.exitCode = await runSearchContrast(process.argv[2], 'rehearsal');
