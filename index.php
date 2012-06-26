<?php

/*
Plugin Name: WP ATC Importer
Plugin URI: http://www.allthecontent.com/
Description: Plugin for importation of content feed
Author: all-the-content.com
Version: 1.0
Author URI: http://www.allthecontent.com/
*/

// classes autoload
require_once(  dirname( __FILE__ ).'/lib/autoload.php' );

if (! class_exists('Atc')) {

class Atc {

		// cron reccurrence in minutes
		const  CRON_RECCURRENCE = 1;

		// file's extension for parsing
		static  $FEED_EXTENSION = "_generic.xml";

		// name of the xsd for validation's process
		static $XSD_FILE_NAME;

		// data base table Importation
		static $TABLE_IMPORTATION_NAME = "atc_importationLog";
		
		// data base table categoryMapping
		static $TABLE_CATEGORY_MAPPING_NAME = "atc_categoryMapping";

		// options available within the plugin
		static $OPTIONS = array( 'atc_importPath', 'atc_emailError', 'atc_autopublish' );
		
		
		// attributes to strip within the tag's xml feed
		static $TAGS_TO_STRIP = array( 'length', 'characters', 'words', 'seconds'   );
		
		// this static property has been added for the post's publication date
		static $USE_PUBLICATION_DATE_IN_FEED = true;
		
		// used by content's importation from old properties site		
		static $IMPORTATION_PROPERTIES = true;
		
		// set license and other meta data for imported content
		private $post_meta_box = null;


		/**
		 * Constructor
		 *
		 * @param $xsdFileName path to the xsd file
		 */
		public function __construct( $xsdFileName ){

			self::$XSD_FILE_NAME = $xsdFileName;

			$this->register_additionnal_reccurrence();		
			register_activation_hook( __FILE__, array( &$this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'deactivation' ) );

			add_action( 'checkFeedEvent', array( &$this, 'checkFeed') );

			$this->setupDataBase();
			$this->setupMenu();
			$this->postLoading();
			$this->post_meta_box = new MetaBox();
		}


		/**
		 * Setup Wordpress Database
		 */
		public function setupDataBase() {
			global $wpdb;
			
			$importTable = $wpdb->prefix.self::$TABLE_IMPORTATION_NAME;
			$structure1 = "CREATE TABLE $importTable(
				fileName VARCHAR(255) NOT NULL,
				uid VARCHAR(255),
				title VARCHAR(255),
				themes TEXT,
				attached_image_fileNames TEXT DEFAULT NULL,
				attached_text_fileName VARCHAR(255) DEFAULT NULL,
				importationDate DATETIME NOT NULL,
				error_type VARCHAR(30) DEFAULT 'no error',
				UNIQUE KEY fileName (fileName)
			);";
			$wpdb->query( $structure1 );
			
			
			$mappingTable = $wpdb->prefix.self::$TABLE_CATEGORY_MAPPING_NAME;
			$structure2 = "CREATE TABLE $mappingTable(
				code VARCHAR(255),
				wp_category_id BIGINT(20),
				
				PRIMARY KEY (code, wp_category_id)
			);";
			$wpdb->query( $structure2 );
		}


		/**
		 * Setup Wordpress Scheduler
		 */
		public function setupScheduler(){
			wp_schedule_event( time(), 'k-minutes', 'checkFeedEvent' );		
		}

		/**
		 * Admin Importation Logs Page
		 */
		public function importationLogsPage(){
				global $wpdb;
				include( 'atc-importationLog.php' );
		}

		/**
		 * Admin Tools Page
		 */
		public function toolsPage(){
				global $wpdb;
				include( 'atc-tools.php' );
		}

		/**
		 * Admin Settings Page
		 */
		public function settingsPage(){
				global $wpdb;
				include( 'atc-settings.php' );
		}

		/**
		 * Setup Wordpress Containers
		 */
		public function setupContainers(){
			add_menu_page( 'ATC_plugin', __('AllTheContent', 'AllTheContent'), __('AllTheContent', 'AllTheContent'), 'Atc_plugin', 'Importation_log', plugin_dir_url(__FILE__).'atc-logo20.png');
			add_submenu_page ( 'Atc_plugin' , 'allthecontent' , 'ImportationLog' , 10, 'ATCimportationlog',  array( &$this, 'importationLogsPage') );
			add_submenu_page ( 'Atc_plugin' , 'allthecontent' , 'Tools' , 10, 'ATCtools',  array( &$this, 'toolsPage' ) );
			add_submenu_page ( 'Atc_plugin' , 'allthecontent' , 'Settings' , 10, 'ATCsetting',  array( &$this, 'settingsPage' ) );
		}

		/**
		 * Set License Meta data for new imported content
		 */
		public function preparePostLicenseMeta(){
		
			$post_id =  $_GET['post'];
			$modif   = get_post_meta( $post_id, "_modification", true );
			$multi   = get_post_meta( $post_id, "_multimediatisation", true );
			$local   = get_post_meta( $post_id, "_localisation", true );
			$distrib = get_post_meta( $post_id, "_distribution", true );
			
			$license = array ( 
									"modification" => $modif,
									"multimediatisation" => $multi,
									"localisation" => $local,
									"distribution" => $distrib	
									);
						
			$this->post_meta_box->setContent( $license );
		}

		/**
		 * Post loading
		 */
		public function postLoading(){
			if ( is_admin() ){
				add_action( 'load-post.php', array( &$this, 'preparePostLicenseMeta' ) );
			}
		}
	
		/**
		 * Setup Wordpress Menu
		 */
		public function setupMenu(){
		
			add_action ( 'admin_menu', array( &$this, 'setupContainers' )  );
	
		}

		/**
		 * On activation
		 */
		public function activation(){

				$this->setupScheduler();
		}


		public function more_reccurrences( ) {

			$time = 60.0 * self::CRON_RECCURRENCE;
			return array(
			'k-minutes' => array(
			
										'interval' => $time,
										'display' => 'K Minutes' )
									);
		}


		/**
		 * Register cron reccurences
		 */
		public function register_additionnal_reccurrence() {
				add_filter('cron_schedules',  array( &$this, 'more_reccurrences' ) );
	 	} 

	 	/**
	 	 * Check for new content to imports
	 	 */
		public function checkFeed() {
			require_once("includes/taxonomy.php");
			
			$importationPath = get_option('atc_importPath');
			$importationAbsPath ="";

			if(!empty($importationPath)) {

				$importationAbsPath = ABSPATH. $importationPath;

				$files = scandir($importationAbsPath);

				$filesToParse = array();

				foreach($files as $f){
					if (preg_match( "#" . self::$FEED_EXTENSION . "$#", $f)) {
						$filesToParse[] = $f;
					}
				}

				$parser = new Parser($filesToParse , $importationAbsPath, plugin_dir_path( __FILE__ ) . self::$XSD_FILE_NAME );

				$xmlFiles = $parser->parsables();

				// here the fileName are exclusively those *_generic.xml
				foreach ($xmlFiles as $xmlFileName => $simpleXml) {
	
					// not well-formed
					if(empty( $simpleXml)) {

						$content = $this->prepareDataForInsertion($files, $xmlFileName);

						// insertion into table importationLog
						$this->insertionLogDataBase($content, $error="file corrupted");
	
						$deliveryId = preg_replace('#^([^_]+)_.+$#' ,'$1', $xmlFileName);
						
						// move all files attached to the XML feed
						foreach( $files as $attachedFile ){

							if (preg_match( "#^" . $deliveryId . "_.+$#",  $attachedFile)) {
							// move file into failure directory
								rename($importationAbsPath . "/" . $attachedFile, $importationAbsPath . "/failure/" . $attachedFile);
							}
						}
					} else {
						// well-formed
						$content = $parser->parsing($simpleXml, $xmlFileName);
						$this->contentInsertion($content, $importationAbsPath);
	
						// insertion into table importationLog	
						$this->insertionLogDataBase(  $content );

						}// end else well-formed

				}// end foreach

			} // end if

		}



		/**
		 * Colect content data when XML is corrupted
		 * WHEN XML FEED IS CORRUPTED !
		 * function which collect the data for data base insertion when xml file is corrupted
		 */
		public function prepareDataForInsertion( array $files, $currentFile ){

			$deliveryId = preg_replace( '#^(.+)_.+$#' ,'$1', $currentFile );
			
			// fetch data for data base insertion
			$imagesRegex = "#^$deliveryId.+\.(jpg|jpeg|png|raw|gif|bmp)$#";
			$images = array();
			foreach( $files as $file ){
				if( preg_match( $imagesRegex, $file ) ) {
					$images[$file] = array(
													'description'		=> '',
													'credits'			=> ''
												);
				}
			}
		
			$textRegex = "#^$deliveryId.+\.txt$#";
			$text = "";
			foreach( $files as $file ){
				if( preg_match( $textRegex, $file ) ) {
					$text = $file;
					break;
				}
			}

			$content = array(
				'fileName'			=> $currentFile, 
				'attachments'		=> $images, 
				'contentTextFileName'	=> $text
			);

			return $content;
		}




		/**
		 * Insert Content
		 *
		 * @param $content array
		 * @param $importPathAbs
		 */
		public function contentInsertion( array $content, $importPathAbs ){
			$autopublish = get_option( 'atc_autopublish' );
			if ( $autopublish == "true" ){
				$post_status = 'publish';
			}
			else{
				$post_status = 'pending';
			}
			
			global $wpdb;
			$categoryMapper = new CategoryMapping( $wpdb->prefix.self::$TABLE_CATEGORY_MAPPING_NAME );
			$postCategoriesId = $categoryMapper->postCategories( $content['themes'] );
			
			$post = array( 
				'post_title'	=> $content['title'],
				'post_content'	=> '', // is temporarily empty because generated by the next method (attachMediaAndGeneratePostContent)
				'tags_input'	=> $content['tags'] ,
				'post_excerpt'	=> $content['description'],
				'post_category'	=> $postCategoriesId,
				'post_status' 	=> $post_status
			);
								
			// when the publication's date should be the one from the feed
			if( self::$USE_PUBLICATION_DATE_IN_FEED ){
				$post['post_date'] =  $content['publicationDate'];	
			}

			$post_id = wp_insert_post( $post );

			// post's meta data
			add_post_meta( $post_id, 'content_uid', $content['uid'], true );
			add_post_meta( $post_id, 'content_description', $post['description'], true );
						
			// hidden meta data for license useful for the MetaBox class
			foreach( $content['license'] as $type => $value ){
				add_post_meta( $post_id, '_'.$type, $value, true );
			}


			// content with image html tag generation
			$fullcontent = $this->attachMediaAndGeneratePostContent( $post_id, $importPathAbs, $content );

			// add credits
			$fullcontent = $fullcontent  . "<p>" . $content['contentCredits'] . "</p>";
			
			wp_update_post( array( 'ID'=> $post_id, 'post_content' => $fullcontent ) ) ;
			
			// 1/1000 of seconds waiting for avoid that files are moved before the parsing has been completed
			usleep( 1000 );
			rename( $importPathAbs."/".$content['fileName'], $importPathAbs."/success/".$content['fileName']);

			// move the attached images file
			foreach( $content['attachments'] as $attachmentFileName => $metadata ){
						
				// the file exists
				if ( $metadata != null ){
				
					// move file into success directory
					rename( $importPathAbs."/".$attachmentFileName, $importPathAbs."/success/".$attachmentFileName );
				}
			}

			// move file the attached text content
			if( !empty( $content['contentTextFileName'] ) ){
				rename( $importPathAbs."/".$content['contentTextFileName'], $importPathAbs."/success/".$content['contentTextFileName'] );
			}

		}


		/**
		 * Set Extra Options
		 */
		public function setExtraOptions( $post_id, $type="" ) {
			global $wpdb;
			include(dirname( __FILE__ ). "/extraoptions.php");
		}



		// function used for the site's properties only
		public function importationProperties( $post_id ){
		
		
			// set custom extra options
			$this->setExtraOptions( $post_id, 'video' );

			// retrieves the category's of 'Video'
			$videoId = get_cat_ID( "Video" );
			
			if( $videoId == 0 ){
				$newCategory = array( 'cat_name' => "Video" ); 
				wp_insert_category( $newCategory );
				$videoId = get_cat_ID( "Video" );
			}
			
						
			$categories = get_the_category( $post_id);
			$catID = array() ;
			
			foreach( $categories as $cat ){
			
				$catID[] = $cat->cat_ID;
			}
			
			// ad the video categpry's id
			$catID[] = $videoId;
			
			// new category's insertion to the post
			wp_update_post( array(
											'ID' => $post_id,
											'post_category' => $catID
										)
								);
		
		
		
			// retrieve the url concerning the video and the poster's video and generated the tag for the stream video plugin
		
			$posterURL = "";
			$videoURL  = "";

			$args = array(
								'post_type'		=> 'attachment',
								'numberposts'	=> -1,
								'post_status'	=> null,
								'post_parent'	=> $post_id
							);

			$attachments = get_posts( $args );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
			 
					$attributes = wp_get_attachment_image_src( $attachment->ID );
					
						if( ! empty( $attributes) ){
			  
							$posterURL = $attributes[0];
			 			}
			 			
			 			else{
							$videoURL = wp_get_attachment_url( $attachment->ID );
						}
				}
			}
			
			$posterURL = preg_replace( "#(.+)-\d+x\d+(.+)#", '$1$2', $posterURL);

			
			$videoTag ="[stream flv=$videoURL img=$posterURL embed=false share=false width=400 height=260 dock=true controlbar=over bandwidth=high autostart=false /]";
			
			return $videoTag;


		}



		// return the generated content with html tag (<img>) and wordpress' [caption] tag
		public function attachMediaAndGeneratePostContent( $post_id, $importPathAbs, array $content ){
		
		
			$fullcontent = $content['text'] ;
			$counter = 0;
			$attachmentsNotTransfered = array();
			


			// media file
			foreach( $content['attachments'] as $filename => $metadata ){
				
			
				// media file not been transfered
				if( $metadata == null ) {
				
					$attachmentsNotTransfered[] = $filename;
									
				} else {
				
					$counter++;

					
					$media_url = $importPathAbs."/".$filename ;
					//$media_data = file_get_contents( $media_url );
					$upload_dir = wp_upload_dir();

					if( wp_mkdir_p( $upload_dir['path']) )
							$file = $upload_dir['path'] . '/' . strtolower( $filename );
					else
							$file = $upload_dir['basedir'] . '/' . strtolower ( $filename );

					//file_put_contents( $file, $media_data );
					copy( $media_url, $file);

					$wp_filetype = wp_check_filetype( $filename, null );
	
					$attachment = array(
						'post_mime_type'	=> $wp_filetype['type'],
						'post_title'		=> sanitize_file_name( $filename ),
						'post_excerpt'		=> "credits:".$metadata['credits'],
						'post_content'		=> $metadata['description'],
						'post_status'		=> 'inherit'
						);

					$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
				
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
					wp_update_attachment_metadata( $attach_id, $attach_data );




					$title = $attachment['post_description'];
					$alt = $attachment['post_title'];
					$url = wp_get_attachment_url( $attach_id );
					$caption = $attachment['post_excerpt'];
					
					if( $counter == 1 ){
						// the first one is set as featured image
						set_post_thumbnail( $post_id, $attach_id );
					} else {
						// in case of properties importation we need only to care about one single attached image
						if ( !self::$IMPORTATION_PROPERTIES ) {
							$fullcontent 	.= "[caption id='attachment_$attach_id' align='alignleft' width='150' caption='$caption' ]"
												. " <img size-thumbnail wp-image-$attach_id' title='$title' src='$url' alt='$alt' width='150' height='150' />"
												.	"[/caption]";
							}
						}

					} // end else that the media exists
					
				} // end for each regarding the attached media file
			
			
			// links
			if ( ! empty( $content['links'] ) )
				$fullcontent .= '</br><ul>';
			
			foreach( $content['links'] as $url => $descr ){
				$fullcontent .= '<li><a href="'.$url.'">'.$descr.'</a></li>';
			}
			$fullcontent .= "</ul>";
				
			
			if( self::$IMPORTATION_PROPERTIES  ){
				if( $content['type'] == 'video' ){
					$fullcontent = $this->importationProperties( $post_id  ) ."\n\n\n".$fullcontent;
				}
				
				else{
					$this->setExtraOptions($post_id);
				}
			}
		
		
			// error have to be logged
			// TO DO : CALL THE FUNCTION TO LOGG ERROR
			if( ! empty( $attachmentsNotTransfered) ){
			
			}
			
			return $fullcontent;

		}// end of attachMediaAndGeneratePostContent's function


	/**
	 * Insert a new log
	 */
	public function insertionLogDataBase( array $content, $error="" ){

		global $wpdb;
		$table =  $wpdb->prefix.self::$TABLE_IMPORTATION_NAME;

		$uid = addslashes( $content['uid'] );
		$fileName = addslashes( $content['fileName'] );
		$title = addslashes( $content['title'] );
	
		$attached_image_fileNames ="";			
		foreach( $content['attachments'] as $ImageName => $metadata ) { 
		
			$attached_image_fileNames .= addslashes( $ImageName ).",";
		}
		$attached_image_fileNames = substr( $attached_image_fileNames, 0, strlen( $attached_image_fileNames) -1 ) ;
		$attached_text_fileName = addslashes( $content['contentTextFileName'] ); 


		// importation's error
		if( $error != "" ){

			$wpdb->query( "INSERT INTO $table( fileName, uid, title, attached_image_fileNames, attached_text_fileName, importationDate, error_type )
					VALUES( '$fileName', '$uid', '$title', '$attached_image_fileNames', '$attached_text_fileName',  NOW(), '$error' )
			" );
		}

		// no importation's error
		else{

			$wpdb->query( "INSERT INTO $table( fileName, uid, title, attached_image_fileNames, attached_text_fileName, importationDate )
				VALUES( '$fileName', '$uid',  '$title', '$attached_image_fileNames', '$attached_text_fileName', NOW()  )
			" );
		}
	}

	/*
	 * remove directory recursively
	 */
	public function rrmdir( $dir ){

		if( is_dir( $dir ) ){

			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( filetype($dir."/".$object) == "dir" ) 
						$this->rrmdir( $dir."/".$object );
					}
				else
					unlink( $dir."/".$object );
				}// end foreach

			reset($objects);
			rmdir($dir);
			}
	}

	/**
	 * Deactivation
	 */
	public function deactivation(){
	
		global $wpdb;
		$table1 = $wpdb->prefix.self::$TABLE_IMPORTATION_NAME;
		$query1 = "DROP TABLE $table1";
		
		$table2 = $wpdb->prefix.self::$TABLE_CATEGORY_MAPPING_NAME;
		$query2 = "DROP TABLE $table2";

//		$wpdb->query( $query1 );
//		$wpdb->query( $query2 );

		$timestamp = wp_next_scheduled( 'checkFeedEvent' );
		wp_unschedule_event( $timestamp, 'k-minutes', 'checkFeedEvent' );
		remove_action( 'checkFeedEvent', array( &$this, 'checkFeed'));

		// remove the created directories
//			$importPath = get_option('atc_importPath');
//			$this->rrmdir(  ABSPATH.$importPath  );

		// remove all options
		call_user_func_array( array( &$this, 'deleteOptions'),  self::$OPTIONS );
	}

	/**
	 * remove check for new contents to import
	 */
	public function checkFeedEvent_deactivate() {
		wp_clear_scheduled_hook('checkFeedEvent');
	}

	/**
	 * Delete all options
	 */
	public function deleteOptions(){
		$args = func_get_args();
		$num = count($args);

		if ($num == 1)
			return (delete_option($args[0]) ? TRUE : FALSE);
		elseif (count($args) > 1) {
			foreach ($args as $option) {
				if (! delete_option($option))
					return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}
}

} // end if


// as soon Atc class has been loaded
if(class_exists('Atc')) {
	$wp_bot = new Atc( 'atc-delivery-generic-content-v1.0.xsd' );
}

?>
