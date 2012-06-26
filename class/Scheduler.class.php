<?php

class Scheduler {

	public function __construct() {}

	public function addJob( $script1Path, $cronReccurence, $script2Path, $logScript2, $siteUrl, $logScript1 ){
			echo "<br/><br/> To enable auto importation, please create a cron job to call this url : " . $siteUrl ;
	}
}

?>
