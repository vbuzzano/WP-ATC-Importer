<?php
/**
 *  ADMIN Tools Class
 */
class Tools {
	
	public function __construct() {}

	public function displayOptionPanel( ){
		echo '<div="wrap">';
		echo "  <div id=\"icon-options-general\" class=\"icon32\" ><br/></div>\n";
		echo '  <h2>' . __( 'ATC Tools', 'atc_trdom'  ) . "</h2>\n";

		//  manual importation
		echo '<form name="atc_manal_import_form" method="post" action="'. str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ) .'">';
		echo '  <input type="hidden" name="atc_hidden_manual_import" value="Y" />';
		echo '  <h3 style="margin-top:50px;">' . __( 'Manual importation', 'atc_trdom' ) . '</h3>';

		// manual button
		echo '  <p class="submit">';
		echo '    <input type="submit" class="button-primary" name="atc_submit_manual" value="' . __( "Manual importation", "atc_trdom" ) . '"/>';

		// action's description
		echo '    <div class="description">&nbsp;&nbsp;&nbsp' . __( "for forcing one manual importation") . '</div>';
		echo '  </p>';

		echo '</form>';
		echo '<hr/>';

		//  erase importation logs'
		echo '<form name="atc_manal_import_form" method="post" action="'. str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ) .'">';
		echo '  <input type="hidden" name="atc_hidden_erase_log" value="Y" />';
		echo '  <h3 style="margin-top:50px;">' . __( 'Erase importation logs\'', 'atc_trdom' ) . '</h3>';

		// Erase importation logs' button
		echo '  <p class="submit">';
		echo '    <input type="submit" class="button-primary" name="atc_submit_manual" value="' . __( "Erase logs", "atc_trdom" ) . '"/>';

		// action's description
		echo '    <div class="description">&nbsp;&nbsp;&nbsp;' . __( "for erasing the importation logs'") . '</div>';
		echo '  </p>';

		echo '</form>';
	
		echo '</div>';
	}
}

?>
