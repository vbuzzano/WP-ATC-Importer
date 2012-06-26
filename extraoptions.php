<?
/*
 * Extra Options for theme WPZoom Telegraph
 *
 */

$table = $wpdb->prefix."postmeta";

if( $type == 'video' ){
    $videoAndSideBarAndSocialShare =
            "INSERT INTO $table( post_id, meta_key, meta_value )
                VALUES
                            (  '$post_id',  'wpzoom_post_type', 'Video'  ),
                            (  '$post_id',  'wpzoom_post_template', 'Sidebar on the left'  ),
                            (  '$post_id',  'wpzoom_post_social', 'Yes'  );
                    ";
                    
    $wpdb->query( $videoAndSideBarAndSocialShare );

}

else{
    $sideBarAndSocialShare =
                    "INSERT INTO $table( post_id, meta_key, meta_value )
                    VALUES
                            (  '$post_id',  'wpzoom_post_template', 'Sidebar on the left'  ),
                            (  '$post_id',  'wpzoom_post_social', 'Yes'  );
                ";
    $wpdb->query( $sideBarAndSocialShare );
}

?>