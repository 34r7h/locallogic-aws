<?php
global $xoouserultra;

//print_r($table_headers);

$html .= '<table class="wp-list-table widefat uultra-directory-default"> <thead>
		 <tr>';

$qtip_classes = 'qtip-light ';	
$qtip_style = '';
							
//building headers							
foreach($table_headers as $header)
{
	
	$tooltipip_class = '';					
	if (isset($header['tooltip']) && $header_tooltips=='yes' )
	{
					
	    $tooltipip_class = '<a class="'.$qtip_classes.' uultra-tooltip" title="' . $header['tooltip'] . '" '.$qtip_style.'><i class="fa fa-info-circle reg_tooltip"></i></a>';
	
	}
						
	$html .='<th>' . $header['label'] . ' '.$tooltipip_class.'</th>';									
}

$html .='</thead>';
$html .='<tbody>';

//building users data
//print_r($table_metas);
foreach($users_list['users'] as $user)
{
	$user_id = $user->ID;
	$html .= "<tr>";	
								
	foreach ($table_metas as $meta)
	{
		//
		
		if($meta["visible"]!=0)
		{
			$user_meta =  $xoouserultra->userpanel->display_fields_on_table_directory($user_id, $pic_size, $display_country_flag, $meta["meta"]);	
		
		}else{ //not visible for public
		
			
			if($private_content_protection_type=='') //by defaul uses the logged in protection only
			{
				if (!is_user_logged_in())  //not logged in
				{
					 $user_meta = '<a class="'.$qtip_classes.' uultra-tooltip" title="' .$private_content_text. '" '.$qtip_style.'><i class="fa fa-eye-slash reg_tooltip"></i></a>';
					
					
				} else {
					
					$user_meta =  $xoouserultra->userpanel->display_fields_on_table_directory($user_id, $pic_size, $display_country_flag, $meta["meta"]);			
					
					
				}
			
			}elseif($private_content_protection_type=='role'){ //display value only to certain user roles
				
				//get current user role
				$user_id = get_current_user_id();
				$show_to_user_role = 1;	
				
				if(!isset($show_to_user_role_list) || $show_to_user_role_list =="")
				{
					$show_to_user_role_list = '';						
				}
				
				$xoouserultra->role->uultra_get_user_roles_by_id($user_id);
				$show_data_status =  $xoouserultra->role->uultra_fields_by_user_role($show_to_user_role, $show_to_user_role_list);
				
				if($show_data_status) //users can see it based on the roles.
				{
					$user_meta =  $xoouserultra->userpanel->display_fields_on_table_directory($user_id, $pic_size, $display_country_flag, $meta["meta"]);
				
				
				} else { //user CANNOT see it based on role
					
					$user_meta = '<a class="'.$qtip_classes.' uultra-tooltip" title="' .$private_content_text. '" '.$qtip_style.'><i class="fa fa-eye-slash reg_tooltip"></i></a>';
					
					
				}			
				
				
			}
		
		}		
		
		
		$html .= "<td>".$user_meta."</td>";
	}
	
	$html .= "</tr>\n";
}
									
$html .='</tr>';

$html .='</tbody>';
$html .='</table>';
?>