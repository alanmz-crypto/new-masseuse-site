// Read-only staging39 checks; this adapter never invokes an installer.
import { runGates } from './run-gates.mjs';
if (process.argv[2] !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) {
  console.log('INVALID Kiro fixes public verification: exact staging39 only; no extra arguments.'); process.exit(2);
}
process.exit(await runGates(process.argv[2], 'public'));
