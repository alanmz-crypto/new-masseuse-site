// Focused quiet-navigation evidence matrix. The prior index-clearance contract
// remains separate and runs unchanged once before these checks.
export const QUIET_SLUGS = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'services', 'corporate-massage-portland', 'work-with-me', 'privacy-policy', 'search'];
export const QUIET_SIDES = ['right', 'left'];
export const QUIET_CAPTURE_KINDS = ['idle-reading', 'index-open'];
export const QUIET_LIMITATION = 'browser touch emulation is not physical-device, Safari or assistive-technology validation.';
export const quietCases = () => QUIET_SLUGS.flatMap(slug => QUIET_SIDES.map(side => `${slug}/${side}`));
export const quietCaptures = engine => QUIET_SLUGS.flatMap(slug => QUIET_SIDES.flatMap(side => QUIET_CAPTURE_KINDS.map(kind => `peek-quiet-nav-${engine}-${slug}-${side}-${kind}.jpg`)));
