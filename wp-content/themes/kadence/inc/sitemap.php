<?php
/**
 * Dynamic XML sitemap for McPrices UK.
 *
 * @package kadence
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the sitemap.xml rewrite rule.
 *
 * @return void
 */
function kadence_mcprices_register_sitemap_rewrite() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?kadence_mcprices_sitemap=1', 'top' );
}
add_action( 'init', 'kadence_mcprices_register_sitemap_rewrite' );

/**
 * Register the custom sitemap query var.
 *
 * @param array $vars Public query vars.
 * @return array
 */
function kadence_mcprices_register_sitemap_query_var( $vars ) {
	$vars[] = 'kadence_mcprices_sitemap';

	return $vars;
}
add_filter( 'query_vars', 'kadence_mcprices_register_sitemap_query_var' );

/**
 * Flush rewrite rules after a theme switch so sitemap.xml resolves.
 *
 * @return void
 */
function kadence_mcprices_flush_sitemap_rewrite() {
	kadence_mcprices_register_sitemap_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kadence_mcprices_flush_sitemap_rewrite' );

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
		$term_meta = $yoast_taxonomy_meta[ $term->taxonomy ][ $term->term_id ];
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

/**
 * Return the homepage/front page object when available.
 *
 * @return WP_Post|null
 */
function kadence_mcprices_get_front_page_object() {
	$front_page_id = (int) get_option( 'page_on_front' );

	if ( $front_page_id > 0 ) {
		$front_page = get_post( $front_page_id );

		if ( $front_page instanceof WP_Post ) {
			return $front_page;
		}
	}

	$home_page = get_page_by_path( 'home' );

	return $home_page instanceof WP_Post ? $home_page : null;
}

/**
 * Return the best last modified value for homepage-based URLs.
 *
 * @return string
 */
function kadence_mcprices_get_home_lastmod() {
	$front_page = kadence_mcprices_get_front_page_object();

	if ( $front_page instanceof WP_Post ) {
		return get_post_modified_time( 'c', true, $front_page );
	}

	return gmdate( 'c' );
}

/**
 * Build a sitemap entry.
 *
 * @param string $loc        Absolute URL.
 * @param string $lastmod    W3C datetime.
 * @param string $changefreq Change frequency.
 * @param string $priority   Priority value.
 * @return array<string, string>
 */
function kadence_mcprices_build_sitemap_entry( $loc, $lastmod, $changefreq, $priority ) {
	return array(
		'loc'        => $loc,
		'lastmod'    => $lastmod,
		'changefreq' => $changefreq,
		'priority'   => $priority,
	);
}

/**
 * Build the static sitemap URLs.
 *
 * @return array<int, array<string, string>>
 */
function kadence_mcprices_get_static_sitemap_entries() {
	$entries      = array();
	$home_lastmod = kadence_mcprices_get_home_lastmod();
	$front_page   = kadence_mcprices_get_front_page_object();

	if ( ! kadence_mcprices_site_is_noindex() && ( ! $front_page instanceof WP_Post || ! kadence_mcprices_post_is_noindex( $front_page ) ) ) {
		$static_urls = array(
			array( home_url( '/' ), 'weekly', '1.0' ),
			array( home_url( '/#full-menu' ), 'weekly', '1.0' ),
			array( home_url( '/#deals' ), 'weekly', '1.0' ),
			array( home_url( '/#blog' ), 'weekly', '1.0' ),
		);

		$category_urls = array(
			home_url( '/#burgers' ),
			home_url( '/#breakfast' ),
			home_url( '/#mccafe' ),
			home_url( '/#mcflurry' ),
			home_url( '/#saver' ),
			home_url( '/#nuggets' ),
			home_url( '/#wraps' ),
			home_url( '/#happy-meal' ),
			home_url( '/#drinks' ),
			home_url( '/#sides' ),
			home_url( '/#desserts' ),
			home_url( '/#salads' ),
			home_url( '/#vegetarian' ),
			home_url( '/#sharers' ),
			home_url( '/#sauces' ),
			home_url( '/#under400' ),
			home_url( '/#whats-new' ),
		);

		foreach ( $static_urls as $static_url ) {
			$entries[] = kadence_mcprices_build_sitemap_entry( $static_url[0], $home_lastmod, $static_url[1], $static_url[2] );
		}

		foreach ( $category_urls as $category_url ) {
			$entries[] = kadence_mcprices_build_sitemap_entry( $category_url, $home_lastmod, 'weekly', '0.9' );
		}
	}

	$info_pages = array(
		'about',
		'privacy-policy',
		'cookie-policy',
		'contact',
		'disclaimer',
		'ad-disclosure',
		'sitemap',
	);

	foreach ( $info_pages as $slug ) {
		$page = get_page_by_path( $slug );

		if ( ! $page instanceof WP_Post || kadence_mcprices_post_is_noindex( $page ) ) {
			continue;
		}

		$entries[] = kadence_mcprices_build_sitemap_entry(
			get_permalink( $page ),
			get_post_modified_time( 'c', true, $page ),
			'yearly',
			'0.5'
		);
	}

	return $entries;
}

/**
 * Build sitemap entries for published posts.
 *
 * @return array<int, array<string, string>>
 */
function kadence_mcprices_get_post_sitemap_entries() {
	$entries = array();
	$query   = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post || kadence_mcprices_post_is_noindex( $post ) ) {
				continue;
			}

			$permalink = get_permalink( $post );

			if ( empty( $permalink ) ) {
				continue;
			}

			$entries[] = kadence_mcprices_build_sitemap_entry(
				$permalink,
				get_post_modified_time( 'c', true, $post ),
				'monthly',
				'0.8'
			);
		}
	}

	wp_reset_postdata();

	return $entries;
}

