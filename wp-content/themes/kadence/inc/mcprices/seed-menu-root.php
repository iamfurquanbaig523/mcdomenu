<?php
/**
 * Menu root seeding and managed-page cleanup for McPrices.
 *
 * @package kadence
 */

namespace Kadence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps the native /menu/ page tree clean and admin-visible.
 */
class McPrices_Menu_Root_Seed {
	/**
	 * Root page slug/path.
	 */
	const ROOT_PATH = 'menu';

	/**
	 * Root page title.
	 */
	const ROOT_TITLE = 'Menu';

	/**
	 * Root page content.
	 */
	const ROOT_CONTENT = '<!-- wp:shortcode -->[mcprices_menu_directory]<!-- /wp:shortcode -->';

	/**
	 * Root page excerpt.
	 */
	const ROOT_EXCERPT = 'Browse every McDonald\'s USA menu category, then open separate item pages for prices, calories, and quick details.';

	/**
	 * Ensure the menu root page exists and remove duplicate managed pages.
	 *
	 * @return void
	 */
	public static function maybe_seed() {
		$root_page_id = self::ensure_root_page();

		if ( ! $root_page_id ) {
			return;
		}

		self::normalize_managed_hierarchy( $root_page_id );
		self::cleanup_duplicate_managed_pages( $root_page_id );
		self::normalize_managed_hierarchy( $root_page_id );
	}

