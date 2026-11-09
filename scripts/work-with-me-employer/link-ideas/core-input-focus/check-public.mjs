// Read-only staging39 verification; no installer is invoked here.
import { runInputGates } from '../input-gates.mjs';
if (process.argv[2] !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) {
  console.log('INVALID core-input-focus public: exact staging39 only; no extra arguments.'); process.exit(2);
}
process.exit(await runInputGates(process.argv[2], 'public', 'core-input-focus'));
