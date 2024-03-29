<?php
class XooPaypalPayment 
{
	var $pager;

	function __construct() 
	{
		
		$this->ini_db();		
				
		if (isset($_POST['txn_id'])) 
		{
				
			$this->handle_paypal_ipn($_POST);
					
			
		}	
		
		add_action( 'wp_ajax_package_add_new', array( $this, 'package_add_new' ));	
		add_action( 'wp_ajax_get_packages_ajax', array( $this, 'get_packages_ajax' ));
		add_action( 'wp_ajax_package_delete', array( $this, 'package_delete' ));
		add_action( 'wp_ajax_package_edit_form', array( $this, 'package_edit_form' ));		
		add_action( 'wp_ajax_package_edit_confirm', array( $this, 'package_edit_confirm' ));
		

	}
	
	public function ini_db()
	{
		global $wpdb;

			// Create table
			$query = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'usersultra_packages (
				`package_id` bigint(20) NOT NULL auto_increment,
				`package_name` text NOT NULL,
				`package_desc` text NOT NULL,
				
				`package_approvation` varchar(5)  NULL,
				`package_limit_photos` int(11)  NULL,
				`package_limit_galleries` int(11) NULL,
				`package_limit_posts` int(11) NULL,
				`package_limit_posts_read` varchar(250) NULL,					
				`package_credits` decimal(11,2) NULL,
				`package_customization` text NOT NULL,
				`package_role` varchar(100)  NULL,	
				`package_registration_form` varchar(100)  NULL,						
									
				`package_type` varchar(60) NOT NULL,
				`package_number_of_times` varchar(60) NOT NULL,
				`package_time_period` varchar(60) NOT NULL,
				`package_amount` decimal(11,2) NOT NULL,				
				PRIMARY KEY (`package_id`)
			) COLLATE utf8_general_ci;';
	
	
		$wpdb->query( $query );
		
