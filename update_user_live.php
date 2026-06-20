<?php
require_once('wp-load.php');

$users = get_users();
$updated = false;
foreach ( $users as $user ) {
    if ( strpos($user->display_name, 'Livingstone') !== false || strpos($user->display_name, 'Editorial') !== false || $user->ID == 1 ) {
        wp_update_user( array( 'ID' => $user->ID, 'display_name' => 'Sarah Jenkins' ) );
        echo "Updated User ID: " . $user->ID . " to Sarah Jenkins\n";
        $updated = true;
    }
}
if (!$updated) {
	echo "No users found matching David Livingstone or ID 1.";
}
