<?php

/**
 * Admin Settings page
 */

$importPathErrors = null;

 // data are sent to the actual page
if ($_POST['atc_hidden'] == 'Y') {
	
	// importation directory
	$importPath = trim($_POST['atc_importPath']);

	// prepare directory
	$directoryCreated = wp_mkdir_p(ABSPATH.$importPath);
	if( $directoryCreated ) {
		wp_mkdir_p(ABSPATH.$importPath."/success");
		wp_mkdir_p(ABSPATH.$importPath."/failure");
	} else {
		$importPathError = "Cannot find import path and cannot create it ! Please give me the right permission";
	}
	
	// update path option
	update_option('atc_importPath', $importPath);

	// email
	$emailError = trim($_POST['atc_emailError']);
	update_option('atc_emailError' , $emailError );

	// autopublish
	$selected = $_POST['atc_autopublish'];
	$autopublish = null;

	if($selected == "noautopublish") {
		update_option('atc_autopublish', "false");
		$autopublish = false;	
	} else {
		update_option('atc_autopublish', "true");
		$autopublish = true;
	}

	// message
	if (is_null($importPathErrors))
	echo '<div class="updated"><p><strong>';
	echo _e('Options saved.');

	$optPath = get_option('atc_importPath');
	if(!empty($optPath)) {
		$scheduler = new Scheduler();
		$siteUrl = get_option('siteurl');
		$dir = dirname(__FILE__);
		$scheduler->addJob($dir . "/bin/atc_addJob.sh", self::CRON_RECCURRENCE, $dir . "/bin/atc-load.sh", $dir . "/logs/load.log", $siteUrl,  $dir . "/logs/cron.log");
	} else {
		echo "<br/>Please specify the path directory";
	}

    if (! is_null($importPathError)) { echo "<div class='error'>" . $importPathError . "</div>"; }

	echo '</strong></p></div>';

} else {

	// pre-population of the previously saved parameters
	$importPath = get_option( 'atc_importPath' );
	$emailError = get_option( 'atc_emailError' );

	// set by default radio button on "no autopublish"
	$publishOption = get_option('atc_autopublish'); 
	if ( empty( $publishOption ) ){
		$autopublish = false;
	} else if (  $publishOption == "true" ) {
		$autopublish = true;
	}
}

$settings = new Settings( );
$settings->displayOptionPanel( $importPath, $emailError, $autopublish );

?>
