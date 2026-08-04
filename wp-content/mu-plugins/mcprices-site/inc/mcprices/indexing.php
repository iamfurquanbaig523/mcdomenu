<?php
/**
 * Shared indexability helpers for the McPrices integration.
 *
 * Sitemap generation is intentionally owned by Rank Math.
 *
 * @package kadence
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a robots meta value into a lower-case flat string.
 *
 * @param mixed $value Raw robots value.
 * @return string
 */
function kadence_mcprices_normalize_robots_value( $value ) {
	if ( empty( $value ) ) {
		return '';
	}

	$value = maybe_unserialize( $value );

	if ( is_array( $value ) ) {
		$normalized = array();

		foreach ( $value as $key => $item ) {
			if ( is_string( $key ) && is_bool( $item ) ) {
				if ( $item ) {
					$normalized[] = strtolower( $key );
				}
				continue;
			}

			if ( is_scalar( $item ) ) {
				$normalized[] = strtolower( trim( (string) $item ) );
			}
		}

		return implode( ',', array_filter( $normalized ) );
	}

	return strtolower( trim( (string) $value ) );
}

/**
 * Determine whether a post should be treated as noindex.
 *
 * @param WP_Post $post Post object.
 * @return bool
 */
function kadence_mcprices_post_is_noindex( WP_Post $post ) {
	if ( 'publish' !== $post->post_status ) {
		return true;
	}

	$yoast_noindex = get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true );
	if ( '1' === (string) $yoast_noindex || 'noindex' === kadence_mcprices_normalize_robots_value( $yoast_noindex ) ) {
		return true;
	}

	$rank_math_robots = kadence_mcprices_normalize_robots_value( get_post_meta( $post->ID, 'rank_math_robots', true ) );
	if ( false !== strpos( $rank_math_robots, 'noindex' ) ) {
		return true;
	}

	$generic_robots = kadence_mcprices_normalize_robots_value( get_post_meta( $post->ID, 'robots', true ) );

	return false !== strpos( $generic_robots, 'noindex' );
}

/**
 * Determine whether a term should be treated as noindex.
 *
 * @param WP_Term $term Term object.
 * @return bool
 */
function kadence_mcprices_term_is_noindex( WP_Term $term ) {
	$yoast_taxonomy_meta = get_option( 'wpseo_taxonomy_meta', array() );

	if ( isset( $yoast_taxonomy_meta[ $term->taxonomy ][ $term->term_id ] ) ) {
		$term_meta    = $yoast_taxonomy_meta[ $term->taxonomy ][ $term->term_id ];
		$yoast_noindex = '';

		if ( isset( $term_meta['wpseo_noindex'] ) ) {
			$yoast_noindex = $term_meta['wpseo_noindex'];
		} elseif ( isset( $term_meta['noindex'] ) ) {
			$yoast_noindex = $term_meta['noindex'];
		}

		$yoast_noindex = kadence_mcprices_normalize_robots_value( $yoast_noindex );

		if ( false !== strpos( $yoast_noindex, 'noindex' ) || '1' === $yoast_noindex ) {
			return true;
		}
	}

	$rank_math_robots = kadence_mcprices_normalize_robots_value( get_term_meta( $term->term_id, 'rank_math_robots', true ) );

	return false !== strpos( $rank_math_robots, 'noindex' );
}

/**
 * Determine whether the whole site is set to noindex.
 *
 * @return bool
 */
function kadence_mcprices_site_is_noindex() {
	return '0' === (string) get_option( 'blog_public', '1' );
}