		$this->update_package_table();
		
		
	}
	
	function update_package_table()
	{
		global $wpdb;
		
							
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_limit_photos" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_limit_photos int (11) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_limit_galleries" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_limit_galleries int (11) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_limit_posts" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_limit_posts int (11) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_limit_posts_read" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_limit_posts_read varchar (250) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_role" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_role varchar (100) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_registration_form" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_registration_form varchar (100) ; ';
			$wpdb->query($sql);
		}
		
		
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_credits" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_credits decimal (11,2) ; ';
			$wpdb->query($sql);
		}
		
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_approvation" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_approvation varchar (5) ; ';
			$wpdb->query($sql);
		}
		
		$sql ='SHOW columns from ' . $wpdb->prefix . 'usersultra_packages where field="package_customization" ';		
		$rows = $wpdb->get_results($sql);		
		if ( empty( $rows ) )
		{	
			$sql = 'Alter table  ' . $wpdb->prefix . 'usersultra_packages add column package_customization text ; ';
			$wpdb->query($sql);
		}
		
		
		
	}
	
	/*handle ipn*/
	public function handle_paypal_ipn($paypal_response)
	{
				
		global $wpdb,  $xoouserultra;
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		
		$req = 'cmd=_notify-validate';

		// Read the post from PayPal system and add 'cmd'
		$fullipnA = array();
		foreach ($_POST as $key => $value)
		{
			$fullipnA[$key] = $value;
		
			$encodedvalue = urlencode(stripslashes($value));
			$req .= "&$key=$encodedvalue";
		}
		
		$fullipn =$this->Array2Str(" : ", "\n", $fullipnA);
		
				
		$response = $this->curl_call($req);
		
		// Assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$txn_type = $_POST['txn_type'];
		$pending_reason = $_POST['pending_reason'];
		$payment_type = $_POST['payment_type'];
		$custom_key = $_POST['custom'];
		
		//tweak for multi purchase
		$custom = explode("|", $_POST['custom']);
		
		$type = $custom[0];
		$custom_key = $custom[1];
		
		
		if (strcmp ($response, "VERIFIED") == 0)		
	    {			
			
			/*VALID TRANSACTION*/			
			$errors = "";
			
			// Get Order
			$rowOrder = $xoouserultra->order->get_order_pending($custom_key);
			
			if ($rowOrder->order_id=="")    
			{
				$errors .= " --- Order Key Not VAlid: " .$custom_key;
				
			}
			
			$paypal_email = $xoouserultra->get_option("gateway_paypal_email");
			$paypal_currency_code = $xoouserultra->get_option("gateway_paypal_currency");			
				
			$order_id = $rowOrder->order_id;
			$user_id = $rowOrder->order_user_id;		
			$buyer_name= $rowOrder->order_name;				
			$total_price = $rowOrder->order_amount;  				
			$business_email = $paypal_email;	
			
			$package_id= $rowOrder->order_package_id;
			$start_date= $rowOrder->order_date;	
			
				
			
			/*Transaction Type*/
			
			if($txn_type=="subscr_cancel" )
			{
				//payment cancelled				
				$errors .= " --- Payment Failed";
				
				/*Update User Status*/				
				update_user_meta ($user_id, 'usersultra_account_status', 'canceled');
				
			}elseif($txn_type=="subscr_eot"){
				
				//payment cancelled				
				$errors .= " --- Payment Expired";
				
				/*Update User Status*/				
				update_user_meta ($user_id, 'usersultra_account_status', 'expired');
			
			}elseif($txn_type=="failed"){
				
				//payment cancelled				
				$errors .= " --- Payment Failed";
				
				/*Update User Status*/				
				update_user_meta ($user_id, 'usersultra_account_status', 'failed');
						
				
			}else{
				
				//sucesful transaction
				
				// check that payment_amount is correct		
				if ($payment_amount < $total_price)    
				{
					$errors .= " --- Wrong Amount: Received $payment_amount$payment_currency; Expected: $total_price$paypal_currency_code";
					
				}
				
				// check currency						
				if ($payment_currency != $paypal_currency_code)
				{
					$errors .= " --- Wrong Currency - Received: $payment_amount$payment_currency; Expected: $total_price$paypal_currency_code";
					
				}
			}
			
			if ($errors=="")
			{
				if ($type=="ini")
				{
				
					/*Update Order status*/				
					$xoouserultra->order->update_order_status($order_id,'confirmed');
					
					/*Update Order With Payment Response*/				
					$xoouserultra->order->update_order_payment_response($order_id,$txn_id);		
					
					/*Update User Status*/				
					update_user_meta ($user_id, 'usersultra_account_status', 'active');
					update_user_meta ($user_id, 'usersultra_account_type', 'paid');
					
					//expiration date
					$start_date = date("Y-m-d"); 					
					$expiration_date = $this->get_expiration_date_based_on_package($start_date,$package_id);
					$xoouserultra->order->update_expiration_date($order_id,$expiration_date);
					
					//expiration meta data
					update_user_meta ($user_id, 'usersultra_membership_expiration', $expiration_date);
					
					//role settings					
					$package = $xoouserultra->paypal->get_package($package_id);	
					
					//get user				
					$user = get_user_by( 'id', $user_id );
					
					/*Notify User&Admin */					
					$u_email=$user->user_email;
					$user_login= $user->user_login;
					//$user_pass =get_user_meta($user_id, 'usersultra_temp_password', true);				
					$xoouserultra->messaging->welcome_email_paid($u_email, $user_login, $user_pass, $package);
				
				}elseif($type=="upgrade"){
				
					
					/*Update Order status*/				
					$xoouserultra->order->update_order_status($order_id,'confirmed');					
				
					/*Update Order With Payment Response*/				
					$xoouserultra->order->update_order_payment_response($order_id,$txn_id);		
					
					/*Update User Status*/				
					update_user_meta ($user_id, 'usersultra_account_status', 'active');
					update_user_meta ($user_id, 'usersultra_account_type', 'paid');
					
					//expiration date 					
					$expiration_date = $this->get_expiration_date_based_on_package($start_date,$package_id);
					$xoouserultra->order->update_expiration_date($order_id,$expiration_date);
					
					//expiration meta data
					update_user_meta ($user_id, 'usersultra_membership_expiration', $expiration_date);					
								
					
					//get user				
					$user = get_user_by( 'id', $user_id );
					
					/*Notify User&Admin */					
					$u_email=$user->user_email;
					$user_login= $user->user_login;
					$xoouserultra->messaging->welcome_email_paid_upgrade($u_email, $user_login, $user_pass);			
					
					//package 
					update_user_meta ($user_id, 'usersultra_user_package_id', $package_id);
					
					//upgraded 
					update_user_meta ($user_id, 'usersultra_user_upgraded', 'yes');
					
					//role settings					
					$package = $xoouserultra->paypal->get_package($package_id);					
					$package_role = $package->package_role;
					
					//set custom role for this package
					if($package_role!="")
					{
						$user = new WP_User( $user_id );
						$user->set_role( $package_role );						
					}
					
				}		
				
			}else{
				
				//$xoouserultra->messaging->paypal_ipn_debug("IPN ERRORS: ".$errors);
				
			
			}
			
			
		
		
		}else{
			
			//$xoouserultra->messaging->paypal_ipn_debug("IPN NOT VERIFIED: ".$fullipn);			
			
			/*This is not a valid transaction*/
		}
		
		
		
	
	}
	
	/*Get Expiration Date*/
	public function get_expiration_date_based_on_package($start_date,$package_id)
	{		
		
		global  $xoouserultra;
		
		//get package
		$package = $this->get_package($package_id);
		
		$amount = $package->package_amount;
		$p_name = $package->package_name;
		$package_id = $package->package_id;
		$package_type= $package->package_type;
		
		$package_period= $package->package_number_of_times;
		$package_time_period= $package->package_time_period;
		
		if($package_time_period=="M")
		{
			$period = 'month';
			
		}elseif($package_time_period=="D"){
			
			$period = 'day';
			
		}elseif($package_time_period=="Y"){
			
			$period = 'year';	
		
		}elseif($package_time_period=="W"){
			
			$period = 'week';		
		
		}
		
		$to_sum = $package_period;
		
		//set expiration date					 
		$ini_date = $start_date ;
		$valid_until=  date("Y-m-d", strtotime("$ini_date + $to_sum $period"));
		
		return $valid_until;	
			
		
	}
	
	
	
	public function curl_call($req)
	{
		
		global $wpdb,  $xoouserultra;
				
		$mode = $xoouserultra->get_option("gateway_paypal_mode");
		
		if ($mode==1) 
		{
			$url ='https://www.paypal.com/cgi-bin/webscr';	
		
		}else{	
		
			$url ='https://www.sandbox.paypal.com/cgi-bin/webscr'; 	
		
		}
		
		$curl_result=$curl_err='';
		
		$fp = curl_init();
		curl_setopt($fp, CURLOPT_URL,$url);
		curl_setopt($fp, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($fp, CURLOPT_POST, 1);
		curl_setopt($fp, CURLOPT_POSTFIELDS, $req);
		curl_setopt($fp, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
		curl_setopt($fp, CURLOPT_HEADER , 0); 
		curl_setopt($fp, CURLOPT_VERBOSE, 1);
		curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($fp, CURLOPT_TIMEOUT, 30);
		
		$response = curl_exec($fp);
		$curl_err = curl_error($fp);
		curl_close($fp);
		
		return $response;
	}
	
	function StopProcess()
	{
	
		exit;
	}
	
	function Array2Str($kvsep, $entrysep, $a)
	{
		$str = "";
		foreach ($a as $k=>$v)
		{
			$str .= "{$k}{$kvsep}{$v}{$entrysep}";
		}
		return $str;
	}
	
	function get_package_url()
	{
		global $wpdb,  $xoouserultra, $wp_rewrite;		
		$wp_rewrite = new WP_Rewrite();		
		require_once(ABSPATH . 'wp-includes/link-template.php');
		
		
		$page_id = $xoouserultra->get_option('registration_page_id');
		$subscrition_url = get_permalink($page_id);
		$web_url = site_url();
		$url = $subscrition_url;
		
		return $url;
	
	}
	
	
	/*Get Package*/
	public function get_ipn_link($order,$tran_type)
	{
		
		
		global $wpdb,  $xoouserultra, $wp_rewrite;
		
		$wp_rewrite = new WP_Rewrite();
		
		require_once(ABSPATH . 'wp-includes/link-template.php');	
		
		extract($order);
		
		$paypal_email = $xoouserultra->get_option("gateway_paypal_email");
		$currency_code = $xoouserultra->get_option("gateway_paypal_currency");
		
		//get package
		$package = $xoouserultra->paypal->get_package($order_package_id);
		
		$amount = $package->package_amount;
		$p_name = $package->package_name;
		$package_id = $package->package_id;
		$package_type= $package->package_type;
		
		$package_period= $package->package_number_of_times;
		$package_time_period= $package->package_time_period;
		
		//modified on 08-20-2014
		$paypalcustom = $tran_type."|".$transaction_key;
		
		//get IPN Call Back URL:
		$web_url = site_url();
		$notify_url = $web_url."/?usersultraipncall=".$transaction_key;
		
		/*return sucess transaction - By default the user is taken to the backend*/		
		$account_page_id = get_option('xoousersultra_my_account_page');
		$my_account_url = get_permalink($account_page_id);
				
		$sucess_url = $my_account_url."?usersultraipncall=".$transaction_key;			
				
		$mode = $xoouserultra->get_option("gateway_paypal_mode");
		
		if($mode==1)
		{
			
			$mode = "www";			
			
		}else{
			
			$mode = "sandbox";
			$paypal_email = $xoouserultra->get_option("gateway_paypal_sandbox_email");
		
		}
		
		if($package_type=="recurring")
		{
			$type = "_xclick-subscriptions";
			
			$url = "https://".$mode.".paypal.com/webscr?cmd=".$type."&business=".$paypal_email."&currency_code=".$currency_code."&no_shipping=1&item_name=".$p_name."&return=".$sucess_url."&notify_url=".$notify_url."&custom=".$paypalcustom."&a3=".$amount."&p3=".$package_period."&t3=".$package_time_period."&src=1&sra=1";
			
		}
		
		if($package_type=="onetime")
		{
			$type = "_xclick";
			
			$url = "https://".$mode.".paypal.com/webscr?cmd=".$type."&business=".$paypal_email."&currency_code=".$currency_code."&no_shipping=1&item_name=".$p_name."&return=".$sucess_url."&notify_url=".$notify_url."&custom=".$paypalcustom."&amount=".$amount."&p3=".$package_period."&t3=".$package_time_period."&src=1&sra=1";
		}
		
		
		return $url;
		
	}
	
	
	
	public function package_delete ()
	{
		global $wpdb,  $xoouserultra;
		
		$p_id = $_POST["p_id"];
		
		$query = "DELETE FROM " . $wpdb->prefix ."usersultra_packages  WHERE  `package_id` = '$p_id'  ";		
		$wpdb->query( $query );
	
	}
	
	
	/*Get Package*/
	public function get_package ($package_id)
	{
		global $wpdb,  $xoouserultra;
		
		$packages = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_packages WHERE package_id = "'.$package_id.'" ' );
		
		if ( empty( $packages ) )
		{
		
		
		}else{
			
			
			foreach ( $packages as $package )
			{
				return $package;			
			
			}
			
		}
		
		
	
	}
	
		/*Get All Packages for Upgrade */
	public function get_upgrade_packages ()
	{
		global $wpdb,  $xoouserultra;
		
		$currency_symbol =  $xoouserultra->get_option('paid_membership_symbol');
		$date_format =  $xoouserultra->get_option('uultra_date_format');
		
		if($date_format==""){date_format=="m/d/Y";}
		
		//get user package
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;	
		
		//date
		$acc_creation_date = date($date_format, strtotime($current_user->user_registered));
		$user_package_info = 	$xoouserultra->userpanel->get_user_account_type_info($user_id);
		$current_package_id = $user_package_info["id"];
		$current_package_name = $user_package_info["name"];
		$current_package_amount = $user_package_info["price"];	
		
		$html = '';
		
		if($xoouserultra->get_option('allow_account_upgrading')=='yes')
		{
			
			
			 
			$html .= '  <div class="commons-panel-content">';		
			$html .= '  <form name="xoouserultra-upgrading-account-form" id="xoouserultra-upgrading-account-form" method="post">';
			$html .= '  <input type="hidden" name="xoouserultra-upgrading-account-confirm" id="xoouserultra-upgrading-account-confirm">';
			
			$html .= ' <h2>'. __('Account Upgrading','xoousers').'</h2>';		
			$html .= ' <div class="xoouserultra-package-list-upgrade">';
			$html.= '<div class="uultra-account-type"><p><span>'.__('Account ID: ').'</span> '.$user_id.' <span>'.__('Registered Date: ').'</span> '.$acc_creation_date.' <span>'.__('Account Type: ').'</span> '.$current_package_name.'</p></div>';
			
			$packages = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_packages  ORDER BY `package_amount` ASC' );
			
			if ( empty( $packages ) )
				{
					$html.= '<p>' .__( 'You have no packages yet.', 'xoousers' ). '</p>';
				
				}else{
					
					$html .= "<ul>" ;
					
					$n = count( $packages );
					$num_unread = 0;
					
					$default_checked = 0;
					
					$plan_id = "";
					
									
									
					foreach ( $packages as $package )
					{
						$checked = '';
						
						if($current_package_id== $package->package_id ||$default_checked==0)
						{
							$checked = 'checked="checked"';
							
						}
						
						$amount = $currency_symbol.$package->package_amount;		
						
						
						if($package->package_amount>$current_package_amount)  //only if it's a greater package
						{	
							
							
							$html.= '<li> 
							
							<div class="uultra-package-opt">
							
							<span class="uultra-package-title"><input type="radio" name="usersultra_package_id" value="'.$package->package_id.'" id="RadioGroup1_'.$package->package_id.'"  '.$checked.'/><label for="RadioGroup1_'.$package->package_id.'"><span><span></span></span>  - '.$package->package_name.' </label></span>
							
							<span class="uultra-package-cost">'.$amount.' </span></div>
							<div class="uultra-package-desc">
							<p>'.$package->package_desc.'</p>
							</div>		
								
							</li>';
							
						
			 
			 $default_checked++;
			 
						}
					
					
					}
					
					$html .= "</ul>" ;				
					$html .= '<p><input type="submit" name="xoouserultra-register" class="xoouserultra-button" value="'.__('CLICK TO UPGRADE YOUR ACCOUNT','xoousers').'" /></p>';
					
					$html .= "</div>" ;
					$html .= "</form>" ;
					$html .= "</div>" ;
			}
		
		}
		
		return $html;
		
	}
	
	
	/*Get All Packages*/
	public function get_packages ()
	{
		global $wpdb,  $xoouserultra;
		
		$currency_symbol =  $xoouserultra->get_option('paid_membership_symbol');
		
		
		$html = "";
		
		$packages = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_packages  ORDER BY `package_amount` ASC' );
		
		if ( empty( $packages ) )
			{
				$html.= '<p>' .__( 'You have no packages yet.', 'xoousers' ). '</p>';
			
			}else{
				
				$html .= "<ul>" ;
				
				$n = count( $packages );
				$num_unread = 0;
				
				$default_checked = 0;
				
				$plan_id = "";
				
				if(isset($_GET["plan_id"]) && $_GET["plan_id"]>0)
				{
					$plan_id =$_GET["plan_id"];
				
				}
				
				//display only selected
				
				$display_only_selected =  $xoouserultra->get_option('membership_display_selected_only');
				$only_selected = false;
				if( $display_only_selected== 1)
				{
					$only_selected = true;
						
				}
				
				foreach ( $packages as $package )
				{
					$checked = '';
					
					if( $plan_id== $package->package_id ||$default_checked==0)
					{
						$checked = 'checked="checked"';
						
					}
					
					$amount = $currency_symbol.$package->package_amount;
					
					
					if($only_selected )
					{
						
						if($package->package_id==$plan_id || $plan_id=="")
						{							
							
							$html.= '<li> 
							
							<div class="uultra-package-opt">
							
							<span class="uultra-package-title"><input type="radio" name="usersultra_package_id" value="'.$package->package_id.'" id="RadioGroup1_'.$package->package_id.'"  '.$checked.'/><label for="RadioGroup1_'.$package->package_id.'"><span><span></span></span>  - '.$package->package_name.' </label></span>
							
							<span class="uultra-package-cost">'.$amount.' </span></div>
							<div class="uultra-package-desc">
							<p>'.$package->package_desc.'</p>
							</div>		
								
							</li>';
						
						}else{
							
						}
					
					}else{
						
						
						$html.= '<li> 
					
					<div class="uultra-package-opt">
					
					<span class="uultra-package-title"><input type="radio" name="usersultra_package_id" value="'.$package->package_id.'" id="RadioGroup1_'.$package->package_id.'"  '.$checked.'/><label for="RadioGroup1_'.$package->package_id.'"><span><span></span></span>  - '.$package->package_name.' </label></span>
					
					<span class="uultra-package-cost">'.$amount.' </span></div>
					<div class="uultra-package-desc">
					<p>'.$package->package_desc.'</p>
					</div>		
						
	     			</li>';
						
					}
		 
		 $default_checked++;
				
				
				}
				
				$html .= "</ul>" ;
		}
		
		return $html;
		
	}
	
	/*Get All Packages Edit*/
	public function get_packages_edit ()
	{
		global $wpdb,  $xoouserultra;
		
		$currency_symbol =  $xoouserultra->get_option('paid_membership_symbol');
		
		
		$html = "";
		
		$packages = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_packages  ORDER BY `package_amount` ASC' );
		
		if ( empty( $packages ) )
			{
				$html.= '<p>' .__( 'You have no packages yet.', 'xoousers' ). '</p>';
			
			}else{
				
				$html .= "<ul>" ;
				
				$n = count( $packages );
				$num_unread = 0;
				
				$default_checked = 0;
				
				foreach ( $packages as $package )
				{
					$checked = '';
					
					if($default_checked==0)
					{
						$checked = 'checked="checked"';
						
					}
					
					
					
					$html.= '<li> 
					
					<div class="uultra-package-opt">
					
					<span class="uultra-package-title"><input type="radio" name="usersultra_package_id" value="'.$package->package_id.'" id="RadioGroup1_'.$package->package_id.'"  '.$checked.'/>
    - '.$package->package_name.'</span>
					
					<span class="uultra-package-cost">'.$currency_symbol.$package->package_amount.' </span></div>
					<div class="uultra-package-desc">
					<p>'.$package->package_desc.'</p>
					</div>
					
					
						
	     </li>';
		 
		 $default_checked++;
				
				
				}
				
				$html .= "</ul>" ;
		}
		
		return $html;
		
	}
	
	public function get_packages_private ()
	{
		global $wpdb,  $xoouserultra;
		$html = "";
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'usersultra_packages  ORDER BY `package_amount` ASC';
		$packages = $wpdb->get_results($sql );
		
		return $packages;
		
	}
	
	function get_custom_form_package_edition($package_id ,$current_form  )
	{
		global $wpdb,  $xoouserultra ,$uultra_form;
		
		$forms = $uultra_form->get_all();
		
		$html = '<select name="p_custom_registration_form_'.$package_id.'" id="p_custom_registration_form_'.$package_id.'">';
		
		$html .= ' <option value="'.$key.'">
                       '.__('Defaul Registration Form', 'xoousers').'
                    </option>';
					
		if (!empty($forms))
		{	
                
                foreach ( $forms as $key => $form )
				{
					$sel = '';
					
					if($current_form==$key)
					{
						$sel = 'selected="selected"';						
					}
					
                   $html .= ' <option value="'.$key.'"  '.$sel.'>
                       '.$form['name'].'
                    </option>';
                
                }
				
		}
		
		$html .= '</select>';
		
		return $html;
	
	}
	
	
	public function package_edit_form()
	{
		global $wpdb,  $xoouserultra;
		
		$html = "";
		
		$package_id = $_POST["p_id"];			
		$package = $this->get_package($package_id);
				
		if (!empty($package)){				
								
        
        
           $html .=' <h3>'.__('Edit Package', 'xoousers').'</h3>
         
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
           <tr>
             <td width="14%">'.__('Name:', 'xoousers').':</td>
             <td width="86%"><input type="text" name="p_name" id="p_name_'.$package->package_id.'" value="'.$package->package_name.'" /></td>
           </tr>
           <tr>
             <td>'.__('Description', 'xoousers').':</td>
             <td>
			 <textarea  cols=""  name="p_desc" id="p_desc_'.$package->package_id.'" style="height:80px; width:50%;">'.$package->package_desc.'</textarea>
			</td>
           </tr>
           <tr>
             <td>'.__('Price', 'xoousers').':</td>
             <td><input type="text" name="p_price" id="p_price_'.$package->package_id.'" value="'.$package->package_amount.'"/></td>
           </tr>

           <tr>
             <td>'.__('Type', 'xoousers').':</td>
             <td><select name="p_type" id="p_type_'.$package->package_id.'">
			 
			 ';
			 
			 $rec = "";
			 $ontime ="";
			 
			 if($package->package_type=="recurring"){$rec = 'selected="selected"';} 
			 if($package->package_type=="onetime"){$ontime = 'selected="selected"';} 
			 
              $html .='  <option value="recurring" '.$rec.' >'.__('Recurring', 'xoousers').'</option>
               <option value="onetime" '.$ontime.'>'.__('One-Time', 'xoousers').'</option> ';
			   
			   
            $html .=' </select></td>
           </tr>
		   
		   		   
		   <tr>
             <td>'.__('Every', 'xoousers').':</td>
             <td>
             <select name="p_every" id="p_every_'.$package->package_id.'">
             <option value="1" >1</option>
              ';
			  
			  $i = 2;
              
			  while($i <=31){
				  
				  $sel = "";
				  if($i==$package->package_number_of_times){$sel=' selected="selected"';}
			
              
                 $html .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';  
              
			    $i++;
			  }
             
             $html .= '</select></td>
           </tr>
           <tr>
             <td> '.__('Billing Period:', 'xoousers').'</td>
             <td><label for="p_period"></label>
               <select name="p_period" id="p_period_'.$package->package_id.'">';
			   
			   $m = "";
			   $w = "";
			   $d = "";
			   $y = "";
			   
			    if($package->package_time_period=="M"){$m = 'selected="selected"';} 
			    if($package->package_time_period=="W"){$w = 'selected="selected"';}
				if($package->package_time_period=="D"){$d = 'selected="selected"';}
				if($package->package_time_period=="Y"){$y = 'selected="selected"';} 
			 
			 
                 $html .= '<option value="M" '.$m.'> '.__('Months:', 'xoousers').'</option>
				  <option value="W" '.$w.'> '.__('Weeks:', 'xoousers').'</option>
                 <option value="D" '.$d.'> '.__('Days:', 'xoousers').'</option>
                  <option value="Y" '.$y.'> '.__('Years:', 'xoousers').'</option>
				  
             </select></td>
           </tr>
		   
		   <tr>
             <td> '.__('Requires Admin Moderation:', 'xoousers').'</td>
             <td><label for="p_moderation"></label>
               <select name="p_moderation" id="p_moderation_'.$package->package_id.'">';
			   
			   $m = "";
			   $w = "";
			  
			   
			    if($package->package_approvation=="yes"){$m = 'selected="selected"';} 
			    if($package->package_approvation=="no"){$w = 'selected="selected"';}
							 
                 $html .= '<option value="yes" '.$m.'> '.__('Yes', 'xoousers').'</option>
				  <option value="no" '.$w.'> '.__('No', 'xoousers').'</option>
               
				  
             </select></td>
           </tr>
		   
		     <tr>
             <td>'.__('Role To Assign:','xoousers').'</td>
             <td>'.$xoouserultra->role->get_package_roles_edit($package->package_id, $package->package_role).'
               
			   
			   </td>
           </tr>
		   
		   
		     <tr>
             <td>'.__('Custom Form To Assign:','xoousers').'</td>
             <td>'.$this->get_custom_form_package_edition($package->package_id, $package->package_registration_form).'
               
			   
			   </td>
           </tr>
		   
		   
           
          </table>';
		  
		  //customization
		  $customization = $package->package_customization;
		  $customization = unserialize($customization);
		  
		  if(is_array($customization))
		  {
			  $p_price_color = $customization["p_price_color"];
			  $p_price_bg_color = $customization["p_price_bg_color"];
			  
			  $p_signup_color = $customization["p_signup_color"];
			  $p_signup_bg_color = $customization["p_signup_bg_color"];
			 
			
		  }
		  
		  $html .='  <h3>'.__('Pricing Table Customization','xoousers').'</h3>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
                <td width="24%"> '. __('Name/Price Font Color','xoousers').'</td>
                <td width="76%"><input name="p_price_color" type="text" id="p_price_color_'.$package->package_id.'" value="'.$p_price_color.'" class="color-picker" data-default-color=""/> 
         </td>
              </tr>
              <tr>
                <td> '.__('Name/Price Background Color','xoousers').'</td>
                <td><input name="p_price_bg_color" type="text" id="p_price_bg_color_'.$package->package_id.'" value="'. $p_price_bg_color.'" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
			  
			   <tr>
                <td> '.__('Sign Up Button Text Color','xoousers').'</td>
                <td><input name="p_signup_color" type="text" id="p_signup_color_'.$package->package_id.'" value="'. $p_signup_color.'" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
			  
			   <tr>
                <td> '.__('Sign Up Button Background Color','xoousers').'</td>
                <td><input name="p_signup_bg_color" type="text" id="p_signup_bg_color_'.$package->package_id.'" value="'. $p_signup_bg_color.'" class="color-picker"  data-default-color="" /> 
               </td>
              </tr>
            
            </table>';
		  
		  
		  
		   $html .= '<h3>'.__('Package Limits: ','xoousers').'</h3>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
                <td width="24%"> '.__('Max Upload Photos: ','xoousers').'</td>
                <td width="76%"><input name="p_max_photos" type="text" id="p_max_photos_'.$package->package_id.'" value="'.$package->package_limit_photos.'"  /> 
          -  '.__(' 9999 for unlimited photos ','xoousers').'</td>
              </tr>
              <tr>
                <td> '.__('Max Galleries','xoousers').'</td>
                <td><input name="p_max_gallery" type="text" id="p_max_gallery_'.$package->package_id.'" value="'.$package->package_limit_galleries.'"  /> 
                -  '.__('9999 for unlimited galleries','xoousers').'</td>
              </tr>
			  
			   <tr>
                <td>'.__('Max Posts ','xoousers').'</td>
                <td><input name="p_max_posts" type="text" id="p_max_posts_'.$package->package_id.'" value="'.$package->package_limit_posts.'"  /> 
                -  '.__('9999 for unlimited posts ','xoousers').'</td>
              </tr>
			  
			  
            </table>
			
			<h3>'. __("Pay per Read:","xoousers").'</h3>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">

			  
			   <tr>
                <td>'. __("Allowed Posts to Read","xoousers").'</td>
                <td><input name="p_max_posts_read" type="text" id="p_max_posts_read_'.$package->package_id.'" value="'.$package->package_limit_posts_read.'"  /> 
                - '. __(" Post IDs separated by commas: 1,4,5","xoousers").'</td>
              </tr> 
			  
			  
            </table>
			
			  <h3>'. __("Credits/Points:","xoousers").'</h3>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">

			  
			   <tr>
                <td>'. __("Quantity","xoousers").'</td>
                <td><input name="p_credits" type="text" id="p_credits_'.$package->package_id.'" value="'.$package->package_credits.'"  /> 
                - </td>
              </tr> 
			  
			  
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
            </table>
         
         <p>
          <a href="#" class="button uultra-close-edit-package" >'. __("Cancel","xoousers").'</a>
           <a href="#" class="button-primary uultra-edit-package-confirm" data-package="'.$package->package_id.'">'.__('Confirm','xoousers').'</a>
        </p>';
		
		$html .='<div class="uultra-package-edit-noti-mess" id="package-edit-noti-mess-'.$package->package_id.'"></div>';
            
		
		}
				
		return  die($html);
		
		
	}
	
	public function get_packages_ajax()
	{
		global $wpdb,  $xoouserultra;
		
		$html = "";
		
		$currency_symbol =  $xoouserultra->get_option('paid_membership_symbol');
		
		
			
		$packages = $this->get_packages_private();
				
		if (!empty($packages)){
				
				
				
        
        
           $html .='<table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
				
				   <th>'. __("ID", "xoousers").'</th>
                    <th>'. __("Name", "xoousers").'</th>
                    <th>'. __("Description", "xoousers").'</th>
                    <th>'.__("Price", "xoousers").'</th>
                      <th>'. __("Period","xoousers").'</th>
                    <th>'. __("Type", "xoousers").'</th>
                  
                    <th>'. __("Action", "xoousers").'</th>
                </tr>
            </thead>
            
            <tbody>';
            
         
			foreach($packages as $package) 
			{
				
				if($package->package_time_period=="M"){$p =  __(" month(s)","xoousers");} 
			    if($package->package_time_period=="W"){$p = __(" week(s)","xoousers");}
				if($package->package_time_period=="D"){$p = __(" day(s)","xoousers");}
				if($package->package_time_period=="Y"){$p = __(" year(s)","xoousers");}  
			           

               $html .='  <tr>
			        <td>'.$package->package_id .'</td>
                    <td>'.$package->package_name .'</td>
                    <td>'.$package->package_desc .'</td>
                    <td>'. $currency_symbol.$package->package_amount  .'</td>
                    <td> '. __("every ","xoousers").$package->package_number_of_times .$p.'</td>
                     <td>'.$package->package_type.'</td>
					 
                   <td> <a href="#" class="button uultra-package-deactivate" data-package="'.$package->package_id.'">'. __("Delete","xoousers").'</a>  <a href="#" class="button-primary uultra-package-edit" data-package="'. $package->package_id.'">'. __("Edit","xoousers").'</a>
				  
				   
				   </td>
				   
				   <tr><td colspan="6"><div class="uultra-edit-package-admin" id="uu-edit-package-box-'. $package->package_id.'"></div></td></tr>
				   
				    
                </tr> ';
				
                
                
               
			}
					
					} else {
			
					$html .= '<p>'.__("There are no packages yet.","xoousers").'</p>';
				}

            $html .= '</tbody>
        </table>';
		
		
				
		return  die($html);
		
		
	}
	
	public function package_edit_confirm ()
	{
		global $wpdb,  $xoouserultra;
		
		$package_id = $_POST["p_id"];
			
		$package_name   = $_POST['p_name'];					
		$package_desc   =  $_POST['p_desc'];
		$package_type   =  $_POST['p_type'];
		$package_number_of_times   =  $_POST['p_every'];
		$package_time_period   =  $_POST['p_period'];
		$package_amount   =  $_POST['p_price'];
		
		$p_moderation   =  $_POST['p_moderation'];
		$p_max_photos   =  $_POST['p_max_photos'];
		$p_max_gallery   =  $_POST['p_max_gallery'];
		
		$p_max_posts   =  $_POST['p_max_posts'];		
		$p_max_posts_read   =  $_POST['p_max_posts_read'];
		$p_credits   =  $_POST['p_credits'];
		
		$p_price_color   =  $_POST['p_price_color']; //font color
		$p_price_bg_color   =  $_POST['p_price_bg_color'];
		
		 $p_signup_color = $_POST["p_signup_color"];
		 $p_signup_bg_color = $_POST["p_signup_bg_color"];
		 $p_role = $_POST["p_role"];
		  $p_custom_registration_form = $_POST["p_custom_registration_form"]; 
		 
		 
		
		//prepare customization array
		$package_customization = array();				
		$package_customization = array('p_price_color' =>$p_price_color, 'p_price_bg_color' =>$p_price_bg_color ,'p_signup_color' =>$p_signup_color, 'p_signup_bg_color' =>$p_signup_bg_color );
		$package_customization = serialize($package_customization);
				
		
		$query = "UPDATE " . $wpdb->prefix ."usersultra_packages SET package_name = '$package_name',package_desc = '$package_desc', package_type = '$package_type' , package_number_of_times = '$package_number_of_times' , package_time_period = '$package_time_period', package_amount = '$package_amount', package_approvation = '$p_moderation'  , package_limit_photos = '$p_max_photos' , package_limit_galleries = '$p_max_gallery'  , package_limit_posts = '$p_max_posts' , package_limit_posts_read = '$p_max_posts_read' , package_credits = '$p_credits' , package_customization = '$package_customization'   , package_role = '$p_role' , package_registration_form = '$p_custom_registration_form'  WHERE  `package_id` = '$package_id' ";						
		
		$wpdb->query( $query ); 
		
		echo "<div class='user-ultra-success uultra-notification'>".__("The Package has been updated", 'xoousers')."</div>";
		
		die();
		
		
		
	}
	
	public function package_add_new ()
	{
		global $wpdb,  $xoouserultra;
		
		$p_price_color   =  $_POST['p_price_color']; //font color
		$p_price_bg_color   =  $_POST['p_price_bg_color'];    	
		$p_signup_color = $_POST["p_signup_color"];
		$p_signup_bg_color = $_POST["p_signup_bg_color"];
		
		
		//prepare customization array
		$package_customization = array();				
		$package_customization = array('p_price_color' =>$p_price_color, 'p_price_bg_color' =>$p_price_bg_color, 'p_signup_color' =>$p_signup_color, 'p_signup_bg_color' =>$p_signup_bg_color );
		$package_customization = serialize($package_customization);
		
		
		$new_message = array(
						'package_id'        => NULL,
						'package_name'   => $_POST['p_name'],						
						'package_desc'   =>  $_POST['p_desc'],
						'package_type'   =>  $_POST['p_type'],
						'package_number_of_times'   =>  $_POST['p_every'],
						'package_time_period'   =>  $_POST['p_period'],
						'package_amount'   =>  $_POST['p_price'],						
						'package_approvation'   =>  $_POST['p_moderation'],
						'package_limit_photos'   =>  $_POST['p_max_photos'],
						'package_limit_galleries'   =>  $_POST['p_max_gallery'],
						
						'package_limit_posts'   =>  $_POST['p_max_posts'],
						'package_limit_posts_read'   =>  $_POST['p_max_posts_read'],
						'package_credits'   =>  $_POST['p_credits'],
						'package_customization'   =>  $package_customization,
						'package_role'   =>  $_POST['p_role'],
						'package_registration_form'   =>  $_POST['p_custom_registration_form']
						
						
						
					);	
		
		
		 $wpdb->insert( $wpdb->prefix . 'usersultra_packages', 
		 $new_message, array( '%d', '%s', '%s', '%s' , '%s', '%s', '%s' ,'%s', '%s', '%s' , '%s', '%s', '%s' , '%s' , '%s'  , '%s' ) ) ;	
		 
		 
		
	}
	
	
	
	
		

	
	

}
$key = "paypal";
$this->{$key} = new XooPaypalPayment();