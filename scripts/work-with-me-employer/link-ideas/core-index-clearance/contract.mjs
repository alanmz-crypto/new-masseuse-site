// Fixed evidence matrix, shared with the focused checker. Engine gates still
// validate every completed case, literal assertion and expected capture.
export const INDEX_SLUGS = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'services', 'corporate-massage-portland', 'work-with-me', 'privacy-policy', 'search'];
export const INDEX_SIDES = ['right', 'left'];
export const INDEX_CAPTURE_KINDS = ['short-portrait', 'landscape-top'];
export const INDEX_LIMITATION = 'viewport resizing is not Safari, physical-device or OS text-scale validation.';
export const indexCases = () => INDEX_SLUGS.flatMap(slug => INDEX_SIDES.map(side => `${slug}/${side}`));
export const indexCaptures = engine => INDEX_SLUGS.flatMap(slug => INDEX_SIDES.flatMap(side => INDEX_CAPTURE_KINDS.map(kind => `peek-index-clearance-${engine}-${slug}-${side}-${kind}.jpg`)));
