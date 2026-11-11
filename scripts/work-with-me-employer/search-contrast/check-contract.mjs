export const SEARCH_ROUTES = [{ name: 'results', path: '/?s=massage' }, { name: 'empty', path: '/?s=ts-no-results-7f3e0b49' }];
export const SEARCH_WIDTHS = [320, 390, 1280];
export const SEARCH_THEMES = ['light', 'dark'];
export const SEARCH_LIMITATION = 'automated browsers are not physical Safari or assistive technology.';
export const searchCases = () => SEARCH_ROUTES.flatMap(r => SEARCH_WIDTHS.flatMap(w => SEARCH_THEMES.map(t => `${r.name}/${w}/${t}`)));
export const searchReturns = () => SEARCH_ROUTES.flatMap(r => SEARCH_WIDTHS.map(w => `${r.name}/${w}/return-to-light`));
export const searchCaptures = engine => searchCases().map(label => `search-contrast-${engine}-${label.replaceAll('/', '-')}.jpg`);
