<?php
class XooUserWall {

	public $allowed_extensions;

	function __construct() 
	{
				
		$this->ini_wall();
		
		add_action( 'wp_ajax_wall_post_message',  array( &$this, 'wall_post_message' ));
		add_action( 'wp_ajax_wall_post_reply',  array( &$this, 'wall_post_reply' ));
		add_action( 'wp_ajax_wall_reload_whole_messages',  array( &$this, 'uultra_get_latest_conversations' ));
		add_action( 'wp_ajax_nopriv_wall_reload_whole_messages',  array( &$this, 'uultra_get_latest_conversations' ));
		
		add_action( 'wp_ajax_reload_whole_replies',  array( &$this, 'reload_whole_replies' ));
		add_action( 'wp_ajax_nopriv_reload_whole_replies',  array( &$this, 'reload_whole_replies' ));
		
		add_action( 'wp_ajax_wall_delete_reply',  array( &$this, 'wall_delete_reply' ));
		add_action( 'wp_ajax_wall_delete_inline_comment',  array( &$this, 'wall_delete_inline_comment' ));		
		add_action( 'wp_ajax_wall_edit_reply',  array( &$this, 'wall_edit_reply' ));
		add_action( 'wp_ajax_wall_edit_reply_confirm',  array( &$this, 'wall_edit_reply_confirm' ));		
		add_action( 'wp_ajax_wall_edit_comment_confirm',  array( &$this, 'wall_edit_comment_confirm' ));		
		add_action( 'wp_ajax_wall_edit_update_form',  array( &$this, 'wall_edit_update_form' ));
		
		
		
		
		add_action( 'wp_ajax_reload_site_wide_wall',  array( &$this, 'uultra_reload_site_wide_wall' ));
		add_action( 'wp_ajax_nopriv_reload_site_wide_wall',  array( &$this, 'uultra_reload_site_wide_wall' ));
		add_action( 'wp_ajax_upload_uultra_site_wide_photo',  array( &$this, 'upload_uultra_site_wide_photo' ));
		
		add_action( 'wp_ajax_uultra_refresh_wall_share_image',  array( &$this, 'uultra_refresh_wall_share_image' ));
		
		

	}
	
	public function ini_wall()
	{
		global $wpdb;
		
		//-------- modules: (update, newuser, topic, reply, posts, photo, media-photo,  media-video, gallery)
		//media-* module is used for files uploaded directly from the wall.
		
		//-------- visible to: 0 or blank -->> Public,  1 -->> Friends Only,  2 -->> Friends and Followers , 3 -->> All Members (logged in)

			// Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_wall (
				`comment_id` bigint(20) NOT NULL auto_increment,		
				`comment_module` varchar(100) NOT NULL,	
				`comment_module_item_id` bigint(20) NOT NULL ,
				`comment_visible_to` int(2) NOT NULL ,
				`comment_title` varchar(200) NOT NULL,		
				`comment_wall_user_id` bigint(20) NOT NULL ,
				`comment_posted_by_id` bigint(20) NOT NULL ,
				`comment_direct_source_path` varchar(200) NOT NULL,				
				`comment_message` text NOT NULL,				
				`comment_date` datetime NOT NULL,				
				PRIMARY KEY (`comment_id`)
			) COLLATE utf8_general_ci;';

		   $wpdb->query( $query );
		   
