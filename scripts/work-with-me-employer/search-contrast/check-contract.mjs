export const SEARCH_ROUTES = [{ name: 'results', path: '/?s=massage' }, { name: 'empty', path: '/?s=ts-no-results-7f3e0b49' }];
export const SEARCH_WIDTHS = [320, 390, 1280];
export const SEARCH_THEMES = ['light', 'dark'];
export const SEARCH_LIMITATION = 'automated browsers are not physical Safari or assistive technology.';
export const searchCases = () => SEARCH_ROUTES.flatMap(r => SEARCH_WIDTHS.flatMap(w => SEARCH_THEMES.map(t => `${r.name}/${w}/${t}`)));
export const searchReturns = () => SEARCH_ROUTES.flatMap(r => SEARCH_WIDTHS.map(w => `${r.name}/${w}/return-to-light`));
export const searchCaptures = engine => searchCases().map(label => `search-contrast-${engine}-${label.replaceAll('/', '-')}.jpg`);
export const searchAssertions = theme => [
  'original Search template and expected result state',
  'requested real theme and one served contrast block',
  'all visible main text is measurable and at least 4.5 to one',
  'panel surfaces are opaque and match the intended theme',
  'theme switch preserves content and all Peek code data and configuration',
  'accepted title strip remains present without summary data',
  'no horizontal page overflow',
  'one original page load and no JavaScript errors',
  'painted Search input submit and placeholder have at least 4.5 to one contrast',
  'Search submit hover remains readable',
  'focused Search text and placeholder remain readable',
  `screen-only correction preserves print${theme === 'light' ? ' and ordinary light colors' : ''}`
];
export const SEARCH_RETURN_ASSERTION = 'exact initial colors and content restored';
