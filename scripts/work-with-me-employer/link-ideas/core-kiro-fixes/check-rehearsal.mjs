// New disclosure/layout gates plus every retained title/rail/core rehearsal.
import { runGates } from './run-gates.mjs';
if (process.argv[2] !== 'http://localhost:8090' || process.argv.length !== 3) {
  console.log('INVALID Kiro fixes rehearsal: isolated localhost:8090 only; no extra arguments.'); process.exit(2);
}
process.exit(await runGates(process.argv[2], 'rehearsal'));
