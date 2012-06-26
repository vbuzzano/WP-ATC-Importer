<?php
	/**
	 * Admin Importation Log Class
	 */
class ImportationLog {

	public function __construct() {}

	/**
	 * Query logs
	 *
	 * @param $mode string 'all', successfull' or 'failed'
	 */
	public function query( $mode ) {

		global $wpdb;
		$prefix = $wpdb->prefix;
		$table = $prefix.Atc::$TABLE_IMPORTATION_NAME;
		$result = null;

		if ($mode == "successfull")
			$result = $wpdb->get_results("SELECT * FROM $table WHERE error_type ='no error' ORDER BY importationDate DESC");
		else if ($mode == "failed")
			$result = $wpdb->get_results("SELECT * FROM $table WHERE error_type <>'no error' ORDER BY importationDate DESC");
		else
			$result = $wpdb->get_results("SELECT * FROM $table ORDER BY importationDate DESC");
		return $result;
	}

	/**
	 * Display Admin Option Panel
	 *
	 */
	public function displayOptionPanel() {
			
		if (function_exists('add_action'))
			add_action( 'wp_head', array( &$this, 'header'), 1 );

		echo '<div="wrap"><div id="icon-options-general" class="icon32" ><br/></div><h2>' . __( 'ATC Importation Logs', 'atc_trdom'  ) . "</h2>\n";

		// successfull imporations
		echo '<h4 style="margin-top:30px;">' . __( 'Successfull importations', 'atc_trdom' ) . '</h4>';
		$succImport = $this->query( 'successfull' );
		$this->displayLines( $succImport );

		// failed importations
		echo '<h4>' . __( 'Failed importations', 'atc_trdom' ) . '</h4>';  
		$failedImport = $this->query( "failed" );
		$this->displayLines($failedImport);
			
		// all last importations
		echo '<h4>' . __( 'Last importations', 'atc_trdom' ) . '</h4>';  
		$allImport = $this->query('all');
		$this->displayLines($allImport);

		echo "</div>\n";
	}

	/**
	 * Display Log lines
	 *
	 * @param $lines array of log lines
	 */
	public function displayLines ( array $lines ){
		$head =
			'<table class="wp-list-table widefat fixed posts" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="deliveryId" class="manage-column column-title" style="">
					<span>%s</span>
				</th>

				<th scope="col" id="title/uid" class="manage-column column-author" style="">
					<span>%s</span>
				</th>

				<th scope="col" id="date" class="manage-column column-datec" style="">
					<span>%s</span>
				</th>

				<th scope="col" id="fileNumber" class="manage-column column-tags" style="">%s
				</th>

				<th scope="col" id="errorType" class="manage-column column-tags" style="">%s
				</th>
			</tr>
			</thead>';

		printf ( $head, 'Delivery ID', 'Title/uid', 'Date', 'File\'s media nb', 'Error Type' );

		foreach(  $lines as $line ){
			// fileName is the xml file
			$deliveryId = preg_replace('#^(.+)_.+$#' ,'$1', $line->fileName);
			$fileMedia = explode(",", $line->attached_image_fileNames);
			echo "<tr>\n";
			echo '  <td>' . $deliveryId ."</td>\n";
			echo '  <td>' .'<b>' . $line->title . ' <br/>' . '</b>' . $line->uid . "</td>\n";
			echo '  <td>' . $line->importationDate . "</td>\n";
			echo '  <td>' . count ($fileMedia ) . "</td>\n";
			echo '  <td>' . $line->error_type . "</td>\n";
			echo "</tr>\n";
		}
		echo "  </table>\n";
	}
}

?>