		   // Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_wall_replies (
				`reply_id` bigint(20) NOT NULL auto_increment,
				`reply_comment_id` bigint(20) NOT NULL ,
				`reply_commented_by_id` bigint(20) NOT NULL ,				
				`reply_message` text NOT NULL,				
				`reply_date` datetime NOT NULL,				
				PRIMARY KEY (`reply_id`)
			) COLLATE utf8_general_ci;';

		   $wpdb->query( $query );
		
		   $this->update_table();
		
	}
	
	function update_table()
	{
		global $wpdb;
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_wall where field="comment_module" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_wall add column comment_module varchar (100) NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_wall where field="comment_title" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_wall add column comment_title varchar (200) NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_wall where field="comment_direct_source_path" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_wall add column comment_direct_source_path varchar (200) NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		
		
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_wall where field="comment_module_item_id" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_wall add column comment_module_item_id bigint (20) NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_wall where field="comment_visible_to" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_wall add column comment_visible_to int (2) NOT NULL ; ';
			$wpdb->query($sql);
		}
		
		
		
	
	}
	
	
	 //this add the event to the wall table. Added on 11-13-2014	 
	public function wall_save_activity($item_id, $module_id)
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		require_once(ABSPATH . 'wp-includes/post.php');
		
		
		$logged_user_id = get_current_user_id();
		$receiver_id =  $logged_user_id;
		
		
		if(!$this->wall_check_enabled($module_id)){return;}		
		
		//is this a post?		
		if($module_id=='post')
		{
			//get post with ID
			$post = get_post($item_id); 			 
			$author_id = $post->post_author;
			$logged_user_id = $author_id;
			$receiver_id =  $author_id;
			
			//check if already added to the wall.			
			if($this->get_one_post_from_wall($item_id)){return $item_id;}						
		
		
		}elseif($module_id=='newuser'){
			
			$user_id = $item_id;	
			$logged_user_id = 		$user_id;
			$receiver = get_user_by('id',$user_id);	
		
		}elseif($module_id=='photo'){
			
			$user_id = $logged_user_id;	
			$logged_user_id = 		$user_id;			
			$receiver = get_user_by('id',$user_id);	
		
		}
		
		//get receiver		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		
		//store in the db		
		if($receiver_id >=0)
		{
			
			$new_message = array(
						'comment_id'        => NULL,
						'comment_module' => $module_id,
						'comment_module_item_id' => $item_id, 
						'comment_wall_user_id' => $receiver_id,
						'comment_posted_by_id'   => $logged_user_id,						
						'comment_message'   => $uu_message,					
						'comment_date'=> date('Y-m-d H:i:s')
						
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_wall', $new_message, array( '%d', '%s', '%s', '%s', '%s', '%s',  '%s' ));
					
			
			//$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		
	}
	
	public function wall_check_enabled($module_id)
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		require_once(ABSPATH . 'wp-includes/post.php');
		
		
		$logged_user_id = get_current_user_id();
		$receiver_id =  $logged_user_id;		
		
		//is this a post?		
		if($module_id=='post')
		{
			if($xoouserultra->get_option('uultra_user_wall_enable_new_post') =='no'){return false;}			
		
		}elseif($module_id=='newuser'){
			
			if($xoouserultra->get_option('uultra_wal_new_user_notification') =='no'){return false;}			
		
		}elseif($module_id=='photo'){
			
			if($xoouserultra->get_option('uultra_user_wall_enable_photo') =='no'){return false;}
			
		}elseif($module_id=='media-photo'){
			
			if($xoouserultra->get_option('uultra_user_wall_enable_photo_sharing') =='no'){return false;}		
			
		
		}	
		
		return true;
		
	}
	
	public function wall_post_message()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$receiver_id =  sanitize_text_field($_POST["wall_profile_id"]);
		$uu_message =   sanitize_text_field($_POST["wall_message"]);		
		$wall_source =   $_POST["wall_image"];
		
		//get receiver		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		if($wall_source!="")
		{
			$module = 'media-photo';
		
		}else{
			
			$module = 'update';			
		}		
		
		//store in the db		
		if($receiver->ID >0)
		{
			
			$new_message = array(
						'comment_id'        => NULL,
						'comment_module' => $module,
						'comment_direct_source_path' => $wall_source,					
						'comment_wall_user_id' => $receiver_id,
						'comment_posted_by_id'   => $logged_user_id,						
						'comment_message'   => $uu_message,					
						'comment_date'=> date('Y-m-d H:i:s')
						
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_wall', $new_message, array( '%d', '%s', '%s', '%s', '%s', '%s',  '%s' ));
					
			
			//$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		die();
		
		
		
	}
	
	function uultra_refresh_wall_share_image()
	{
		global $xoouserultra;
		global $wpdb;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";		
		$image_name = $_POST["image"];
		
		$upload_folder = $xoouserultra->get_option('media_uploading_folder');		
		$wall_pics_path = 'site-wide-wall';		
		$thumb = $site_url.$upload_folder."/".$wall_pics_path."/".$image_name;
		
		$html = '<img src="'.$thumb.'">';
		
		echo $html;
		die();
	
	}
	
	// File upload handler for the site-wide photo uploader:
	function upload_uultra_site_wide_photo()
	{
		global $xoouserultra;
		global $wpdb;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		$site_url = site_url()."/";
		
		// Check referer, die if no ajax:
		check_ajax_referer('photo-upload');
		
		/// Upload file using Wordpress functions:
		$file = $_FILES['async-upload'];
		
		
		$original_max_width = $xoouserultra->get_option('wall_image_share_width'); 
        $original_max_height =$xoouserultra->get_option('wall_image_share_height'); 
		
		if($original_max_width=="" || $original_max_height==80)
		{			
			$original_max_width = 600;			
			$original_max_height = 600;
			
		}
		
			
		$o_id = get_current_user_id();		
				
		$info = pathinfo($file['name']);
		$real_name = $file['name'];
        $ext = $info['extension'];
		$ext=strtolower($ext);
		
		$rand = $this->genRandomString();
		
		$rand_name = "wall_share_".$rand."_".session_id()."_".time(); 
		
		$path_pics = ABSPATH.$xoouserultra->get_option('media_uploading_folder');
		
		$wall_pics_path = 'site-wide-wall';
			
			
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') 
		{
			if($o_id != '')
			{
				
				   if(!is_dir($path_pics."/".$wall_pics_path."")) {
						$xoouserultra->photogallery->CreateDir($path_pics."/".$wall_pics_path);								   
					}					
										
					$pathBig = $path_pics."/".$wall_pics_path."/".$rand_name.".".$ext;						
					
					
					if (copy($file['tmp_name'], $pathBig)) 
					{
						//check auto-rotation						
						if($xoouserultra->get_option('uultra_rotation_fixer')=='yes')
						{
							$xoouserultra->photogallery->orient_image($pathBig);
						
						}
						
						$upload_folder = $xoouserultra->get_option('media_uploading_folder');				
						$path = $site_url.$upload_folder."/".$wall_pics_path."/";
						
						//check max width
												
						list( $source_width, $source_height, $source_type ) = getimagesize($pathBig);
						
						if($source_width > $original_max_width) 
						{
							//resize
							if ($xoouserultra->photogallery->createthumb($pathBig, $pathBig, $original_max_width, $original_max_height,$ext)) 
							{
								$old = umask(0);
								chmod($pathBig, 0755);
								umask($old);
														
							}
						
						
						}
						
						
						$new_avatar = $rand_name.".".$ext;						
						$new_avatar_url = $path.$rand_name.".".$ext;					
						
											
						
					}
									
					
			     }  		
			
			  
			
        } // image type
		
		// Create response array:
		$uploadResponse = array('image' => $new_avatar);
		
		// Return response and exit:
		echo json_encode($uploadResponse);
		
		die();
		
	}
	
	public function genRandomString() 
	{
		$length = 5;
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWZYZ";
		
		$real_string_legnth = strlen($characters) ;
		//$real_string_legnth = $real_string_legnth– 1;
		$string="ID";
		
		for ($p = 0; $p < $length; $p++)
		{
			$string .= $characters[mt_rand(0, $real_string_legnth-1)];
		}
		
		return strtolower($string);
	}
		
	public function wall_photo_uploader() 
	{
		
		$plupload_init = array(
				'runtimes'            => 'html5,silverlight,flash,html4',
				'browse_button'       => 'uultra-upload-photo-site-wide',
				'container'           => 'plupload-upload-ui-sitewidewall',
				'drop_element'        => 'drag-drop-area-sitewidewall',
				'file_data_name'      => 'async-upload',
				'multiple_queues'     => true,
				'multi_selection'	  => false,
				'max_file_size'       => wp_max_upload_size().'b',
				//'max_file_size'       => get_option('drag-drop-filesize').'b',
				'url'                 => admin_url('admin-ajax.php'),
				'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
				//'filters'             => array(array('title' => __('Allowed Files', $this->text_domain), 'extensions' => "jpg,png,gif,bmp,mp4,avi")),
				'filters'             => array(array('title' => __('Allowed Files', "xoousers"), 'extensions' => "jpg,png,gif,jpeg")),
				'multipart'           => true,
				'urlstream_upload'    => true,

				// Additional parameters:
				'multipart_params'    => array(
					'_ajax_nonce' => wp_create_nonce('photo-upload'),
					'action'      => 'upload_uultra_site_wide_photo' // The AJAX action name
					
				),
			);
			
			//print_r($plupload_init);

			// Apply filters to initiate plupload:
			$plupload_init = apply_filters('plupload_init', $plupload_init);
		
		   // Uploading functionality trigger:
		  // (Most of the code comes from media.php and handlers.js)
		   $template_dir = get_template_directory_uri();
		   
		   $html = '';
		   
		   if(!is_user_logged_in())
		   {
			   $html .="<p>".__("You have to be logged in to upload photos ",'xoousers')."</p>";
			
		   }else{

		
		   $html = '<div id="uploadContainer" style="margin-top: 10px;">
			
			
			<!-- Uploader section -->
			<div id="uploaderSection" style="position: relative;">
				<div id="plupload-upload-ui-sitewidewall" class="hide-if-no-js">
				<input type="hidden" id="uultra-site-wide-wall-image-share">
				
				
				
                
					<div id="drag-drop-area-sitewidewall">
						                        
                        <div id="progressbar-sitewidewall"></div>                 
                         <div id="symposium_filelist_sitewidewall" class="cb"></div>
					</div>
					
					<div id="uultra-img-to-share-id-refresh" class="uultra-img-to-share-id-refresh-cl">
				
					</div>
				
				</div>
                
                 
			
			</div>
            
           
		</div>';
			 
			 
			$js_messages_one_file = __("'You may only upload one image at a time!'", 'xoousers');
			$js_messages_file_size_limit = __("'The file you selected exceeds the maximum filesize limit.'", 'xoousers');
			
			

			$html .= '<script type="text/javascript">';
			
			$html .= "jQuery(document).ready(function($){
					
					// Create uploader and pass configuration:
					var uploader_sitewidewall = new plupload.Uploader(".json_encode($plupload_init).");

					// Check for drag'n'drop functionality:
					uploader_sitewidewall.bind('Init', function(up){
						
					var uploaddiv_sitewidewall = $('#plupload-upload-ui-sitewidewall');
						
						// Add classes and bind actions:
						if(up.features.dragdrop){
							uploaddiv_sitewidewall.addClass('drag-drop');
							
							$('#drag-drop-area-sitewidewall')
								.bind('dragover.wp-uploader', function(){ uploaddiv_sitewidewall.addClass('drag-over'); })
								.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv_sitewidewall.removeClass('drag-over'); });

						} else{
							uploaddiv_sitewidewall.removeClass('drag-drop');
							$('#drag-drop-area').unbind('.wp-uploader');
						}

					});

					
					// Init ////////////////////////////////////////////////////
					uploader_sitewidewall.init(); 
					
					// Selected Files //////////////////////////////////////////
					uploader_sitewidewall.bind('FilesAdded', function(up, files) {
						
						
						var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
						
						// Limit to one limit:
						if (files.length > 1){
							alert($js_messages_one_file);
							
							

							return false;
						}
						
						// Remove extra files:
						if (up.files.length > 1){
							up.removeFile(uploader_sitewidewall.files[0]);
							
							$.each(uploader_sitewidewall.files, function (i, file) {
								
														
									//up.removeFile(file);
							
								
							});
							
							up.refresh();
						}
						
						// Loop through files:
						plupload.each(files, function(file){
							
							// Handle maximum size limit:
							if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
								alert($js_messages_file_size_limit);
								return false;
							}
						
						});
						
						jQuery.each(files, function(i, file) {
							
							//fix this
							
							//jQuery('#symposium_filelist_sitewidewall').append('<div class='addedFile' id=' + file.id + '>' + file.name + '</div>');
						});
						
						up.refresh(); 
						uploader_sitewidewall.start();
						//alert('start here');
						$( '#uultra-wall-photo-uploader-box' ).slideDown();
						$( '#progressbar-sitewidewall' ).slideDown();
						
						
					});
					
					// A new file was uploaded:
					uploader_sitewidewall.bind('FileUploaded', function(up, file, response){
						
						var obj = jQuery.parseJSON(response.response);												
						var img_name = obj.image;
						
						
						$('#uultra-site-wide-wall-image-share').val(img_name);
						
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {'action': 'uultra_refresh_wall_share_image', 'image': img_name},
							
							success: function(data){
								
														
								$('#uultra-img-to-share-id-refresh').html(data);
								$('#uultra-img-to-share-id-refresh').slideDown();
								$( '#progressbar-sitewidewall' ).slideUp();
								
								
								
								}
						});
						
						
					
					});
					
					// Error Alert /////////////////////////////////////////////
					uploader_sitewidewall.bind('Error', function(up, err) {
						alert('Error: ' + err.code + ', Message: ' + err.message + (err.file ? ', File: ' + err.file.name : '') );
						up.refresh(); 
					});
					
					// Progress bar ////////////////////////////////////////////
					uploader_sitewidewall.bind('UploadProgress', function(up, file) {
						
						var progressBarValue = up.total.percent;
						
						jQuery('#progressbar-sitewidewall').fadeIn().progressbar({
							value: progressBarValue
						});
						
						//fix this
						
						//jQuery('#progressbar-sitewidewall').html('<span class='progressTooltip'>' + up.total.percent + '%</span>');
					});
					
					// Close window after upload ///////////////////////////////
					uploader_sitewidewall.bind('UploadComplete', function() {
						
						//jQuery('.uploader').fadeOut('slow');						
						jQuery('#progressbar-sitewidewall').fadeIn().progressbar({
							value: 0
						});
						
						
					});
					
					
					
				}); ";
				
					
			$html .= '</script>';
			
			
			}
			
		
		
		return $html;
	
	
	}
	
	public function wall_post_reply()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		$wall_comment_id =  sanitize_text_field($_POST["wall_comment_id"]);
		$wall_reply_message =   sanitize_text_field($_POST["wall_reply_message"]);
		
		//get receiver		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		//print_r($receiver );
		
		//store in the db		
		if($wall_comment_id >0)
		{
			
			$new_message = array(
						'reply_id'        => NULL,
						'reply_comment_id' => $wall_comment_id,
						'reply_commented_by_id'   => $logged_user_id,						
						'reply_message'   => $wall_reply_message,					
						'reply_date'=> date('Y-m-d H:i:s')
						
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'usersultra_wall_replies', $new_message, array( '%d', '%s', '%s', '%s',  '%s' ));
					
			
			//$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		die();
		
		
		
	}
	
	function message_authorization($wall_reply_id, $wall_comment_id)
	{
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();
		
		
	
	}
	
	//get one message posted on my wall	
	public function get_one_message($message_id) 
	{
		global $wpdb, $xoouserultra;
		
		$logged_user_id = get_current_user_id();
		

		$messages = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'usersultra_wall WHERE `comment_id` = ' . $message_id . ' AND  `comment_wall_user_id` = ' . $logged_user_id . ' ' );
		

		foreach ( $messages as $message )
		{
			return $message;
							
		}
		
	
	}
	
	//get one post from wall table
	public function get_one_post_from_wall($post_id)
	{
		global $wpdb, $xoouserultra;
		
		$logged_user_id = get_current_user_id();		

		$messages = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'usersultra_wall WHERE `comment_module_item_id` = ' . $post_id . ' AND  `comment_module` = "post" ' );
		

		foreach ( $messages as $message )
		{
			return true;
							
		}
		
		return false;
		
	
	}
	
	public function wall_delete_inline_comment()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_comment_id =   sanitize_text_field($_POST["wall_comment_id"]);
		
		if($wall_comment_id >0 && $logged_user_id >0)
		{			
			$query = "DELETE FROM " . $wpdb->prefix ."usersultra_wall WHERE (comment_id = '$wall_comment_id' AND comment_wall_user_id = '".$logged_user_id."') OR  (comment_id = '$wall_comment_id' AND comment_posted_by_id = '".$logged_user_id."')";						
		    $wpdb->query( $query );					
			
			//delete all replies of this message			
			$query = "DELETE FROM " . $wpdb->prefix ."wp_usersultra_wall_replies WHERE  reply_comment_id = '".$wall_comment_id."' ";						
		    $wpdb->query( $query );				
		
		}
		
		die();
		
		
		
	}
	
	public function wall_delete_reply()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_reply_id =   sanitize_text_field($_POST["wall_reply_id"]);
		$wall_comment_id =   sanitize_text_field($_POST["wall_comment_id"]);
		
		
		//store in the db		
		if($wall_reply_id >0 && $logged_user_id >0)
		{			
			
			$query = "DELETE FROM " . $wpdb->prefix ."usersultra_wall_replies WHERE reply_id = '$wall_reply_id'  ";						
		    $wpdb->query( $query );			
		
		}
		
		die();
		
		
		
	}
	
	public function wall_edit_reply()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_reply_id =   sanitize_text_field($_POST["wall_reply_id"]);
		$wall_comment_id =   sanitize_text_field($_POST["wall_comment_id"]);
		
		$html = '';
		
		
		//store in the db		
		if($wall_reply_id >0 && $logged_user_id >0)
		{			
			
			$query = "SELECT * FROM " . $wpdb->prefix ."usersultra_wall_replies WHERE reply_id = '$wall_reply_id'  ";		
				
			$replies = $wpdb->get_results( $query);
			
			if ( !empty( $replies ) )
			{
		
		
				foreach ( $replies as $reply )
				{
					
					$html .= '<div class="uultra-b-edit-reply">';
					$html .= '<p>'.__('Edit reply:', 'xoousers').'</p>';					
				
				
$html .='<textarea style="height: 40px; overflow: hidden; word-wrap: break-word; resize: none; width:95% !important; clear:both;" class="uultra-commentTextArea" id="uultra-edit-reply-text-box-'.$wall_reply_id.'">'.$reply->reply_message.'</textarea>';

					$html .='<input value="Update" id="uultra-wall-edit-reply-confirm" class="uultra-button-edit-reply-wall-edition-confirm" type="button" data-reply-id="'.$wall_reply_id.'"> <span><a href="#" class="uultra-close-edit-reply-box" title="'.__('Close', 'xoousers').'" data-reply-id="'.$wall_reply_id.'">'.__('close', 'xoousers').'</a></span> <span class="uultra-error-message-reply-empty" id="uultra-empty-message-reply-id-'.$wall_reply_id.'">'.__(' Please, input some text', 'xoousers').' </span>';
					
					$html .= '</div>';
									
				}				
			
			}
		
		}
		
		echo $html;
		
		die();
		
		
		
	}
	
	//This returns the form to edit the "update" inline
	public function wall_edit_update_form()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_comment_id =   sanitize_text_field($_POST["wall_comment_id"]);
		
		$html = '';
		
		
		//store in the db		
		if($wall_comment_id >0 && $logged_user_id >0)
		{			
			
			$query = "SELECT * FROM " . $wpdb->prefix ."usersultra_wall WHERE (comment_id = '$wall_comment_id' AND comment_wall_user_id = '".$logged_user_id."') OR (comment_id = '$wall_comment_id' AND comment_posted_by_id = '".$logged_user_id."') ";		
				
			$replies = $wpdb->get_results( $query);
			
			if ( !empty( $replies ) )
			{		
		
				foreach ( $replies as $reply )
				{
					
					$html .= '<div class="uultra-b-edit-reply">';
					$html .= '<p>'.__('Edit reply:', 'xoousers').'</p>';			
				
				
$html .='<textarea style="height: 40px; overflow: hidden; word-wrap: break-word; resize: none; width:95% !important; clear:both;" class="uultra-commentTextArea" id="uultra-edit-comment-text-box-'.$wall_comment_id.'">'.$reply->comment_message.'</textarea>';

					$html .='<input value="Update" id="uultra-wall-edit-reply-confirm" class="uultra-button-edit-comment-wall-edition-confirm" type="button" data-reply-id="'.$wall_comment_id.'"> <span><a href="#" class="uultra-close-edit-comment-box" title="'.__('Close', 'xoousers').'" data-reply-id="'.$wall_comment_id.'">'.__('close', 'xoousers').'</a></span> <span class="uultra-error-message-reply-empty" id="uultra-empty-message-comment-id-'.$wall_comment_id.'">'.__(' Please, input some text', 'xoousers').' </span>';
					
					$html .= '</div>';
									
				}	
			
			
			}
		
		}
		
		echo $html;
		
		die();
		
		
		
	}
	
	public function wall_edit_reply_confirm()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_reply_id =   sanitize_text_field($_POST["wall_reply_id"]);
		$wall_text =   sanitize_text_field($_POST["wall_text"]);
		
		$html = '';
		
		
		//update in the db		
		if($wall_reply_id >0)
		{			
			$query = "UPDATE " . $wpdb->prefix ."usersultra_wall_replies SET reply_message = '".$wall_text."' WHERE reply_id = '$wall_reply_id'  ";						
		    $wpdb->query( $query );		
		
		}
		
		echo stripslashes($wall_text);
		
		die();
	}
	
	public function wall_edit_comment_confirm()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$wall_comment_id =   sanitize_text_field($_POST["wall_reply_id"]);
		$wall_text =   sanitize_text_field($_POST["wall_text"]);
		
		$html = '';
		
		
		//update in the db		
		if($wall_comment_id >0)
		{			
			$query = "UPDATE " . $wpdb->prefix ."usersultra_wall SET comment_message = '".$wall_text."' WHERE (comment_id = '$wall_comment_id' AND comment_wall_user_id = '".$logged_user_id ."') OR   (comment_id = '$wall_comment_id' AND comment_posted_by_id = '".$logged_user_id ."')";						
		    $wpdb->query( $query );		
		
		}
		
		echo stripslashes($wall_text);
		
		die();
	}
	
	
	
	
	public function reply_private_message()
	{
		
		global $wpdb,  $xoouserultra;		
		require_once(ABSPATH . 'wp-includes/formatting.php');
		
		$logged_user_id = get_current_user_id();		
		$message_id =  sanitize_text_field($_POST["message_id"]);				
		$uu_message =   sanitize_text_field($_POST["uu_message"]);		
		$message = $this->get_one($message_id, $logged_user_id);
		
		$uu_subject =   __("Reply: ", 'xoousers')." ".$message->subject;
		
		//check if reply equal to sender
		$receiver_id = $message->sender;
		
		if($receiver_id==$logged_user_id)
		{
			
			$receiver_id = $message->recipient;
		
		
		}
		
		//get receiver
		
		$receiver = get_user_by('id',$receiver_id);		
		$sender = get_user_by('id',$logged_user_id);
		
		//store in the db
		
		if($receiver->ID >0)
		{
			
			$new_message = array(
						'id'        => NULL,
						'subject'   => $uu_subject,						
						'content'   => $uu_message,
						'sender'   => $logged_user_id,
						'recipient'   => $receiver_id,	
						'parent'   => $message->id,						
						'date'=> date('Y-m-d H:i:s'),
						'readed'   => 0,
						'deleted'   => 0
						
					);
					
					// insert into database
					$wpdb->insert( $wpdb->prefix . 'users_ultra_pm', $new_message, array( '%d', '%s', '%s', '%s',  '%s', '%s', '%s', '%s' , '%s' ));
					
			
			$xoouserultra->messaging->send_private_message_user($receiver ,$sender->display_name,  $uu_subject,$_POST["uu_message"]);
			
			
		
		}
		
		echo "<div class='uupublic-ultra-success'>".__(" Reply sent ", 'xoousers')."</div>";
		die();
		
		
		
	}
	
	
	//this is called when leaving a reply
	public function reload_whole_replies()
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";			
		$comment_id = $_POST['comment_id'];
		
		$html = "";
		
		//get replies
		$drReplies = $this->get_convers_replies($comment_id );
		
		if ( !empty( $drReplies ) )
		{
		
			$html .='<ul>';
			
			foreach ( $drReplies as $reply )
			{
  
				  //replieds
				  $html .='<li class="uultra-commentHolder">';
				  
				  $when = $this->nicetime($reply->reply_date);
				  
				   //check if i can delete.. only if it's my own profile or if it's my own comment
				  $can_delete = $this->can_delete_reply($reply->reply_commented_by_id);	
				  
				  
				   if( $can_delete)
					{
												
						$html .= ' <span class="uultraprofile-wall-delete">';
								   
						$html .= '<a href="#" title="'.__("Edit reply", "xoousers").'" class="uultra-wall-edit-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$comment_id.'"> <i class="fa fa-edit fa-2"></i> </a> ';	
									
								   // i can delete this reply.											  
						$html .= '<a href="#" title="'.__("Delete reply", "xoousers").'" class="uultra-wall-delete-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$comment_id.'"> <i class="fa fa-times fa-2"></i> </a> ';
								  
						$html .='</span>';		
																 
					   
					}
							  
							  
				  
				 
				  //avatar =
				  $html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $reply->reply_commented_by_id, 30, 'avatar', $pic_boder_type, 'fixed').'</span>';
				 
								
				  $html .='<p><a>'. $xoouserultra->userpanel->get_display_name($reply->reply_commented_by_id).'</a>: <span></a>: <span id="uultr-reply-text-box-id-'.$reply->reply_id.'">'.stripslashes($reply->reply_message).'</span></p>
								
								<div class="uultra-commentFooter"> <span class="timeago" >'.$when.'</span>&nbsp;</div>
								 <div class="uultra-edit-reply-fiv" id="uultra-edit-reply-box-'.$reply->reply_id.'"> </div>
								
						   </li>';
		   
			 } //end for each
			
			
			   $html .='   </ul>';
	
			
		
		} // end if
		echo $html;
	   die();
	}
	
	//show wall function called by shortode
	public function show_wall($atts)
	{
		global $xoouserultra;
		
		$atts2 = $atts;
		extract( shortcode_atts( array(		
		
			'user_id' => '',
			'template_width' => ''
			
								
		), $atts ) );
		
		$html ='<div id="uultra-site-wide-wall-container">';
		$html .= $this->uultra_get_wall($user_id, $howmany, $template_width);
		$html .='</div>';
		
		return $html;
		
	
	}
	
	public function uultra_reload_site_wide_wall()
	{
		global $xoouserultra;
		
		$atts2 = $atts;
		extract( shortcode_atts( array(		
		
			'user_id' => '',
			'template_width' => '70%',
			'howmany' => '10'
								
		), $atts ) );
		
		$html ='<div id="uultra-site-wide-wall-container">';
		$html .= $this->uultra_get_wall($user_id, $howmany, $template_width);
		$html .='</div>';
		
		echo $html;
		die();
		
	
	}
	
	function get_excerpt_by_id($the_excerpt,$excerpt_length)
	{
		$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
		$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	
		if(count($words) > $excerpt_length) :
			array_pop($words);
			array_push($words, '… ');
			$the_excerpt = implode(' ', $words);
		endif;
	
		$the_excerpt = '<p>' . $the_excerpt . '</p>';
	
		return $the_excerpt;
	}
	
	public function get_module_title($module, $item_id)
	{
		 
		 $message = "";
		 if($module == 'newuser')		 
		 {
			  $message = __(" has registered" , 'xoousers');
		
		 }elseif($module == 'post'){
			 
			 $post_title = get_the_title( $item_id );
			 $post_url = get_permalink( $item_id );
			 
			 $message = __(" wrote a new post " , 'xoousers');
			 $message .=", ". "<a href='". $post_url."'>".$post_title."</a>";
			 
			 //get post
		
		}elseif($module == 'update' || $module == ''){
			 
			 $message = __(" posted an update " , 'xoousers');
			 
		}elseif($module == 'photo'){
			 
			 $message = __(" has uploaded a new photo " , 'xoousers');
		
		}elseif($module == 'media-photo'){
			 
			 $message = __(" has shared a photo " , 'xoousers');
			 
		 }
		 
	  	
		return $message; 
	  
	 }
	 
	function get_total_replies( $post_parent_id, $post_type) 
	{
		global $wpdb;

	
		$where = " WHERE post_parent = '$post_parent_id'  AND post_type = '$post_type' and post_status= 'publish' ";	
		$count = $wpdb->get_var( "SELECT COUNT(*) as total FROM $wpdb->posts $where" );	
		return apply_filters( 'get_usernumposts', $count, $userid );
	}
	
	function get_total_comments( $post_id) 
	{
		global $wpdb;

		$comments_count  =wp_count_comments( $post_id );
		return $comments_count->approved;
	}
	
	
	 
	public function get_one_post_content($post_id)
	{
		global $xoouserultra;
		
		$post = get_post($post_id);		
		$permalink = get_permalink( $post->ID ); 			
		$comment_count = wp_count_comments($post->ID);
		
		$post_title = get_the_title( $post_id );
		$thumb = get_the_post_thumbnail($post_id, 'medium');		
			 
		
		// $content = apply_filters('the_content', $post->post_content);
		$content = $post->post_content;
		$content = str_replace(']]>', ']]&gt;', $content); 
			
		$desc = $this->get_excerpt_by_id($content, 20);
			
		//$total_replies  = $this->get_total_replies( $post->ID, 'reply' );
		
		//include facebook like
		
	 
			
		$html .='<li>';
		
		if($xoouserultra->get_option('uultra_site_wide_facebook_sharing_options')!='no')
		{		
			$html .= $this->uultra_include_fb_library(); 		
			$html .= '<div class="uultra-post-wall-share-facebook">';		
			$html .= $this->get_share_social_buttons($permalink);	
			$html .= '</div>';	
		
		}
			
		$html .= '<h1 class="uultra-post-title"><a href="'.$permalink.'">'. $post_title.'</a>';		//
		
			
		
		$html .= '</h1>';
		
		$html .='<div class="uultra-my-post-thumb">';
		$html .='<a href="'.$permalink.'">'.$thumb  .'</a>';			
		$html .='</div>';
			
			
		$html .='<div class="uultra-my-post-desc">';
		$html .='<p>'.$desc.'</p>';
		$html .='</div>';
			
		$html .='<div class="uultra-my-post-info-bar">';
		$html .='<p><span class="uultra-post-date"> <i class="fa fa-calendar "></i>'.date("m/d/Y",strtotime($post->post_date)).'</span>  <span class="uultra-post-comments"><i class="fa fa-comment-o "></i>'.$comment_count->approved .'</span> <span class="uultra-post-see"> <i class="fa fa-eye "> <a href="'.$permalink.'">read more </a></i></span></p>';
		$html .='</div>';
			
				
		$html .='</li>';
		
		return $html;
		
	}
	
	public function get_one_photo_media_sharing($user_id, $item_id, $source_path)
	{
		global $xoouserultra;
		
		$site_url = site_url()."/";
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
		
	
			//get photo
					
		$thumb = $site_url.$upload_folder."/site-wide-wall/".$source_path;					
		$thumb_img = "<img src='".$thumb."' />";		
		
		
		$html .='<li>';
			
		$html .='<div class="uultra-my-photo-thumb">';
		$html .='<a href="'.$permalink.'">'.$thumb_img  .'</a>';			
		$html .='</div>';	
			
				
		$html .='</li>';
		
		return $html;
		
	}
	
	public function get_one_photo_content($user_id, $item_id)
	{
		global $xoouserultra;
		
		$site_url = site_url()."/";
		
		$upload_folder =  $xoouserultra->get_option('media_uploading_folder'); 
		
		
		if (get_option('uultra_new_media_feature_pro') ==1)  // use the new media method 06-10-2014
		{
			$photo = $xoouserultra->get_photo($item_id , $user_id);			
			$thumb = $site_url.$upload_folder."/".$user_id."/".$photo->photo_thumb;								
			$html.= "<a href='#' class='' ><img src='".$thumb."' class='rounded'/> </a>";
			
			
		
		}else{ //use the old method
			
			//get photo
			$photo = $xoouserultra->photogallery->get_photo($item_id , $user_id);			
			$thumb = $site_url.$upload_folder."/".$user_id."/".$photo->photo_thumb;					
			$thumb_img = "<img src='".$thumb."' />";		
		
		}
		
					
			
		$html .='<li>';
			
		$html .= '<h1 class="uultra-photo-title"><a href="'.$permalink.'">'. $post_title.'</a></h1>';
		
		$html .='<div class="uultra-my-photo-thumb">';
		$html .='<a href="'.$permalink.'">'.$thumb_img  .'</a>';			
		$html .='</div>';
			
			
		$html .='<div class="uultra-my-photo-desc">';
		$html .='<p>'.$desc.'</p>';
		$html .='</div>';
			
			
		$html .='</li>';
		
		return $html;
		
	}
	
	public function get_share_social_buttons($url, $type = null)
	{
		$html = '<div class="fb-like" data-href="'.$url.'" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true" data-width="500"></div>';	
		
		
		return $html;
	}
	
	public function uultra_include_fb_library()
	{
		$html = '<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, "script", "facebook-jssdk"));</script>';
		
		return $html;
	}
	
	
	 
	 
	public function get_module_content($conversa)
	{
		
		$module = $conversa->comment_module;
		$item_id = $conversa->comment_module_item_id;
		$user_id = $conversa->comment_wall_user_id;
		
		 
		 $message = "";
		 if($module == 'newuser')		 
		 {
			  
		
		 }elseif($module == 'post'){
			 
			 $message = '';
			 //display my posts			
			 $html .= '<div class="wall-my-posts">';
			 $html .= '<ul>';							
			 $html .= $this->get_one_post_content($item_id);				
			 $html .= '</ul>';			
			 $html .= '</div>';			 
			 $message = $html;
		
		}elseif($module == 'photo'){
			 
			 $message = '';
			 $html .= '<div class="wall-my-photo">';
			 $html .= '<ul>';							
			 $html .= $this->get_one_photo_content($user_id, $item_id);				
			 $html .= '</ul>';			
			 $html .= '</div>';			 
			 $message = $html;
		 
					
		}elseif($module == 'update' || $module == ''){
			
			
			$message = '<p id="uultra-flb-comment-text-inline-'.$conversa->comment_id.'">';			
			$message .=stripslashes($conversa->comment_message);	
			$message .= '</p>';
		
		}elseif($module == 'media-photo'){
			
			
			$message_sharing =stripslashes($conversa->comment_message);	
			 
			 $message = '';
			 $html .= '<p id="uultra-flb-comment-text-inline-'.$conversa->comment_id.'">';	
			 $html .= $message_sharing ;
			 $html .= '</p>';
			 $html .= '<div class="wall-my-photo">';
			 $html .= '<ul>';							
			 $html .= $this->get_one_photo_media_sharing($user_id, $item_id, $conversa->comment_direct_source_path);				
			 $html .= '</ul>';			
			 $html .= '</div>';			 
			 $message = $html;		 
			
			 
		 }
		 
	  	
		return $message; 
	  
	 }
	 
	
	//SITE-WIDE WALL
	public function uultra_get_wall($user_id=null, $howmany=null,  $template_width=null)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		require_once(ABSPATH . 'wp-includes/user.php');
	
		$site_url = site_url()."/";	
		
		$logged_user_id = get_current_user_id();
		
		if($user_id=="")
		{
			$user_id = $_POST['user_id'];
		
		}
		
		$howmany = $xoouserultra->get_option('uultra_site_wide_wall_how_many');
		
		if($howmany=="")
		{
			$howmany =10;
		
		}
		
		if(isset($template_width))
		{
			$template_width = 'style="width:'.$template_width.'"';
		
		}else{
		
			$template_width = '';
		}
		
		//module activation
		
	   	$photo_sharing_active = $this->wall_check_enabled('media-photo');	
			
		$html ='<div class="uultra-wall-wrapper"  '.$template_width.'>';
		
		//write a message box		
		$html .= '<div class="uultra-publishContainer" style="clear:both">';
		
		 if(is_user_logged_in())
		 {
			 //avatar =
			$html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $logged_user_id, 60, 'avatar', $pic_boder_type, 'fixed').'</span>';
			
			$text_size_logged_n = 'width: 88%;';      
			
		 }
		
			
	    $html .= '<textarea class="uultra-msgTextArea" id="uultra-txtMessage"   style="height: 49px !important; overflow: hidden; word-wrap: break-word; resize: none; '.$text_size_logged_n.' " placeholder="'.__("What's new?","xoousers").'"></textarea>';
		
		
		$html .='<div class="uultra-tool-bar-option-buttons">';
		
		
		
		 if(is_user_logged_in() && $photo_sharing_active)
		 {
		
			$html .= '<a href="#" title="'.__("Share Photo", "xoousers").'" class="uultra-site-wide-upload-photo" id="uultra-upload-photo-site-wide"  > <i class="fa fa-camera fa-2"></i> </a> ';			
		
		}else{
			
			$html .= '<span id="uultra-not-loggedin-message" class="uultra-wall-logged-in-usage" >'.__("Please login to use this feature ","xoousers").' </span> ';	
			
			$html .= '<input type="hidden" value="nologgedin" id="uultra-no-logged-flag-btn">';		
		
		}
		
        $html .='<input value="'.__("Share","xoousers").'" class="xoouserultra-button-wall" id="uultra-site-wide-wall-post-commment"  data-id="'.$logged_user_id.'" type="button">

