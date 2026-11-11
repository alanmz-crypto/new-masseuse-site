// Four actual-page cases. Root font changes approximate text enlargement;
// browser engine emulation never establishes physical Safari or OS text scale.
import { parseFocused } from '../peek-release/focused-evidence.mjs';

export const RESIZE_SLUGS = ['therapeutic-massage', 'work-with-me'];
export const RESIZE_SIDES = ['right', 'left'];
export const RESIZE_LABELS = ['font-1-enlarged', 'font-1-restored', 'font-2-enlarged', 'font-2-restored', 'font-3-enlarged', 'font-3-restored', 'viewport-shorter', 'viewport-restored', 'menu-open', 'menu-closed', 'identity', 'no-errors'];
export const RESIZE_LIMITATION = 'root text enlargement is not OS text scale; Linux WebKit is not iOS Safari.';
export const resizeCases = () => RESIZE_SLUGS.flatMap(slug => RESIZE_SIDES.map(side => `${slug}/${side}`));
export const resizeCaptures = engine => RESIZE_SLUGS.flatMap(slug => RESIZE_SIDES.flatMap(side => ['enlarged', 'restored'].map(kind => `peek-title-resize-${engine}-${slug}-${side}-${kind}.jpg`)));

export function resizeSpec(engine) {
  if (!['chromium', 'firefox', 'webkit'].includes(engine)) throw new Error('Unknown title-resize engine');
  return { family: 'title-resize', engine, name: `INFO Peek title resize ${engine}`, cases: resizeCases(), captures: resizeCaptures(engine), completion: true,
    assertionPrefix: `title-resize ${engine}/`,
    summaryPattern: new RegExp(`^INFO Peek title resize ${engine}: (\\d+) passed, (\\d+) failed; 4/4 cases, 4 page loads, 8 captures, \\d+s; root text enlargement is not OS text scale; Linux WebKit is not iOS Safari\\.$`) };
}

export function parseResize(lines, engine) {
  const parsed = parseFocused(lines, resizeSpec(engine));
  const expected = new Set(resizeCases().flatMap(item => RESIZE_LABELS.map(label => `${item}/${label}`))), seen = new Set();
  const assertion = new RegExp(`^(?:PASS|FAIL) title-resize ${engine}/([^\\r\\n]+?) \\[check \\d+\\](?: .*)?$`);
  for (const line of lines) {
    const match = assertion.exec(line);
    if (!match) continue;
    if (!expected.has(match[1]) || seen.has(match[1])) parsed.invalid.push('unexpected or duplicate title-resize assertion');
    seen.add(match[1]);
  }
  if (seen.size !== expected.size || [...expected].some(label => !seen.has(label))) parsed.invalid.push('complete title-resize assertion matrix missing');
  return parsed;
}
