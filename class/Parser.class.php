<?php


class Parser{


	private $files;
	private $importationPath;
	private $xsdPath;	



	public function __construct( array $xmlFilesName, $importationPath, $xsdPath ){

		$this->files = $xmlFilesName;
		$this->importationPath = $importationPath;
		$this->xsdPath = $xsdPath;
	}




	// return a hash : xmlFileName => simpleXmlElementObject 
	public function parsables(){

		$filesWellFormed = array();
		
		foreach( $this->files as $xmlFileName ){


				try{
					$simpleXml = @new SimpleXMLElement( $this->importationPath."/".$xmlFileName , NULL, TRUE );
					$filesWellFormed["$xmlFileName"] = $simpleXml;
				}

				// the xml file is not well-formed
				catch( Exception $e ) {
					$filesWellFormed["$xmlFileName"] = "";

				}

		}// end foreach

		return $filesWellFormed;

	} // end parsables





	public function parsing( SimpleXMLElement $xml, $xmlFileName ){
		
		$uid									= $xml->content['uid'];
		$contentTitle						= $xml->content->title;
		$description						= $xml->content->description;
		$contentCredits						= $xml->content->credits;
		$contentCredits = str_replace("/ATCNA", "<a href=\"http://www.actna.net\" target=\"_blank\">/ATCNA</a>", $contentCredits);
		$attachments						= array();


		// attachments : hash of hash ( filename => array(  credits => cr, destription => descr )   ) 
		foreach( $xml->content->attachment as $attachment ){
		
					$mediaName = $attachment['filename'];
					$mediaExists = true;

					if (! file_exists( $this->importationPath .'/'.$mediaName ) ) {
						$mediaName = $attachment['path'];
						if (! file_exists( $mediaName ) ){
							$mediaName = $attachment['path'] . '/'.$mediaName;
							if (! file_exists( $mediaName ) ) {
									$mediaExists = false;
							}
						}
					}

					$mediaInfos = null;
					// it means that the file has been transfered
					if( $mediaExists ){
						$mediaInfos = array(
												'description'		=> (string) $attachment->description,
												'credits'			=> (string) $attachment->credits
												);
					}
					
					$attachments["$mediaName"] = $mediaInfos;

		}// end attachments


		// keyword and tags for post's tags
		$keywords = array();
		foreach( $xml->content->keywords->value as $keyword ){
				$keywords[] = (string) $keyword;
		}

		foreach( $xml->content->tag as $tag ) {
																		// ! NOT BEING IN THE ONE TO STRIP
			if ( !in_array( $tag->value, $keywords ) && !in_array( $tag['code'] , Atc::$TAGS_TO_STRIP)  ){
				$keywords[] = (string) $tag->value;
			}
		}


		// themes for post's category ( assoc table, key = code, value = themevalue ) 
		$themes = array();
		foreach( $xml->content->theme as $theme ){
				$code = 	$theme['code'];
				$themes["$code"] = (string) $theme;
		}


		// licence for hidden post's meta
		$licenses = array();
		foreach( $xml->content->license as $license ){
					$children = $license->children();

					foreach( $children as $child){
						$childName = $child->getName();
						$allowed = (string) $child['allowed'];

						if( $allowed == "false" )
							$licenses["$childName"] ='not allowed';
						
						else
							$licenses["$childName"] ='allowed';
					}
		}


		// THIS MUST BE IMPROVED .....· !!!!!!!!!!
		// text's content in XML feed not embedded
		if ( !empty( $xml->content->item['filename'] ) ){

			$contentTextFileName = $xml->content->item['filename'];
			$contentText = file_get_contents( $contentTextFileName  );
		}

		// text's content in XML feed embedded 		
		else{
			$contentText = $xml->content->item;
		}
		
		
		// publication's date
		$publicationDate = $xml->content->pubdate;
		
		
		// links 
		$links = array();
		foreach( $xml->content->link as $link ){
				$url = $link['url'];
				$links["$url"] = (string) $link;
		}
		
		$content = array( 
										'type'								=> $xml->content->contenttype['code'],
										'fileName'							=> $xmlFileName,
										'uid'									=> (string) $uid,
										'title'								=> (string) $contentTitle,
										'description'						=> (string) $description,
										'contentCredits'					=> (string) $contentCredits,
										'attachments'						=> $attachments,
										'themes'								=> $themes,
										'tags'								=> $keywords,
										'license'							=> $licenses,
										'text'								=> (string) $contentText,
										'contentTextFileName'			=> (string) $contentTextFileName,
										'publicationDate'					=> (string) $publicationDate,
										'links'								=> $links
										);

		return $content;

	}




	public function valid( $fileName, $xmlDom, $xsdPath ){

		

		if( ! $xmlDom->schemaValidate( $xsdPath ) ){

				$this->validationsError[] = $fileName;
				 
			}

	}






}// end class

?>