	/**
	 * Create or normalize the canonical /menu/ root page.
	 *
	 * @return int
	 */
	protected static function ensure_root_page() {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'order'          => 'ASC',
			)
		);

		$candidates = array();

		foreach ( $pages as $page ) {
			$page_path   = (string) get_page_uri( $page->ID );
			$managed_key = (string) get_post_meta( $page->ID, '_mcprices_managed_key', true );
			$managed_type = (string) get_post_meta( $page->ID, '_mcprices_managed_page', true );

			if ( self::ROOT_PATH === $page_path || 'menu' === $managed_key || 'menu-root' === $managed_type ) {
				$candidates[] = $page;
			}
		}

		$root_page = self::pick_canonical_page( $candidates );

		if ( ! $root_page instanceof \WP_Post ) {
			$root_page_id = wp_insert_post(
				array(
					'post_title'   => self::ROOT_TITLE,
					'post_name'    => self::ROOT_PATH,
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_parent'  => 0,
					'menu_order'   => 0,
					'post_excerpt' => self::ROOT_EXCERPT,
					'post_content' => self::ROOT_CONTENT,
				),
				true
			);

			if ( is_wp_error( $root_page_id ) || ! $root_page_id ) {
				return 0;
			}

			$root_page = get_post( (int) $root_page_id );
		}

		if ( ! $root_page instanceof \WP_Post ) {
			return 0;
		}

		wp_update_post(
			array(
				'ID'           => (int) $root_page->ID,
				'post_title'   => self::ROOT_TITLE,
				'post_name'    => self::ROOT_PATH,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'menu_order'   => 0,
				'post_excerpt' => self::ROOT_EXCERPT,
				'post_content' => self::ROOT_CONTENT,
			)
		);

		update_post_meta( (int) $root_page->ID, '_mcprices_managed_page', 'menu-root' );
		update_post_meta( (int) $root_page->ID, '_mcprices_managed_key', 'menu' );

		return (int) $root_page->ID;
	}

	/**
	 * Remove duplicate managed menu pages while preserving the canonical tree.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function cleanup_duplicate_managed_pages( $root_page_id ) {
		$managed_pages = self::get_managed_pages();

		$groups = array();

		foreach ( $managed_pages as $page ) {
			$key = trim( (string) get_post_meta( $page->ID, '_mcprices_managed_key', true ) );

			if ( '' === $key ) {
				$key = 'path::' . (string) get_page_uri( $page->ID );
			}

			$groups[ $key ][] = $page;
		}

		foreach ( $groups as $pages ) {
			if ( count( $pages ) < 2 ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages, $root_page_id );

			if ( ! $canonical instanceof \WP_Post ) {
				continue;
			}

			foreach ( $pages as $page ) {
				if ( (int) $page->ID === (int) $canonical->ID ) {
					continue;
				}

				self::delete_duplicate_page( (int) $page->ID, (int) $canonical->ID );
			}
		}

		self::cleanup_duplicate_paths( $root_page_id );
	}

	/**
	 * Remove duplicate pages that still share the exact same path.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function cleanup_duplicate_paths( $root_page_id ) {
		$pages = self::get_managed_pages();

		$groups = array();

		foreach ( $pages as $page ) {
			$path = (string) get_page_uri( $page->ID );

			if ( '' === $path ) {
				continue;
			}

			$groups[ $path ][] = $page;
		}

		foreach ( $groups as $pages_with_path ) {
			if ( count( $pages_with_path ) < 2 ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages_with_path, $root_page_id );

			if ( ! $canonical instanceof \WP_Post ) {
				continue;
			}

			foreach ( $pages_with_path as $page ) {
				if ( (int) $page->ID === (int) $canonical->ID ) {
					continue;
				}

				self::delete_duplicate_page( (int) $page->ID, (int) $canonical->ID );
			}
		}
	}

	/**
	 * Normalize the managed page hierarchy so categories sit under /menu/ and
	 * item pages sit under their canonical category pages.
	 *
	 * @param int $root_page_id Canonical root page ID.
	 * @return void
	 */
	protected static function normalize_managed_hierarchy( $root_page_id ) {
		$managed_pages = self::get_managed_pages();
		$groups        = array();

		foreach ( $managed_pages as $page ) {
			$key = trim( (string) get_post_meta( $page->ID, '_mcprices_managed_key', true ) );

			if ( '' === $key ) {
				continue;
			}

			$groups[ $key ][] = $page;
		}

		$canonical_pages = array(
			'menu' => get_post( (int) $root_page_id ),
		);

		foreach ( $groups as $key => $pages ) {
			if ( 'menu' === $key ) {
				continue;
			}

			$canonical = self::pick_canonical_page( $pages, $root_page_id );

			if ( $canonical instanceof \WP_Post ) {
				$canonical_pages[ $key ] = $canonical;
			}
		}

		foreach ( $canonical_pages as $key => $page ) {
			if ( ! $page instanceof \WP_Post || 'menu' === $key || false !== strpos( $key, '::' ) ) {
				continue;
			}

			self::normalize_managed_page(
				(int) $page->ID,
				array(
					'post_parent' => (int) $root_page_id,
					'post_name'   => self::get_desired_slug_from_key( $key ),
				)
			);

			$canonical_pages[ $key ] = get_post( (int) $page->ID );
		}

		foreach ( $canonical_pages as $key => $page ) {
			if ( ! $page instanceof \WP_Post || false === strpos( $key, '::' ) ) {
				continue;
			}

			list( $category_key ) = explode( '::', $key, 2 );
			$parent_page = isset( $canonical_pages[ $category_key ] ) && $canonical_pages[ $category_key ] instanceof \WP_Post
				? $canonical_pages[ $category_key ]
				: null;

			self::normalize_managed_page(
				(int) $page->ID,
				array(
					'post_parent' => $parent_page instanceof \WP_Post ? (int) $parent_page->ID : (int) $root_page_id,
					'post_name'   => self::get_desired_slug_from_key( $key ),
				)
			);
		}
	}

	/**
	 * Choose the canonical page from a duplicate group.
	 *
	 * @param array $pages        Candidate pages.
	 * @param int   $root_page_id Canonical root page ID.
	 * @return \WP_Post|null
	 */
	protected static function pick_canonical_page( array $pages, $root_page_id = 0 ) {
		$pages = array_filter(
			$pages,
			static function ( $page ) {
				return $page instanceof \WP_Post;
			}
		);

		if ( empty( $pages ) ) {
			return null;
		}

		usort(
			$pages,
			static function ( $left, $right ) use ( $root_page_id ) {
				$left_score  = self::get_page_preference_score( $left, $root_page_id );
				$right_score = self::get_page_preference_score( $right, $root_page_id );

				if ( $left_score === $right_score ) {
					return (int) $left->ID - (int) $right->ID;
				}

				return $right_score - $left_score;
			}
		);

		return $pages[0];
	}

	/**
	 * Score one page candidate for canonical preference.
	 *
	 * @param \WP_Post $page         Candidate page.
	 * @param int      $root_page_id Canonical root page ID.
	 * @return int
	 */
	protected static function get_page_preference_score( \WP_Post $page, $root_page_id = 0 ) {
		$score     = 0;
		$page_path = (string) get_page_uri( $page->ID );

		if ( 'publish' === $page->post_status ) {
			$score += 1000;
		}

		if ( $root_page_id && (int) $page->ID === (int) $root_page_id ) {
			$score += 500;
		}

		if ( false === strpos( $page_path, '-2' ) ) {
			$score += 150;
		}

		if ( false === strpos( $page_path, '-3' ) ) {
			$score += 50;
		}

		$score -= strlen( $page_path );

		$children = get_children(
			array(
				'post_parent' => (int) $page->ID,
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'any',
				'fields'      => 'ids',
			)
		);

		$score += is_array( $children ) ? count( $children ) * 25 : 0;

		return $score;
	}

	/**
	 * Return all managed menu pages, including trashed duplicates.
	 *
	 * @return \WP_Post[]
	 */
	protected static function get_managed_pages() {
		return get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_mcprices_managed_page',
						'compare' => 'EXISTS',
					),
				),
			)
		);
	}

	/**
	 * Derive the intended slug from a managed page key.
	 *
	 * @param string $managed_key Managed page key.
	 * @return string
	 */
	protected static function get_desired_slug_from_key( $managed_key ) {
		$managed_key = trim( (string) $managed_key );

		if ( '' === $managed_key ) {
			return '';
		}

		if ( false !== strpos( $managed_key, '::' ) ) {
			list( , $item_slug ) = explode( '::', $managed_key, 2 );

			return sanitize_title( $item_slug );
		}

		return sanitize_title( $managed_key );
	}

	/**
	 * Normalize one managed page record.
	 *
	 * @param int   $page_id  Page ID.
	 * @param array $updates  Post fields to normalize.
	 * @return void
	 */
	protected static function normalize_managed_page( $page_id, array $updates ) {
		$page = get_post( (int) $page_id );

		if ( ! $page instanceof \WP_Post ) {
			return;
		}

		$post_data = array(
			'ID'          => (int) $page->ID,
			'post_status' => 'publish',
		);

		if ( array_key_exists( 'post_parent', $updates ) ) {
			$post_data['post_parent'] = (int) $updates['post_parent'];
		}

		if ( ! empty( $updates['post_name'] ) ) {
			$post_data['post_name'] = (string) $updates['post_name'];
		}

		wp_update_post( $post_data );
	}

	/**
	 * Remove a duplicate managed page after reassigning its children.
	 *
	 * @param int $duplicate_page_id Duplicate page ID.
	 * @param int $canonical_page_id Canonical page ID.
	 * @return void
	 */
	protected static function delete_duplicate_page( $duplicate_page_id, $canonical_page_id ) {
		if ( $duplicate_page_id === $canonical_page_id ) {
			return;
		}

		self::move_child_pages( (int) $duplicate_page_id, (int) $canonical_page_id );
		wp_delete_post( (int) $duplicate_page_id, true );
	}

	/**
	 * Reassign child pages to the canonical page before trashing duplicates.
	 *
	 * @param int $from_page_id Old parent page ID.
	 * @param int $to_page_id   New parent page ID.
	 * @return void
	 */
	protected static function move_child_pages( $from_page_id, $to_page_id ) {
		if ( $from_page_id === $to_page_id ) {
			return;
		}

		$children = get_children(
			array(
				'post_parent' => (int) $from_page_id,
				'post_type'   => 'page',
				'numberposts' => -1,
				'post_status' => 'any',
				'fields'      => 'ids',
			)
		);

		if ( empty( $children ) || ! is_array( $children ) ) {
			return;
		}

		foreach ( $children as $child_id ) {
			wp_update_post(
				array(
					'ID'          => (int) $child_id,
					'post_parent' => (int) $to_page_id,
				)
			);
		}
	}
}
