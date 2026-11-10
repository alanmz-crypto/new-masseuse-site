// Isolated staging39-copy verification; no installer or site writes here.
import { runNeighbourGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'http://localhost:8090') {
  console.log('INVALID core-neighbours rehearsal: exact localhost:8090 only; no extra arguments.'); process.exit(2);
}
process.exit(await runNeighbourGates(process.argv[2], 'rehearsal'));
