<?php


class MetaBox{

	const LANG = 'atc_trdom';
	private $content = null;

	public function __construct()	{
		
	  add_action( 'add_meta_boxes', array( &$this, 'add_some_meta_box' ) );
	}
	
	
	public function setContent( array $content = null ){
	
		$this->content = $content;
	}

	/**
	* Adds the meta box container
	*/
	public function add_some_meta_box()	{
	  add_meta_box( 
		    'Licence'
		   ,__( 'Content license', self::LANG )
		   ,array( &$this, 'render_meta_box_content' )
		   ,'post' 
		   ,'advanced'
		   ,'high'
	  );
	}


	/**
	* Render Meta Box content
	*/
	            
	public function render_meta_box_content() {
	
	if( $this->content != null ){
		echo "<table>";
		foreach ( $this->content as $type => $value ){
			echo "<tr><td>".$type."</td><td>&nbsp;&nbsp;".$value."</td><tr/>";
			}
		echo "</table>";
		
		}
	}
		

}// end class

?>
