# Site op `surerank-keys-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `seo-scan/plugin-keys.sh`  |
| lane | ops (site-ops.yml) |
| commit | `c7d184b4ca46187a602feefcae35d898c42b78a3` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120364088 |
| started | 2026-10-30 20:04:34 UTC |

## plugin-keys.sh (S_TARGET=staging38): PASS

```text
plugin version: 1.10.2
-- files that mention facebook_image
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/schema/products.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/slimseo/slim-seo.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/slimseo/constants.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/yoast/constants.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/squirrly/squirrly.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/seopress/constants.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/seopress/seopress.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/aioseo/aioseo.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/aioseo/constants.php
/home/<ssh-user>/www/staging38.thaisolitude.com/public_html/wp-content/plugins/surerank/inc/importers/seo-framework/seo-framework.php
-- the lines (php only)
108:		'wpseo_opengraph-image'       => [ 'social-image-url-tax', 'facebook_image_url' ],
109:		'rank_math_facebook_image'       => [ '', 'facebook_image_url' ],
109:		'wpseo_opengraph-image-id'    => [ 'social-image-id-tax', 'facebook_image_id' ],
110:		'rank_math_facebook_image_id'    => [ '', 'facebook_image_id' ],
112:		'wpseo_twitter-image'         => [ '', 'twitter_image_url' ],
113:		'rank_math_twitter_image'        => [ '', 'twitter_image_url' ],
113:		'wpseo_twitter-image-id'      => [ '', 'twitter_image_id' ],
114:		'rank_math_twitter_image_id'     => [ '', 'twitter_image_id' ],
136:		'_social_image_url'       => 'facebook_image_url',
137:		'_social_image_id'        => 'facebook_image_id',
138:		'_yoast_wpseo_opengraph-image'       => [ 'social-image-url', 'facebook_image_url' ],
139:		'_yoast_wpseo_opengraph-image-id'    => [ 'social-image-id', 'facebook_image_id' ],
141:		'og_image_custom_url'      => [ '', 'facebook_image_url' ],
142:		'_yoast_wpseo_twitter-image'         => [ '', 'twitter_image_url' ],
142:		'og_image_custom_id'       => [ '', 'facebook_image_id' ],
143:		'_yoast_wpseo_twitter-image-id'      => [ '', 'twitter_image_id' ],
145:		'twitter_image_custom_url' => [ '', 'twitter_image_url' ],
146:		'twitter_image_custom_id'  => [ '', 'twitter_image_id' ],
149:		'social_image_url' => 'facebook_image_url',
150:		'social_image_id'  => 'facebook_image_id',
153:			'facebook_image_url'   => $meta['facebook_image_url'] ?? $auto_generated_og_image,
154:		'open_graph_frontpage_image' => 'home_page_facebook_image_url',
156:			'twitter_image_url'    => $meta['twitter_image_url'] ?? $auto_generated_og_image,
157:		'homepage_facebook_image' => 'home_page_facebook_image_url',
199:		$facebook_image_url = $meta['facebook_image_url'] ?? '';
200:		$facebook_image_id  = ! empty( $meta['facebook_image_id'] ) ? (int) $meta['facebook_image_id'] : 0;
202:		$facebook_image_details = self::get_image_dimensions( $facebook_image_url, $facebook_image_id );
204:		$meta['facebook_image_height'] = $facebook_image_details['height'];
205:		$meta['facebook_image_width']  = $facebook_image_details['width'];
207:			} elseif ( 'facebook_image_url' === $key ) {
208:				$get_featured_image         = get_the_post_thumbnail_url( $post_id );
209:				$social_image               = ! empty( $get_featured_image ) ? $get_featured_image : '';
210:				$meta['facebook_image_url'] = $social_image;
213:				if ( empty( $meta['twitter_image_url'] ) && empty( $meta['twitter_same_as_facebook'] ) ) {
214:					$meta['twitter_image_url'] = $social_image;
238:				'facebook_image_url'       => '',
241:				'facebook_image_id'        => '',
242:				'twitter_image_url'        => '',
245:				'twitter_image_id'         => '',
250:					'facebook_image_url'       => '',
251:					'facebook_image_id'        => 0,
252:					'facebook_image_width'     => 0,
253:					'facebook_image_height'    => 0,
257:					'twitter_image_url'        => '',
258:					'twitter_image_id'         => 0,
292:			'facebook_image_url' => 'facebook_image_id',
293:			'twitter_image_url'  => 'twitter_image_id',
294:			'featured_image_url' => 'featured_image_id',
296:		$thumbnail_id  = get_post_thumbnail_id( $post_id );
299:			'facebook_image_url'        => '',
300:			'facebook_image_id'         => 0,
301:			'facebook_image_width'      => 0,
302:			'facebook_image_height'     => 0,
306:			'twitter_image_url'         => '',
307:			'twitter_image_id'          => 0,
314:				if ( 'featured_image_url' === $url_field ) {
326:				if ( in_array( $slim_key, [ 'facebook_image', 'twitter_image' ], true ) ) {
32:		'home_page_facebook_image_url',
33:		'home_page_twitter_image_url',
359:		$image->get( $meta_data, 'facebook_image_url' );
```

**RESULT: PASS** - plugin-keys.sh succeeded

_finished 2026-10-30 20:04:41 UTC_
