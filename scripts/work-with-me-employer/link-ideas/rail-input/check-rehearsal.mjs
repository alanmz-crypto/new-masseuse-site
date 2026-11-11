// Corrected practice-page keyboard/native touch and both full title matrices.
import { runInputGates } from '../input-gates.mjs';
if (process.argv[2] !== 'http://localhost:8090' || process.argv.length !== 3) {
  console.log('INVALID rail-input rehearsal: exact localhost:8090 only; no extra arguments.'); process.exit(2);
}
process.exit(await runInputGates(process.argv[2], 'rehearsal', 'rail-input'));