/**
 * Return the full sitemap entry list.
 *
 * @return array<int, array<string, string>>
 */
function kadence_mcprices_get_sitemap_entries() {
	return array_merge(
		kadence_mcprices_get_static_sitemap_entries(),
		kadence_mcprices_get_post_sitemap_entries()
	);
}

/**
 * Output the XML sitemap response.
 *
 * @return void
 */
function kadence_mcprices_render_xml_sitemap() {
	if ( '1' !== (string) get_query_var( 'kadence_mcprices_sitemap' ) ) {
		return;
	}

	$entries = kadence_mcprices_get_sitemap_entries();

	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );

	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

	foreach ( $entries as $entry ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( $entry['loc'] ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( $entry['lastmod'] ) . "</lastmod>\n";
		echo "\t\t<changefreq>" . esc_html( $entry['changefreq'] ) . "</changefreq>\n";
		echo "\t\t<priority>" . esc_html( $entry['priority'] ) . "</priority>\n";
		echo "\t</url>\n";
	}

	echo "</urlset>";
	exit;
}
add_action( 'template_redirect', 'kadence_mcprices_render_xml_sitemap', 0 );

/**
 * Schedule a sitemap ping when relevant content changes.
 *
 * @return void
 */
function kadence_mcprices_schedule_sitemap_ping( ...$ignored ) {
	if ( false !== get_transient( 'kadence_mcprices_sitemap_ping_scheduled' ) ) {
		return;
	}

	set_transient( 'kadence_mcprices_sitemap_ping_scheduled', '1', 10 * MINUTE_IN_SECONDS );

	if ( ! wp_next_scheduled( 'kadence_mcprices_ping_sitemap_search_engines' ) ) {
		wp_schedule_single_event( time() + 15, 'kadence_mcprices_ping_sitemap_search_engines' );
	}
}

/**
 * Schedule a sitemap ping when a post is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function kadence_mcprices_schedule_sitemap_ping_for_post( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
		return;
	}

	kadence_mcprices_schedule_sitemap_ping();
}
add_action( 'save_post', 'kadence_mcprices_schedule_sitemap_ping_for_post', 10, 2 );
add_action( 'deleted_post', 'kadence_mcprices_schedule_sitemap_ping' );
add_action( 'trashed_post', 'kadence_mcprices_schedule_sitemap_ping' );
add_action( 'created_term', 'kadence_mcprices_schedule_sitemap_ping' );
add_action( 'edited_term', 'kadence_mcprices_schedule_sitemap_ping' );
add_action( 'delete_term', 'kadence_mcprices_schedule_sitemap_ping' );

/**
 * Ping search engines with the sitemap URL.
 *
 * @return void
 */
function kadence_mcprices_ping_sitemap_search_engines() {
	$sitemap_url = rawurlencode( home_url( '/sitemap.xml' ) );
	$ping_urls   = array(
		'https://www.google.com/ping?sitemap=' . $sitemap_url,
		'https://www.bing.com/ping?sitemap=' . $sitemap_url,
	);

	foreach ( $ping_urls as $ping_url ) {
		wp_remote_get(
			$ping_url,
			array(
				'timeout'    => 5,
				'blocking'   => false,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
			)
		);
	}

	delete_transient( 'kadence_mcprices_sitemap_ping_scheduled' );
}
add_action( 'kadence_mcprices_ping_sitemap_search_engines', 'kadence_mcprices_ping_sitemap_search_engines' );
