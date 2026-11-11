// Retain the complete index-clearance graph once. Let stdout drain naturally.
import { runIndexClearanceGates } from '../core-index-clearance/gates.mjs';
const [base, mode] = process.argv.slice(2);
if (process.argv.length !== 4 || !['public', 'rehearsal'].includes(mode)) {
  console.log('INVALID core-quiet-nav retained adapter: exact origin and mode required.');
  process.exitCode = 2;
} else process.exitCode = await runIndexClearanceGates(base, mode);
