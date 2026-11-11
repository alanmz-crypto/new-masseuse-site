// Read-only checks of the deployed staging39 candidate.
import { runNeighbourGates } from './gates.mjs';
if (process.argv.length !== 3 || process.argv[2] !== 'https://staging39.thaisolitude.com') {
  console.log('INVALID core-neighbours public: exact staging39 only; no extra arguments.'); process.exit(2);
}
process.exit(await runNeighbourGates(process.argv[2], 'public'));
