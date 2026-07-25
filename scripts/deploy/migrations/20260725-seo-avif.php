<?php
/**
 * Targeted production migration for the managed-sync, author, sitemap, and
 * homepage AVIF release.
 *
 * Run from the WordPress root:
 * MCPRICES_MIGRATION_APPROVAL=APPLY_20260725_SEO_AVIF php scripts/deploy/migrations/20260725-seo-avif.php
 */

if ( 'cli' !== PHP_SAPI ) {
	fwrite( STDERR, "This migration may only run from the command line.\n" );
	exit( 2 );
}

$wordpress_root = dirname( __DIR__, 3 );
require $wordpress_root . '/wp-load.php';

global $wpdb;

$approval = (string) getenv( 'MCPRICES_MIGRATION_APPROVAL' );
$apply    = 'APPLY_20260725_SEO_AVIF' === $approval;
$home_id  = (int) get_option( 'page_on_front' );
$home_id  = $home_id > 0 ? $home_id : 11;

/**
 * Recursively replace the retired placeholder author identity.
 *
 * @param mixed $value Stored option or metadata value.
 * @return mixed
 */
function mcprices_migration_replace_author( $value ) {
	if ( is_string( $value ) ) {
		return str_replace(
			array(
				'Sarah Jenkins',
				'Sarah Jankins',
				'Sarah tracks',
				'Sarah reviews',
				'Sarah helps',
				'Sarah focuses',
				', Lead Menu Analyst & Senior Editor',
				', Lead Menu Analyst &amp; Senior Editor',
				', Lead Menu Analyst and Senior Editor based in the United States',
				', Senior Editor',
			),
			array(
				'David Livingstone',
				'David Livingstone',
				'David tracks',
				'David reviews',
				'David helps',
				'David focuses',
				'',
				'',
				'',
				'',
			),
			$value
		);
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = mcprices_migration_replace_author( $item );
		}
	}

	if ( is_object( $value ) ) {
		foreach ( get_object_vars( $value ) as $key => $item ) {
			$value->{$key} = mcprices_migration_replace_author( $item );
		}
	}

	return $value;
}

/**
 * Replace homepage image references only when the corresponding AVIF exists.
 *
 * @param string $content Stored post content.
 * @return string
 */
function mcprices_migration_convert_content_images( $content ) {
	$content = (string) mcprices_migration_replace_author( $content );
	$content = preg_replace(
		'/(Mcdonalds-Breakfast|mcdonalds-meal-prices-calories-usa)(-\d+x\d+)?\.webp\b/i',
		'$1$2.avif',
		$content
	);

	$content = preg_replace_callback(
		'#((?:https?:)?//[^/"\'\s]+)?((?:/wordpress)?/wp-content/themes/kadence/assets/images/mcprices/(?:homepage/items|official/categories)/[^"\'\s?]+)\.(?:jpe?g|png|webp)(\?[^"\'\s]*)?#i',
		static function ( $matches ) {
			$url_path       = (string) $matches[2];
			$wp_content_pos = strpos( $url_path, '/wp-content/' );

			if ( false === $wp_content_pos ) {
				return $matches[0];
			}

			$relative_path = ltrim( substr( $url_path, $wp_content_pos ), '/' );
			$avif_relative = $relative_path . '.avif';

			if ( ! is_file( ABSPATH . $avif_relative ) ) {
				return $matches[0];
			}

			$avif_url_path = $url_path . '.avif';

			return (string) $matches[1] . $avif_url_path . ( isset( $matches[3] ) ? (string) $matches[3] : '' );
		},
		$content
	);

	return (string) $content;
}

/**
 * Return an attachment metadata payload that matches deployed AVIF files.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $stem          Filename without extension.
 * @return array<string,mixed>
 */
function mcprices_migration_attachment_metadata( $attachment_id, $stem ) {
	$upload   = wp_get_upload_dir();
	$base_dir = trailingslashit( $upload['basedir'] ) . '2026/07/';
	$existing = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
	$metadata = is_array( $existing ) ? $existing : array();
	$main     = $base_dir . $stem . '.avif';

	if ( ! is_file( $main ) ) {
		throw new RuntimeException( 'Required AVIF is missing: ' . $main );
	}

	$metadata['width']    = 1672;
	$metadata['height']   = 941;
	$metadata['file']     = '2026/07/' . $stem . '.avif';
	$metadata['filesize'] = filesize( $main );
	$metadata['sizes']    = array();

	$sizes = array(
		'thumbnail'  => array( 150, 150 ),
		'medium'     => array( 300, 169 ),
		'medium_large' => array( 768, 432 ),
		'large'      => array( 1024, 576 ),
		'1536x1536'  => array( 1536, 864 ),
	);

	foreach ( $sizes as $key => $dimensions ) {
		list( $width, $height ) = $dimensions;
		$filename               = $stem . '-' . $width . 'x' . $height . '.avif';
		$path                   = $base_dir . $filename;

		if ( ! is_file( $path ) ) {
			throw new RuntimeException( 'Required responsive AVIF is missing: ' . $path );
		}

		$metadata['sizes'][ $key ] = array(
			'file'      => $filename,
			'width'     => $width,
			'height'    => $height,
			'mime-type' => 'image/avif',
			'filesize'  => filesize( $path ),
		);
	}

	return $metadata;
}

