<?php

/**
 * Admin Tools page
 */

// data are sent to the actual page
if ($_POST['atc_hidden_manual_import'] == 'Y') {
	// force manual import
	$this->checkFeed();
	echo '<div class="updated">';
		echo '<p>';
			echo '<strong>';
				echo _e('Importation\'s process completed.');
			echo '</strong>';
		echo '</p>';
	echo '</div>';
}

// data are sent to the actual page
if ($_POST['atc_hidden_erase_log'] == 'Y') {
	// remove importation log's from the data base
	$table = $wpdb->prefix.self::$TABLE_IMPORTATION_NAME;
	$deleteRows = "DELETE FROM $table";
	$wpdb->query( $deleteRows );
	echo '<div class="updated">';
		echo '<p>';
			echo '<strong>';
				echo _e('Importation logs\' have been removed.');
			echo '</strong>';
		echo '</p>';
	echo '</div>';
}

$tools = new Tools( );
$tools->displayOptionPanel( );
?>
