<?php
class XooActivity 
{
	
	//-------- modules: (update, newuser, topic, reply, posts, photo, gallery)
	var $mDateToday ;


	function __construct() 
	{
		$this->ini_module();
		$this->mDateToday =  date("Y-m-d"); 
		
		
	}
	
	public function ini_module()
	{
		global $wpdb;
	
    	  $query = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'usersultra_activity` (
		  `act_id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `act_user_id` int(11) NOT NULL ,
		  `act_item_id` int(11) NOT NULL,
		  `act_module` varchar(100) NOT NULL,
		  `act_title` varchar(100) NOT NULL,
		  `act_visibility` int(1) NOT NULL,			 
		  `act_date` datetime NOT NULL,
		  PRIMARY KEY (`act_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;';
		
		$wpdb->query( $query );	
		
		$this->update_table();
		
	}
	
	function update_table()
	{
		global $wpdb;
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_activity where field="act_visibility" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_activity add column act_visibility int (1) NOT NULL ; ';
			$wpdb->query($sql);
		}
	
	}
	
	public function update_activity($data) 
	{
		
		 global $wpdb;		 
		 
		 $title = $this->get_module_title($data['user_id'] ,$data['module']);				
				
		$new_query = array(
						'act_id'        => NULL,
						'act_user_id'   => $data['user_id'],						
						'act_item_id'   =>$data['act_item_id'],
						'act_module'   => $data['module'],
						'act_title'   => $title ,
						'act_visibility'   => $act_visibility,
						'act_date'   => date('Y-m-d H:i:s')
						
					);
		
		// insert into database
		if ( $wpdb->insert( $wpdb->prefix . 'usersultra_activity', $new_message, array( '%d', '%s', '%s', '%s' , '%s', '%s', '%s' ) ) )
		{
					
					
		}							
		
    }
	
	 public function get_module_title($user_id , $module)
	 {
		 
		 $message = "";
		 if($module == 'newuser')		 
		 {
			  $message = __(" has just registered" , 'xoousers');
		
		 }elseif($module == 'posts'){
			 
			 $message = __(" wrote a new post " , 'xoousers');
			 
		 }
		 
	  	
		return $message; 
	  
	 }
	
	 public function get_module_stats($item_id, $module)
	 {
		 global $wpdb;
		 
		 
         $sql = "SELECT  * FROM " . $wpdb->prefix . "usersultra_stats  WHERE stat_item_id  = '$item_id' AND stat_module = '$module'  ";	 
		 
		 $res = $wpdb->get_results( $sql );
		 
		 if ( !empty( $res ) )
		 {
			foreach ( $res as $row )
			{
				return  $row;
			
			}
			
			
		 }
     }
	
	
	
	
	
	

}
$key = "activity";
$this->{$key} = new XooActivity();