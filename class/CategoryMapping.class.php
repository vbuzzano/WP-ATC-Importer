<?php


class CategoryMapping{

	private $table;


	public function __construct(  $table )	{
		
		$this->table = $table;
	}
	

	
	
	public function codeExists( $code ){
	
	
		global $wpdb;
		$row = $wpdb->get_row( "SELECT * FROM $this->table WHERE code='$code'");

		return $row->wp_category_id;

	}
	

	
	public function nameExists( $name ){
	
		$categories = get_categories( 'hide_empty=0' );
		$exists = NULL;
		
		foreach( $categories as $cat ){
		
				// not case sensitive		
			if( strtolower( $cat->name ) == strtolower( $name )  ){	
				$exists = $cat->cat_ID;
				break;
			}
		
		}
	
		return $exists;
	}
	


	
	public function algorithm( $code, $name ){
	
	$mappedId = $this->codeExists( $code );
	if(   $mappedId != NULL ){
		
		// return the wordpress categoryID
		return $mappedId;
	}
	
	else{
	
			$catId = $this->nameExists( $name );
			if(  $catId != NULL ){
				return $catId;
			}
		
			else{
				
				return wp_create_category( $name );
			}
	
		}
	}




	public function postCategories( array $themes ){
	
		$postCategoriesId = array();
		
		foreach( $themes as $code => $name ){
			
			$postCategoriesId[] = $this->algorithm( $code, $name );
		
		}
		
		return $postCategoriesId;
	}


		

}// end class

?>