$canonical_user = get_user_by( 'id', 1 );

if ( ! $canonical_user instanceof WP_User ) {
	$users = get_users(
		array(
			'search'         => 'David Livingstone',
			'search_columns' => array( 'user_login', 'display_name' ),
			'number'         => 1,
		)
	);
	$canonical_user = ! empty( $users[0] ) && $users[0] instanceof WP_User ? $users[0] : null;
}

if ( ! $canonical_user instanceof WP_User ) {
	fwrite( STDERR, "A real David Livingstone WordPress user could not be identified.\n" );
	exit( 1 );
}

$canonical_user_id = (int) $canonical_user->ID;
$authorless_count  = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('page','post') AND post_status='publish' AND post_author <> %d",
		$canonical_user_id
	)
);
$sarah_posts       = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='publish' AND CONCAT(post_title,post_excerpt,post_content) LIKE '%Sarah%'"
);
$candidate_posts   = $wpdb->get_results(
	"SELECT ID,post_author,post_title,post_excerpt,post_content,post_modified,post_modified_gmt
	FROM {$wpdb->posts}
	WHERE (post_type IN ('page','post') AND post_status='publish' AND post_author <> " . (int) $canonical_user_id . ")
	OR post_content LIKE '%Sarah%'
	OR post_content LIKE '%Lead Menu Analyst%'
	OR post_content LIKE '%Senior Editor%'
	OR post_content LIKE '%Mcdonalds-Breakfast.webp%'
	OR post_content LIKE '%mcdonalds-meal-prices-calories-usa.webp%'
	OR post_content LIKE '%/assets/images/mcprices/%'",
	ARRAY_A
);

$attachment_definitions = array(
	array(
		'old'  => '2026/07/Mcdonalds-Breakfast.webp',
		'new'  => '2026/07/Mcdonalds-Breakfast.avif',
		'stem' => 'Mcdonalds-Breakfast',
	),
	array(
		'old'  => '2026/07/mcdonalds-meal-prices-calories-usa.webp',
		'new'  => '2026/07/mcdonalds-meal-prices-calories-usa.avif',
		'stem' => 'mcdonalds-meal-prices-calories-usa',
	),
);
$attachments = array();

foreach ( $attachment_definitions as $definition ) {
	$attachment_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			WHERE meta_key='_wp_attached_file' AND meta_value IN (%s,%s)
			ORDER BY post_id ASC LIMIT 1",
			$definition['old'],
			$definition['new']
		)
	);

	if ( ! $attachment_id ) {
		throw new RuntimeException( 'Attachment record not found for ' . $definition['old'] );
	}

	$attachments[] = array(
		'definition' => $definition,
		'post'       => get_post( $attachment_id, ARRAY_A ),
		'attached'   => get_post_meta( $attachment_id, '_wp_attached_file', true ),
		'metadata'   => get_post_meta( $attachment_id, '_wp_attachment_metadata', true ),
	);
}

echo 'MODE=' . ( $apply ? 'apply' : 'dry-run' ) . "\n";
echo 'CANONICAL_USER_ID=' . $canonical_user_id . "\n";
echo 'NON_CANONICAL_PUBLISHED_AUTHORS=' . $authorless_count . "\n";
echo 'SARAH_PUBLISHED_POSTS=' . $sarah_posts . "\n";
echo 'CANDIDATE_POST_ROWS=' . count( $candidate_posts ) . "\n";
echo 'ATTACHMENTS=' . count( $attachments ) . "\n";

if ( ! $apply ) {
	echo "No changes applied. Set MCPRICES_MIGRATION_APPROVAL=APPLY_20260725_SEO_AVIF to continue.\n";
	exit( 0 );
}

$backup_dir = (string) getenv( 'MCPRICES_BACKUP_DIR' );
$backup_dir = '' !== trim( $backup_dir ) ? $backup_dir : WP_CONTENT_DIR . '/uploads/mcprices-deploy-backups';

if ( ! wp_mkdir_p( $backup_dir ) ) {
	throw new RuntimeException( 'Could not create migration backup directory: ' . $backup_dir );
}

