// One pure evidence parser for staging and first-install release gates.
import { searchCases, searchReturns, searchCaptures, searchAssertions, SEARCH_RETURN_ASSERTION } from './check-contract.mjs';

export function parseSearchContrast(raw, err, engine) {
  if (!['chromium', 'firefox'].includes(engine)) return { passed: 0, failed: 0, cases: 0, captures: 0, invalid: ['unsupported Search browser engine'], summary: null };
  const cases = searchCases(), returnCases = searchReturns(), invalid = [];
  const same = (a, b) => JSON.stringify([...a].sort()) === JSON.stringify([...b].sort());
  const expectedCaptures = searchCaptures(engine);
  const numbers = [], assertions = new Set(), completed = new Set(), measures = new Set(), captures = new Set(), summaries = [];
  const caseAssertions = new Map(); let passed = 0, failed = 0;
  for (const line of raw.split(/\r?\n/)) {
    if (/^(INVALID|ERROR)(?:\s|$)/.test(line)) invalid.push(line);
    const assertion = new RegExp(`^(PASS|FAIL) search-contrast ${engine}/([^/]+/\\d+/(?:light|dark|return-to-light))/(.+) \\[check (\\d+)\\](?: .*)?$`).exec(line);
    if (assertion) {
      assertion[1] === 'PASS' ? passed++ : failed++;
      if (![...cases, ...returnCases].includes(assertion[2]) || assertions.has(`${assertion[2]}/${assertion[3]}`)) invalid.push('unexpected or duplicate assertion');
      assertions.add(`${assertion[2]}/${assertion[3]}`); numbers.push(Number(assertion[4]));
      if (!caseAssertions.has(assertion[2])) caseAssertions.set(assertion[2], new Set());
      caseAssertions.get(assertion[2]).add(assertion[3]);
    } else if (/^(PASS|FAIL)(?:\s|$)/.test(line)) invalid.push('malformed or unexpected assertion');
    if (line.startsWith('INFO Search contrast completed ')) {
      const prefix = `INFO Search contrast completed ${engine}/`, label = line.slice(prefix.length);
      if (!line.startsWith(prefix) || !cases.includes(label) || completed.has(label)) invalid.push('unexpected or duplicate completed case');
      else completed.add(label);
    }
    if (line.startsWith(`INFO Search contrast ${engine}/`)) {
      const marker = new RegExp(`^INFO Search contrast ${engine}/([^:]+): (.+)$`).exec(line);
      try {
        const value = marker && JSON.parse(marker[2]);
        if (!marker || !cases.includes(marker[1]) || measures.has(marker[1]) || !Array.isArray(value.rows) || value.rows.length < 2 || !Array.isArray(value.surfaces) || !value.surfaces.length) throw new Error('missing complete measured evidence');
        measures.add(marker[1]);
      } catch (error) { invalid.push(error.message); }
    }
    if (line.startsWith('INLINE ')) {
      const match = /^INLINE ([a-z0-9-]+\.jpg) base64:([A-Za-z0-9+/]+={0,2})$/.exec(line), jpeg = match && Buffer.from(match[2], 'base64');
      if (!match || !expectedCaptures.includes(match[1]) || captures.has(match[1]) || jpeg.length < 4 || jpeg[0] !== 0xff || jpeg[1] !== 0xd8 || jpeg.at(-2) !== 0xff || jpeg.at(-1) !== 0xd9) invalid.push('unexpected, duplicate or invalid JPEG');
      else captures.add(match[1]);
    }
    if (line.startsWith(`INFO Search contrast ${engine}:`)) summaries.push(line);
  }
  const summary = summaries.length === 1 && new RegExp(`^INFO Search contrast ${engine}: CHECK, (\\d+) passed, (\\d+) failed; 12/12 cases, 6 page loads, 12 captures; \\d+s; evidence .+; automated browsers are not physical Safari or assistive technology\\.$`).exec(summaries[0]);
  if (!summary || Number(summary[1]) !== passed || Number(summary[2]) !== failed) invalid.push('missing or inconsistent final summary');
  if (numbers.some((n, i) => n !== i + 1) || !same(completed, cases) || !same(measures, cases) || !same(captures, expectedCaptures) || !same(caseAssertions.keys(), [...cases, ...returnCases]) || cases.some(c => !same(caseAssertions.get(c) || [], searchAssertions(c.split('/').at(-1)))) || returnCases.some(c => !same(caseAssertions.get(c) || [], [SEARCH_RETURN_ASSERTION]))) invalid.push('incomplete exact assertions, cases, measures or captures');
  if (err.trim()) invalid.push(`browser stderr: ${err.trim()}`);
  return { passed, failed, cases: completed.size, captures: captures.size, invalid, summary: summaries[0] || null };
}
