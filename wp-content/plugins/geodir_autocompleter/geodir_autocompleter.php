<?php
/*
Plugin Name: GeoDirectory Autocompleter
Plugin URI: http://wpgeodirectory.com
Description: GeoDirectory Autocompleter Plugin.
Version: 1.0.5
Author: GeoDirectory
Author URI: http://wpgeodirectory.com
*/


global $wpdb,$plugin_prefix,$geodir_addon_list;
if(is_admin()){
	require_once('gd_update.php'); // require update script
}
///GEODIRECTORY CORE ALIVE CHECK START
if(is_admin()){
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('geodirectory/geodirectory.php')){
return;
}}/// GEODIRECTORY CORE ALIVE CHECK END

$geodir_addon_list['geodir_autocompleter_manager'] = 'yes' ;


function geodir_autocompleter_is_retired()
{

	echo '<div class="error">';
	echo "<img class='gd-icon-noti' src='".plugin_dir_url('')."geodirectory/geodirectory-assets/images/favicon.ico' > ";
	echo "<p>GeoDirectory Autocompleter Plugin has now been merged into Advanced Search, please delete the Autocompleter plugin to avoid problems</p>";
	echo "</div>";
	?>
	 <style>
	.gd-icon-noti{
		float: left;
		margin-top: 10px;
		margin-right: 5px;
	}

	
	</style>
    <?php 
	
}
add_action('admin_notices', 'geodir_autocompleter_is_retired');