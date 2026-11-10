// Use the unchanged neighbour contract exactly once. Setting exitCode allows
// its forwarded captures and final aggregate to drain before this process ends.
import { runNeighbourGates } from '../core-neighbours/gates.mjs';
const [base, mode] = process.argv.slice(2);
if (process.argv.length !== 4 || !['public', 'rehearsal'].includes(mode)) {
  console.log('INVALID core-index-clearance retained adapter: exact origin and mode required.');
  process.exitCode = 2;
} else process.exitCode = await runNeighbourGates(base, mode);
