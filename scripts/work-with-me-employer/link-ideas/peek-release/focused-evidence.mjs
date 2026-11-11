// Release-only adapter for the existing focused checkers. Keep the staging
// aggregate guards unchanged and require their complete visitor-page matrices.
import { indexCases, indexCaptures, INDEX_LIMITATION } from '../core-index-clearance/contract.mjs';
import { quietCases, quietCaptures, QUIET_LIMITATION } from '../core-quiet-nav/contract.mjs';

const engines = ['chromium', 'firefox', 'webkit'];
const slugs = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
const sameKeys = (actual, expected) => JSON.stringify([...actual].sort()) === JSON.stringify([...expected].sort());
const escaped = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

export function focusedSpec(family, engine) {
  if (!engines.includes(engine) || !['neighbours', 'index-clearance', 'quiet-nav'].includes(family)) throw new Error('unknown release focused suite');
  const neighbours = family === 'neighbours', quiet = family === 'quiet-nav';
  const name = `INFO Peek ${neighbours ? 'neighbours' : quiet ? 'quiet nav' : 'index clearance'} ${engine}`;
  const cases = neighbours ? slugs.flatMap(slug => ['right', 'left'].map(side => `${slug}/${side}`)) : quiet ? quietCases() : indexCases();
  const captures = neighbours ? slugs.flatMap(slug => ['right', 'left'].flatMap(side => ['middle-short', 'research-landscape'].map(kind => `peek-neighbours-${engine}-${slug}-${side}-${kind}.jpg`))) : quiet ? quietCaptures(engine) : indexCaptures(engine);
  const suffix = neighbours
    ? `; 8/8 completed cases, 8 page loads, 16 captures; \\d+s; ${engine === 'chromium' ? 'trusted CDP touch and keyboard' : 'keyboard only'}; viewport resizing is not Safari, physical-device or OS text-scale validation\\.`
    : `; ${cases.length}/${cases.length} cases, ${cases.length} page loads, ${captures.length} captures, \\d+s; ${escaped(quiet ? QUIET_LIMITATION : INDEX_LIMITATION)}`;
  return { family, engine, name, cases, captures, completion: !neighbours, assertionPrefix: `${family} ${engine}/`, summaryPattern: new RegExp(`^${escaped(name)}: (\\d+) passed, (\\d+) failed${suffix}$`) };
}

export function parseFocused(lines, spec) {
  const invalid = [], summaries = [], assertedCases = new Set(), completed = new Set(), captures = new Set(), labels = new Set();
  let passed = 0, failed = 0, failureObserved = false;
  const assertionPattern = new RegExp(`^(PASS|FAIL) ${escaped(spec.assertionPrefix)}([a-z0-9-]+/(?:right|left))(?:/|: )([^\\r\\n]+?) \\[check (\\d+)\\](?: .*)?$`);
  const completionPattern = new RegExp(`^INFO ${escaped(spec.family)} completed ${spec.engine}/([a-z0-9-]+/(?:right|left))$`);
  for (const line of lines) {
    if (/^(?:INVALID|ERROR)(?:\s|$)/.test(line)) invalid.push(line);
    if (/^FAIL(?:\s|$)/.test(line)) failureObserved = true;
    const assertion = assertionPattern.exec(line);
    if (assertion) {
      assertion[1] === 'PASS' ? passed++ : failed++;
      if (Number(assertion[4]) !== passed + failed) invalid.push('focused assertion check numbers must be unique and sequential');
      if (!spec.cases.includes(assertion[2])) invalid.push('unexpected focused assertion case');
      assertedCases.add(assertion[2]);
      const label = `${assertion[2]}/${assertion[3]}`;
      // Neighbour resizing deliberately repeats a size; index geometry also
      // revisits states. Their literal sequential check numbers disambiguate.
      if (spec.family === 'quiet-nav' && labels.has(label)) invalid.push('duplicate quiet-navigation assertion label');
      labels.add(label);
    } else if (/^(PASS|FAIL)(?:\s|$)/.test(line)) invalid.push('unexpected focused assertion source or malformed check number');
    if (line.startsWith(`INFO ${spec.family} completed `)) {
      const match = completionPattern.exec(line);
      if (!spec.completion || !match || !spec.cases.includes(match[1]) || completed.has(match[1])) invalid.push('unexpected or duplicate focused completion');
      else completed.add(match[1]);
    }
    if (line.startsWith('INFO Peek ')) {
      const summary = spec.summaryPattern.exec(line);
      if (!summary) invalid.push('unexpected or incomplete focused summary');
      else summaries.push({ name: spec.name, passed: Number(summary[1]), failed: Number(summary[2]), line });
    }
    if (line.startsWith('INLINE ')) {
      const match = /^INLINE ([a-zA-Z0-9_.-]+\.jpg) base64:([A-Za-z0-9+/]+={0,2})$/.exec(line), bytes = match && Buffer.from(match[2], 'base64');
      if (!match || !spec.captures.includes(match[1]) || captures.has(match[1]) || bytes.length < 4 || bytes[0] !== 0xff || bytes[1] !== 0xd8 || bytes.at(-2) !== 0xff || bytes.at(-1) !== 0xd9) invalid.push('missing, duplicate or malformed focused screenshot');
      else captures.add(match[1]);
    }
  }
  if (summaries.length !== 1 || summaries[0].passed !== passed || summaries[0].failed !== failed || passed + failed === 0 || !sameKeys(assertedCases, spec.cases) || !sameKeys(captures, spec.captures) || (spec.completion && !sameKeys(completed, spec.cases))) invalid.push('complete focused assertion, case and capture evidence differs from its summary');
  return { summaries, passed, failed, captures: captures.size, completedCases: spec.completion ? completed.size : assertedCases.size, invalid, failureObserved };
}
