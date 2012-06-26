<?php
/**
 *  ADMIN Settings Class
 */
class Settings{

	public function __construct() {}

	/**
	 * Display All ATC Themes -> Wordpress categories Mapping
	 */
	public function displayMappingLines (){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$table = $prefix.Atc::$TABLE_CATEGORY_MAPPING_NAME;
		$lines = $wpdb->get_results( "SELECT * FROM $table");

		// table header
		echo "<h3 style=\"margin-top:30px;\">" . __( 'Category mapping', 'atc_trdom' ) . "</h3>\n";
		echo "<table class=\"wp-list-table widefat fixed posts\" cellspacing=\"0\">\n";
		echo "  <thead>\n";
		echo "    <tr>\n";
		echo "      <th scope=\"col\" id=\"code\" class=\"manage-column column-tags\">ATC Theme Code</th>\n";
		echo "      <th scope=\"col\" id=\"category\" class=\"manage-column column-tags\">WP Category</th>\n";
		echo "    </tr>\n";
		echo "  </thead>\n";

		foreach( $lines as $line) {
			echo "  <tr>\n";
			echo "    <td>" . $line->code . "</td>\n";
			echo "    <td>" . get_cat_name($line->wp_category_id) . "</td>\n";
			echo "  </tr>\n";
		}

		echo "</table>\n";
	}

	/*
	 * Display Admin Option Panel
	 * @param $importPath  path to contents to import
	 * @param $emailError  email address in case of error
	 * @param $autopublish true/false
	 */
	public function displayOptionPanel( $importPath, $emailError, $autopublish ) {
?>
<div="wrap">
  <div id="icon-options-general" class="icon32" >
    <br/>
  </div>
  <h2> <?=__( 'ATC Settings Options', 'atc_trdom'  )?></h2>
  <form name="atc_settings_form" method="post" action="<?=str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] )?>">
    <input type="hidden" name="atc_hidden" value="Y" />

    <h3 style="margin-top:50px;"><?=__( 'Importation', 'atc_trdom' )?></h3>
    <p>
      <?=_e( "Importation's directory: ")?> <input type="text" name="atc_importPath" value="<?=$importPath?>" size="30"/>
      <div class="description"><?=_e( " (required) ex: wp-content/plugins/ATC_importation")?></div>
    </p>
    
    <br/>
    <br/>
    <hr/>
    
    <h3><?=__( 'Failure feed-back', 'atc_trdom' )?></h3>
    <p>
      <?=_e( "Email : ")?> <input type="text" name="atc_emailError" value="<?=$emailError?>" size="30"/>
      <div class="description"><?=_e( " (optional) ex: larry.wall@gmail.com")?></div>
	</p>

    <br/>
    <br/>
    <hr/>

    <h3><?=__( 'Content autopublish or publication after agreement', 'atc_trdom' )?></h3>
    <p>
<?
	if($autopublish) {
		echo '<span style="margin:20px;"><label>autopublish (publish status) &nbsp;&nbsp;';
		echo '<input type="radio" id="autopublish" name="atc_autopublish" value="autopublish" checked="checked"/></label></span>';
		echo '<span style="margin:20px;"><label>no autopublish (pending status) &nbsp;&nbsp;';
		echo '<input type="radio" id="noautopublish" name="atc_autopublish" value="noautopublish" /></label></span>';
	} else {
		echo '<span style="margin:20px;"><label>autopublish (publish status) &nbsp;&nbsp;';
		echo '<input type="radio" id="autopublish" name="atc_autopublish" value="autopublish" /></label></span>';
		echo '<span style="margin:20px;"><label>no autopublish (pending status) &nbsp;&nbsp;';
		echo '<input type="radio" id="noautopublish" name="atc_autopublish" checked="checked" value="noautopublish" /></label></span>';
	}
?>
    </p>

    <br/>
    <br/>
    <hr/>

	<?$this->displayMappingLines();?>

    <br/>

    <p class="submit">
      <input type="submit" class="button-primary" name="atc_submit" value="<?=_e( "Save All Changes", "atc_trdom" )?>"/>
    </p>
  </form>
</div>
<?
	}

}
?>
