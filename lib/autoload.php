<?php
/*
 * Simple Class Autoload 
*/
function autoload( $className ){
	if ( file_exists( $file = dirname( __FILE__). "/../class/".$className.".class.php" ) ){
		require ( $file ); 
	}
}
spl_autoload_register( 'autoload' );
?>