';

		 $html .='</div>'; //end write message
		
			
		if($photo_sharing_active)
		{
			$html .='<div class="uultra-site-wide-photo-uploader" id="uultra-wall-photo-uploader-box">';
			$html .=$this->wall_photo_uploader();			
			$html .='</div>';
	    
		}
		
		    
		
		$html .='</div>'; //end write a message box
		
		$html .= '<ul id="msgHolder">';
		
		//loop through messages		
		$drConversations = $this->get_conversation($user_id, $howmany);
		
		if ( !empty( $drConversations ) )
		{
			
			foreach ( $drConversations as $conversa )
			{
				
				$module_active = $this->wall_check_enabled($conversa->comment_module);
				
				if($module_active)
				{
				//echo "flag ";
				
				//print_r($conversa );
				$reply_msg_date = date("F j, Y, g:i a", strtotime($conversa->comment_date));				
				$user_id = $conversa->comment_posted_by_id;				
				$when_c = $this->nicetime($conversa->comment_date);	
				
				//get title depending on module id
				$message_action = $this->get_module_title($conversa->comment_module, $conversa->comment_module_item_id);
				
				//				
				$message_content =  $this->get_module_content($conversa);				
		
				//main message
				$html .= '<li class="uultra-postHolder" id="uultra-whole-comments-holder-'.$conversa->comment_id.'">';
				
				
				 //check if i can delete.. only if it's my own profile or if it's my own comment
				 $can_delete_update = $this->can_delete_wall_update($conversa);				
				
				if( ($can_delete_update && $conversa->comment_module=='') || ($can_delete_update && $conversa->comment_module=='update' ) || ($can_delete_update && $conversa->comment_module=='media-photo' ))
				{
					
					// i can delete this reply.		
					$html .= ' <span class="uultraprofile-update-wall-delete"> ';
					
					
					$html .= '<a href="#" title="'.__("Edit Update", "xoousers").'" class="uultra-wall-edit-update-inline"  data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-edit fa-2"></i> </a> ';				
					
					
					$html .=' <a href="#" title="'.__("Delete Update", "xoousers").'" class="uultra-wall-delete-message"  data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-times fa-2"></i> </a> </span>';
					$html .='</span>';
								 
				}
				
				
				
				
				//avatar =
				$html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $user_id, 50, 'avatar', $pic_boder_type, 'fixed').'</span>';
								
				$html .='<p><a >'. $xoouserultra->userpanel->get_display_name($user_id).'</a> <span> '.$message_action.' </span>,  <span class="timeago">'.$when_c.'</span></p>';
				
				$html .=''.$message_content.'';
				
				
				$html .='<div class="uultra-edit-reply-fiv" id="uultra-edit-update-box-'.$conversa->comment_id.'"> </div>';
				
				$html .=' <div class="uultra-postFooter">
					&nbsp;<a class="linkComment" href="#">'.__('Replies','xoousers').'</a>
					<div class="commentSection" id="uultra-replies-list-'.$conversa->comment_id.'">';

					//get replies
					$drReplies = $this->get_convers_replies($conversa->comment_id);
					
					if ( !empty( $drReplies ) )
					{
					
						$html .='<ul>';
						
						foreach ( $drReplies as $reply )
						{
							 $when = $this->nicetime($reply->reply_date);
							 							 
							 //check if i can delete.. only if it's my own profile or if it's my own comment
							 $can_delete = $this->can_delete_reply($reply->reply_commented_by_id);					 
			  
							  //replieds
							  $html .='<li class="uultra-commentHolder">';
							  
							  if( $can_delete)
							  {
								  
								   $html .= ' <span class="uultraprofile-wall-delete">';
								   
								    $html .= '<a href="#" title="'.__("Edit reply", "xoousers").'" class="uultra-wall-edit-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-edit fa-2"></i> </a> ';	
									
								   // i can delete this reply.											  
								  $html .= '<a href="#" class="uultra-wall-delete-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-times fa-2"></i> </a> ';
								  
								    $html .='</span>';						 
								 
							  }
							    
							 
							  //avatar =
							  $html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $reply->reply_commented_by_id, 30, 'avatar', $pic_boder_type, 'fixed').'</span>';
							 
											
							  $html .='<p><a>'. $xoouserultra->userpanel->get_display_name($reply->reply_commented_by_id).'</a>: <span></a>: <span id="uultr-reply-text-box-id-'.$reply->reply_id.'">'.stripslashes($reply->reply_message).'</span></p>
											
								<div class="uultra-commentFooter"> <span class="timeago" >'.$when.'</span>&nbsp;</div>
								
								<div class="uultra-edit-reply-fiv" id="uultra-edit-reply-box-'.$reply->reply_id.'"> </div>
								
							 </li>';
					   
			             } //end for each
						
						
				           $html .='   </ul>';
				
						
					
					} // end if
			
				
           $html .= ' </div>';
		   
		   $html .='  <div style="display: block" class="uultra-publishComment">
                    <textarea style="height: 40px !important; overflow: hidden; word-wrap: break-word; resize: none;" class="uultra-commentTextArea" placeholder="'.__("write a comment ...","xoousers").'" id="uultra-reply-to_comment-'.$conversa->comment_id.'"></textarea>';
					
		  
		  
		  if(!is_user_logged_in())
		  {			
					
		 	 $html .= '<span id="uultra-not-loggedin-message-reply-'.$conversa->comment_id.'" class="uultra-wall-logged-in-usage" >'.__("Please login to leave a reply ","xoousers").' </span> ';		  
		  }
          
		   $html .= '<input value="'.__("Comment","xoousers").'" id="uultra-wall-post-reply" class="xoouserultra-button-wall" type="button" data-comment-id="'.$conversa->comment_id.'">
                </div>
				
           
        </div>';
  
  
  			 $html .=' </li>';
			 
			 } //if enabled module
	
			} //for each
	
	
			} //end if
	
		//end message holder
		$html .= '</ul>';		
		$html .= '</div>';
	   
	   return $html;
	  
	}
	
	//get conversation displayed in users profile
	public function uultra_get_latest_conversations($user_id=null, $howmany=null)
	{
		global $xoouserultra;
		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		$site_url = site_url()."/";	
		
		if($user_id=="")
		{
			$user_id = $_POST['user_id'];
		
		}
		
		$howmany = $xoouserultra->get_option('uultra_user_wall_how_many');
		
		if($howmany=="")
		{
			$howmany = 5;
		
		}	
			
		$html ='<div class="uultra-user-profile-wall"  >';
		
		//write a message box		
		$html .= '<div class="uultra-publishContainer" style="clear:both">
    <textarea class="uultra-msgTextArea" id="uultra-txtMessage"   style="height: 49px important; overflow: hidden; word-wrap: break-word; resize: none;" placeholder="'.__("What's new?","xoousers").'"></textarea>';
	
	  if(is_user_logged_in())
	  {
		
			//$html .= '<a href="#" title="'.__("Share Photo", "xoousers").'" class="uultra-site-wide-upload-photo" id="uultra-upload-photo-site-wide"  > <i class="fa fa-camera fa-2"></i> </a> ';			
		
	  }else{
			
			$html .= '<span id="uultra-not-loggedin-message" class="uultra-wall-logged-in-usage" >'.__("Please login to use this feature ","xoousers").' </span> ';	
			
			$html .= '<input type="hidden" value="nologgedin" id="uultra-no-logged-flag-btn">';		
		
	  }
	
  	 $html .= ' <input value="'.__("Share","xoousers").'" class="xoouserultra-button-wall" id="uultra-wall-post-commment"  data-id="'.$user_id.'" type="button">';
   
   
 $html .= '</div>';
		
		$html .= '<ul id="msgHolder">';
		
		$drConversations = array();
		
		//loop through my own messages and friend's messages		
		$drConversations = $this->get_conversation_friends_follow($user_id, $howmany);
		
		if ( !empty( $drConversations ) )
		{
			
			$i = 0;
			
			
			
	    foreach ($drConversations as $key => $conversa) 
		{
			
			$module_active = $this->wall_check_enabled($conversa->comment_module);
			
			if($module_active)
			{
			
			if($i>=$howmany){ continue;}
			
			$conversa = json_decode(json_encode($conversa), FALSE);
			
				
				$reply_msg_date = date("F j, Y, g:i a", strtotime($conversa->comment_date));				
				$user_id = $conversa->comment_posted_by_id;				
				$when_c = $this->nicetime($conversa->comment_date);					
				
				//get title depending on module id
				$message_action = $this->get_module_title($conversa->comment_module, $conversa->comment_module_item_id);
				
				//				
				$message_content =  $this->get_module_content($conversa);		
		
				//main message
				$html .= '<li class="uultra-postHolder" id="uultra-whole-comments-holder-'.$conversa->comment_id.'">';
				
				
				 //check if i can delete.. only if it's my own profile or if it's my own comment
				 $can_delete_update = $this->can_delete_wall_update($conversa);	
				
				
								
				if( ($can_delete_update && $conversa->comment_module=='') || ($can_delete_update && $conversa->comment_module=='update' ) || ($can_delete_update && $conversa->comment_module=='media-photo' ))
				{
					
					// i can delete this reply.		
					$html .= ' <span class="uultraprofile-update-wall-delete"> ';					
					
					$html .= '<a href="#" title="'.__("Edit Update", "xoousers").'" class="uultra-wall-edit-update-inline"  data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-edit fa-2"></i> </a> ';				
					
					
					$html .=' <a href="#" title="'.__("Delete Update", "xoousers").'" class="uultra-wall-delete-message"  data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-times fa-2"></i> </a> </span>';
					$html .='</span>';
								 
				}
				
				
				//avatar =
				$html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $user_id, 50, 'avatar', $pic_boder_type, 'fixed').'</span>';
								
				$html .='<p><a >'. $xoouserultra->userpanel->get_display_name($user_id).'</a> <span> '.$message_action.' </span>,  <span class="timeago">'.$when_c.'</span></p>';
				
				$html .=''.$message_content.'';
				
				$html .='<div class="uultra-edit-reply-fiv" id="uultra-edit-update-box-'.$conversa->comment_id.'"> </div>';
				
								
				$html .=' <div class="uultra-postFooter">
					<a class="linkComment" href="#">'.__('Replies','xoousers').'</a>
					
					<div class="commentSection" id="uultra-replies-list-'.$conversa->comment_id.'">';

					//get replies
					$drReplies = $this->get_convers_replies($conversa->comment_id);
					
					if ( !empty( $drReplies ) )
					{
					
						$html .='<ul>';
						
						foreach ( $drReplies as $reply )
						{
							 $when = $this->nicetime($reply->reply_date);
							 							 
							 //check if i can delete.. only if it's my own profile or if it's my own comment
							 $can_delete = $this->can_delete_reply($reply->reply_commented_by_id);					 
			  
							  //replieds
							  $html .='<li class="uultra-commentHolder">';
							  
							  if( $can_delete)
							  {
								  // i can delete this reply.		
								  $html .= ' <span class="uultraprofile-wall-delete">';
								  
								   $html .= '<a href="#" title="'.__("Edit reply", "xoousers").'" class="uultra-wall-edit-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-edit fa-2"></i> </a> ';								   
								   								  
								  $html .= '<a href="#" title="'.__("Delete reply", "xoousers").'" class="uultra-wall-delete-reply" data-reply-id="'.$reply->reply_id.'" data-comment-id="'.$conversa->comment_id.'"> <i class="fa fa-times fa-2"></i> </a> ';	
								  
								  $html .= '</span>';				 
								 
							  }
							  
							 
							  //avatar =
							  $html .= '<span class="uultra-u-avatar">'.$xoouserultra->userpanel->get_user_pic( $reply->reply_commented_by_id, 30, 'avatar', $pic_boder_type, 'fixed').'</span>';
							 
											
							  $html .='<p><a>'. $xoouserultra->userpanel->get_display_name($reply->reply_commented_by_id).'</a>: <span></a>: <span id="uultr-reply-text-box-id-'.$reply->reply_id.'">'.stripslashes($reply->reply_message).'</span></p>
											
								<div class="uultra-commentFooter"> <span class="timeago" >'.$when.'</span>&nbsp;</div>
								
								<div class="uultra-edit-reply-fiv" id="uultra-edit-reply-box-'.$reply->reply_id.'"> </div>
															
								
							 </li>';
					   
			             } //end for each
						
						
				           $html .='   </ul>';
				
						
					
					} // end if
			
				
           $html .= ' </div>';
		   $html .='   <div style="display: block" class="uultra-publishComment">
                    <textarea style="height: 40px !important; overflow: hidden; word-wrap: break-word; resize: none;" class="uultra-commentTextArea" placeholder="'.__("write a comment ...","xoousers").'" id="uultra-reply-to_comment-'.$conversa->comment_id.'"></textarea>';
					
		   if(!is_user_logged_in())
		  {			
					
		 	 $html .= '<span id="uultra-not-loggedin-message-reply-'.$conversa->comment_id.'" class="uultra-wall-logged-in-usage" >'.__("Please login to leave a reply ","xoousers").' </span> ';		  
		  }
		  
           $html .=' <input value="'.__("Comment","xoousers").'" id="uultra-wall-post-reply" class="xoouserultra-button-wall" type="button" data-comment-id="'.$conversa->comment_id.'">
                </div>
				
           
        </div>
    </li>';
	
		
		$i++;
		
		    } //end if activate
	
			} //for each
	
	
			} //end if
	
	
	
	
		
		//end message holder
		$html .= '</ul>';
		$html .= '</div>';
		
			   
	   echo $html;
	   die();
	}
	
	
	
	function can_delete_reply($reply_user_id)
	{
		$return = false;
		
		$user_id = get_current_user_id();
		
		if($user_id == $reply_user_id) //this is one of my own reply, then i can delete it.
		{
			$return = true;
		
		}
		
		if($user_id == "") //user not logged in
		{
			$return = false;
		
		}
		
		return $return;
	
	}
	
	function can_delete_wall_update($conversa)
	{
		$return = false;
		
		$user_id = get_current_user_id();
		
		if(($user_id == $conversa->comment_wall_user_id) || ($user_id == $conversa->comment_posted_by_id)) //this is one of my own reply, then i can delete it.
		{
			$return = true;
		
		}
		
		if($user_id == "") //user not logged in
		{
			$return = false;
		
		}
		
		return $return;
	
	}
	
	
	function nicetime($date)
	{
		if(empty($date)) {
			return "No date provided";
				}
	   
		$periods         = array(__("second", 'xoousers'), __("minute", 'xoousers'), __("hour", 'xoousers'), __("day", 'xoousers'), __("week", 'xoousers'), __("month", 'xoousers'), __("year", 'xoousers'), __("decade", 'xoousers'));
		$lengths         = array("60","60","24","7","4.35","12","10");
	   
		$now             = time();
		$unix_date         = strtotime($date);
	   
		   // check validity of date
		if(empty($unix_date)) {   
			return "Bad date";
		}
	
		// is it future date or past date
		if($now > $unix_date) {   
			$difference     = $now - $unix_date;
			$tense         =  __("ago", 'xoousers');
		   
		} else {
			$difference     = $unix_date - $now;
			$tense         =  __("from now", 'xoousers');
		}
	   
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
	   
		$difference = round($difference);
	   
		if($difference != 1) {
			$periods[$j].= "s";
		}
	   
		return "$difference $periods[$j] {$tense}";
	}
	
	//this is used for sitie-wide activity	
	function get_conversation($user_id, $howmany)	
	{
		global $wpdb, $current_user, $xoouserultra;		
	
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_wall   ' ;		
	    
		
		if($user_id!="" && $user_id>0)
		{
			$sql .= " WHERE comment_wall_user_id = '".(int)$user_id."'  ";		
		}
		
		$sql .= "  ORDER BY comment_id DESC LIMIT  ".$howmany." ";
		
		$rows = $wpdb->get_results($sql);
		
			
		return  $rows;			
	
	}
	
	//this is used for user's profile and displays friend's post as well
	function get_conversation_friends_follow($user_id, $howmany)	
	{
		global $wpdb, $current_user, $xoouserultra;		
		
		$all_conversa = array();
			
    
		$sql = "SELECT * FROM " . $wpdb->prefix . "usersultra_wall  WHERE comment_wall_user_id = '".(int)$user_id."'";	$sql .= "  ORDER BY comment_id DESC LIMIT  ".$howmany." ";
				
		$rows = $wpdb->get_results($sql);
		
		if ( !empty( $rows ) )
		{
			foreach ( $rows as $reply ) //creat reply array
			{
				
				$all_conversa[$reply->comment_id] = array(
				'comment_id' =>$reply->comment_id,
				'comment_message' =>$reply->comment_message,
				'comment_title' =>$reply->comment_title, 
				 'comment_date' =>$reply->comment_date , 
				 'comment_posted_by_id' =>$reply->comment_posted_by_id ,
				  'comment_module' =>$reply->comment_module  ,
				   'comment_module_item_id' =>$reply->comment_module_item_id,
				    'comment_wall_user_id' =>$reply->comment_wall_user_id,
					'comment_visible_to' =>$reply->comment_visible_to,
					'comment_direct_source_path' =>$reply->comment_direct_source_path);	 				
			}	
		
		}
		
		//get friend's messages
		
		$my_friends = $this->uultra_get_my_friends_col($user_id);
		
		//echo "friends: ". $my_friends;
		
		if($my_friends!='')
		{
		
			$sql = "SELECT * FROM " . $wpdb->prefix . "usersultra_wall  WHERE comment_wall_user_id  IN(".$my_friends.") ";		$sql .= "  ORDER BY comment_id DESC LIMIT  ".$howmany." ";
					
		
			$rows = $wpdb->get_results($sql);
			
			if ( !empty( $rows ) )
			{
				foreach ( $rows as $reply ) //creat reply array
				{
					
					$all_conversa[$reply->comment_id] = array(
					'comment_id' =>$reply->comment_id,
					'comment_message' =>$reply->comment_message,
					'comment_title' =>$reply->comment_title, 
					 'comment_date' =>$reply->comment_date , 
					 'comment_posted_by_id' =>$reply->comment_posted_by_id ,
					  'comment_module' =>$reply->comment_module  ,
					   'comment_module_item_id' =>$reply->comment_module_item_id,
						'comment_wall_user_id' =>$reply->comment_wall_user_id,
						'comment_visible_to' =>$reply->comment_visible_to,
						'comment_direct_source_path' =>$reply->comment_direct_source_path);	 			
				}	
			
			}
		
		}		
		
		 krsort($all_conversa);
		return  $all_conversa;			
	
	}
	
	function uultra_get_my_friends_col($user_id)	
	{
		global $wpdb, $current_user, $xoouserultra;		
	
		$sql = ' SELECT  * FROM ' . $wpdb->prefix . 'usersultra_friends   ' ;		
	    $sql .= " WHERE friend_receiver_id = '".(int)$user_id."'  AND friend_status= '1'  ";	
		//echo $sql;		
		$rows = $wpdb->get_results($sql);
		$str= '';
		if ( !empty( $rows ) )
		{
			$count_total = count($rows);
			$i = 1;
			foreach ( $rows as $row ) //creat reply array
			{
				
				$str .= $row->friend_sender_user_id	;
				
				if($i<$count_total){$str .= ',';}				
				$i++;				
						
			}	
		
		}
		
		return  $str;			
	
	}
	
	function get_convers_replies($conversation_id)	
	{
		global $wpdb, $current_user, $xoouserultra;		
	
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'usersultra_wall_replies   ' ;		
	    $sql .= " WHERE reply_comment_id = '".$conversation_id."'  ORDER BY reply_id  ";	
		//echo $sql;		
		$rows = $wpdb->get_results($sql);
		return  $rows;			
	
	}
	
	
	
	
	
	
	
	
	public function message_delete()
	{
		
		global $wpdb,  $xoouserultra;
		
		$message_id = $_POST["message_id"];
		$logged_user_id = get_current_user_id();
		
			
		$sql = "UPDATE " . $wpdb->prefix . "users_ultra_pm SET `deleted` = '2' WHERE `id` = '$message_id' AND  `recipient` = '".$logged_user_id."' ";
		
		$wpdb->query($sql);
		
		echo "<div class='uupublic-ultra-success'>".__(" The message has been deleted. Please refresh your screen.", 'xoousers')."</div>";
		die();
	
	}
	
	

}
$key = "wall";
$this->{$key} = new XooUserWall();