$backup_file = trailingslashit( $backup_dir ) . '20260725-seo-avif-' . gmdate( 'Ymd-His' ) . '.json';
$backup      = array(
	'created_at'         => gmdate( 'c' ),
	'home_id'            => $home_id,
	'canonical_user_id'  => $canonical_user_id,
	'users'              => $wpdb->get_results( "SELECT ID,user_login,display_name FROM {$wpdb->users} ORDER BY ID", ARRAY_A ),
	'candidate_posts'    => $candidate_posts,
	'attachments'        => $attachments,
	'managed_cron'       => wp_next_scheduled( 'mcprices_run_managed_bootstrap' ),
	'managed_transient'  => get_transient( 'mcprices_managed_bootstrap_scheduled' ),
);

if ( false === file_put_contents( $backup_file, wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) ) {
	throw new RuntimeException( 'Could not write migration backup: ' . $backup_file );
}

$wpdb->query( 'START TRANSACTION' );
$changed_posts       = 0;
$meaningful_posts    = 0;
$changed_meta        = 0;
$changed_options     = 0;
$changed_usermeta    = 0;
$changed_attachments = 0;

try {
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_author=%d WHERE post_type IN ('page','post') AND post_status='publish' AND post_author <> %d",
			$canonical_user_id,
			$canonical_user_id
		)
	);

	$users_to_normalize = get_users( array( 'fields' => 'all' ) );
	foreach ( $users_to_normalize as $user ) {
		if ( ! $user instanceof WP_User ) {
			continue;
		}

		$should_normalize = (int) $user->ID === $canonical_user_id
			|| 'Sarah Jenkins' === (string) $user->display_name
			|| false !== stripos( (string) $user->display_name, 'David Livingstone' )
			|| false !== stripos( (string) $user->user_login, 'David Livingstone' );

		if ( $should_normalize && 'David Livingstone' !== (string) $user->display_name ) {
			$wpdb->update(
				$wpdb->users,
				array( 'display_name' => 'David Livingstone' ),
				array( 'ID' => (int) $user->ID ),
				array( '%s' ),
				array( '%d' )
			);
			clean_user_cache( (int) $user->ID );
		}
	}

	$posts_to_transform = $wpdb->get_results(
		"SELECT ID,post_status,post_content FROM {$wpdb->posts}
		WHERE post_content LIKE '%Sarah%'
		OR post_content LIKE '%Lead Menu Analyst%'
		OR post_content LIKE '%Senior Editor%'
		OR post_content LIKE '%Mcdonalds-Breakfast.webp%'
		OR post_content LIKE '%mcdonalds-meal-prices-calories-usa.webp%'
		OR post_content LIKE '%/assets/images/mcprices/%'",
		ARRAY_A
	);

	foreach ( $posts_to_transform as $row ) {
		$original    = (string) $row['post_content'];
		$transformed = mcprices_migration_convert_content_images( $original );

		if ( $original === $transformed ) {
			continue;
		}

		$meaningful = false !== strpos( $original, 'Sarah' )
			|| false !== strpos( $original, 'Lead Menu Analyst' )
			|| false !== strpos( $original, 'Senior Editor' );

		if ( $meaningful && 'publish' === (string) $row['post_status'] ) {
			$result = wp_update_post(
				array(
					'ID'           => (int) $row['ID'],
					'post_content' => $transformed,
				),
				true
			);

			if ( is_wp_error( $result ) ) {
				throw new RuntimeException( $result->get_error_message() );
			}
			$meaningful_posts++;
		} else {
			$wpdb->update(
				$wpdb->posts,
				array( 'post_content' => $transformed ),
				array( 'ID' => (int) $row['ID'] ),
				array( '%s' ),
				array( '%d' )
			);
			clean_post_cache( (int) $row['ID'] );
		}

		$changed_posts++;
	}

	foreach ( $attachments as $attachment ) {
		$definition    = $attachment['definition'];
		$attachment_id = (int) $attachment['post']['ID'];
		$upload        = wp_get_upload_dir();
		$guid          = trailingslashit( $upload['baseurl'] ) . $definition['new'];
		$metadata      = mcprices_migration_attachment_metadata( $attachment_id, $definition['stem'] );
		$post          = is_array( $attachment['post'] ) ? $attachment['post'] : array();
		$needs_update  = 'image/avif' !== (string) ( $post['post_mime_type'] ?? '' )
			|| $guid !== (string) ( $post['guid'] ?? '' )
			|| $definition['new'] !== (string) $attachment['attached']
			|| maybe_serialize( $metadata ) !== maybe_serialize( $attachment['metadata'] );

		if ( ! $needs_update ) {
			continue;
		}

		$wpdb->update(
			$wpdb->posts,
			array(
				'post_mime_type' => 'image/avif',
				'guid'           => $guid,
			),
			array( 'ID' => $attachment_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		update_post_meta( $attachment_id, '_wp_attached_file', $definition['new'] );
		update_post_meta( $attachment_id, '_wp_attachment_metadata', $metadata );
		clean_post_cache( $attachment_id );
		$changed_attachments++;
	}

	$meta_rows = $wpdb->get_results(
		"SELECT meta_id,post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%Sarah%'",
		ARRAY_A
	);
	foreach ( $meta_rows as $row ) {
		$value       = maybe_unserialize( $row['meta_value'] );
		$transformed = mcprices_migration_replace_author( $value );
		if ( $value !== $transformed ) {
			update_post_meta( (int) $row['post_id'], (string) $row['meta_key'], $transformed );
			$changed_meta++;
		}
	}

	$option_rows = $wpdb->get_results(
		"SELECT option_name,option_value FROM {$wpdb->options}
		WHERE option_value LIKE '%Sarah%'
		AND option_name NOT LIKE '\\_transient\\_%'
		AND option_name NOT LIKE '\\_site\\_transient\\_%'",
		ARRAY_A
	);
	foreach ( $option_rows as $row ) {
		$value       = maybe_unserialize( $row['option_value'] );
		$transformed = mcprices_migration_replace_author( $value );
		if ( $value !== $transformed ) {
			update_option( (string) $row['option_name'], $transformed );
			$changed_options++;
		}
	}

	$usermeta_rows = $wpdb->get_results(
		"SELECT umeta_id,user_id,meta_key,meta_value FROM {$wpdb->usermeta} WHERE meta_value LIKE '%Sarah%'",
		ARRAY_A
	);
	foreach ( $usermeta_rows as $row ) {
		$value       = maybe_unserialize( $row['meta_value'] );
		$transformed = mcprices_migration_replace_author( $value );
		if ( $value !== $transformed ) {
			update_user_meta( (int) $row['user_id'], (string) $row['meta_key'], $transformed );
			$changed_usermeta++;
		}
	}

	if ( class_exists( '\Kadence\McPrices_Integration' ) ) {
		$integration = \Kadence\McPrices_Integration::get_instance();
		$reflection  = new ReflectionClass( $integration );
		$signatures  = array(
			'get_menu_directory_signature'      => 'mcprices_menu_directory_signature',
			'get_page_category_signature'       => 'mcprices_page_category_signature',
			'get_seeded_support_pages_signature' => 'mcprices_support_page_signature',
		);

		foreach ( $signatures as $method_name => $option_name ) {
			if ( ! $reflection->hasMethod( $method_name ) ) {
				continue;
			}
			$method = $reflection->getMethod( $method_name );
			$method->setAccessible( true );
			update_option( $option_name, (string) $method->invoke( $integration ), false );
		}
	}

	wp_clear_scheduled_hook( 'mcprices_run_managed_bootstrap' );
	delete_transient( 'mcprices_managed_bootstrap_scheduled' );
	flush_rewrite_rules( false );

	$remaining_noncanonical = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ('page','post') AND post_status='publish' AND post_author <> %d",
			$canonical_user_id
		)
	);
	$remaining_sarah       = (int) $wpdb->get_var(
		"SELECT
		(SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status='publish' AND CONCAT(post_title,post_excerpt,post_content) LIKE '%Sarah%')
		+ (SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%Sarah%')
		+ (SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_value LIKE '%Sarah%'
			AND option_name NOT LIKE '\\_transient\\_%'
			AND option_name NOT LIKE '\\_site\\_transient\\_%')
		+ (SELECT COUNT(*) FROM {$wpdb->users} WHERE display_name LIKE '%Sarah%')
		+ (SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_value LIKE '%Sarah%')"
	);

	if ( 0 !== $remaining_noncanonical || 0 !== $remaining_sarah ) {
		throw new RuntimeException( 'Post-migration author verification failed.' );
	}

	$wpdb->query( 'COMMIT' );
} catch ( Throwable $exception ) {
	$wpdb->query( 'ROLLBACK' );
	fwrite( STDERR, 'Migration rolled back: ' . $exception->getMessage() . "\n" );
	exit( 1 );
}

echo 'BACKUP_FILE=' . $backup_file . "\n";
echo 'CHANGED_POSTS=' . $changed_posts . "\n";
echo 'MEANINGFUL_PUBLISHED_CHANGES=' . $meaningful_posts . "\n";
echo 'CHANGED_ATTACHMENTS=' . $changed_attachments . "\n";
echo 'CHANGED_POSTMETA=' . $changed_meta . "\n";
echo 'CHANGED_OPTIONS=' . $changed_options . "\n";
echo 'CHANGED_USERMETA=' . $changed_usermeta . "\n";
echo "MIGRATION_STATUS=success\n";
