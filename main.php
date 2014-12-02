<?php
/*
Plugin Name: Paypal Registration
Plugin URI: http://steponwebstudio.com/
Description: Wordpress registration using paypal Plugin
Version: 1.1
Author: Prakash
Author URI: http://steponwebstudio.com/
License: GPL
*/



$siteurl = get_option('siteurl');
define('PRO_FOLDER', dirname(plugin_basename(__FILE__)));
define('PRO_URL', $siteurl.'/wp-content/plugins/' . PRO_FOLDER);
define('PRO_FILE_PATH', dirname(__FILE__));
define('PRO_DIR_NAME', basename(PRO_FILE_PATH));
// this is the table prefix
global $wpdb;
$pro_table_prefix=$wpdb->prefix.'pro_';
define('PRO_TABLE_PREFIX', $pro_table_prefix);

register_activation_hook(__FILE__,'pro_install');
register_deactivation_hook(__FILE__ , 'pro_uninstall' );


wp_enqueue_script('stripe_frontend', plugins_url( '/js/check.js' , __FILE__ ) , array( 'jquery' ));
// including ajax script in the plugin Myajax.ajaxurl
wp_localize_script( 'stripe_frontend', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));



function pro_install()
{
	global $wpdb;
    $table1 = PRO_TABLE_PREFIX."temp_users";
    $structure1 = "CREATE TABLE $table1 (
    	id int(11) NOT NULL AUTO_INCREMENT,
  		username varchar(225) NOT NULL,
  		firstname varchar(225) NOT NULL,
  		lastname varchar(225) NOT NULL,
  		email varchar(225) NOT NULL,
  		password varchar(225) NOT NULL,
  		PRIMARY KEY (`id`)
    );";
    $wpdb->query($structure1);
	  // Populate table
	  
	$table2 = PRO_TABLE_PREFIX."registration_detail";
    $structure2 = "CREATE TABLE $table2 (
    		id int(11) NOT NULL AUTO_INCREMENT,
  			metaname varchar(255) NOT NULL,
 		 	value varchar(225) NOT NULL,
			PRIMARY KEY (`id`)
    );";
    $wpdb->query($structure2);
	
	
	 $wpdb->query("INSERT INTO $table2 (id,metaname, value)
        VALUES (1,'registrationcharge', 5)");
		
	$wpdb->query("INSERT INTO $table2 (id,metaname, value)
        VALUES (2,'sandbox', 1)");
	
	$wpdb->query("INSERT INTO $table2 (id,metaname, value)
        VALUES (3,'email', 'example.xyz@test.com')");
		
		  
	  
	  
}
function pro_uninstall()
{
    global $wpdb;
    $table1 = PRO_TABLE_PREFIX."temp_users";
    $structure1 = "drop table if exists $table1";
    $wpdb->query($structure1);  
	
	$table2 = PRO_TABLE_PREFIX."registration_detail";
    $structure2 = "drop table if exists $table2";
    $wpdb->query($structure2);  
}


add_action('admin_menu','pro_admin_menu');

function pro_admin_menu() { 
	add_menu_page(
		"Paypal Register",
		"Paypal Register",
		8,
		__FILE__,
		"pro_admin_menu_list",
		PRO_URL."/images/prakash.png"
	); 
	/*add_submenu_page(__FILE__,'Account Details','Account Details','8','list-charge','pro_admin_list_site');*/
}

function pro_admin_menu_list()
{
	 include 'register_amount.php';
}


// function for the site listing
function pro_admin_list_site()
{
	 include 'account_detail.php';
}

add_action('init', array('pra_stripe', 'init'));
class pra_stripe {
	function init() { 
	 wp_enqueue_style( 'paypal_css', plugins_url('/css/paypal.css', __FILE__ )); 

}}

//Add ShortCode for "front end listing"
//Short Code [registartion_form]

add_shortcode("registartion_form","registartion_form_shortcode");
 function registartion_form_shortcode($atts) 
{ 
	  include 'registration_form.php';
}

function post_word_count(){
		$user_login = $_POST['user_login'];
		global $wpdb;
		
		$rowcount_user = sizeof($wpdb->get_col('SELECT * FROM '.$wpdb->prefix.'users WHERE user_login="'.$user_login.'"'));
		$rowcount_temp_user = sizeof($wpdb->get_col('SELECT * FROM '.PRO_TABLE_PREFIX.'temp_users WHERE username="'.$user_login.'"'));
		
	
		$namesize = strlen($user_login);
		if($namesize<4)
		{
			echo 'Please enter username more than 3 charecters';
		}
		else if($rowcount_user > 0 ||  $rowcount_temp_user > 0) //if username exist in wp_user table
		{
			echo 'Try another,this Username is already exist';
		}
		
		die();
		return true;
}
add_action('wp_ajax_post_word_count', 'post_word_count');
add_action('wp_ajax_nopriv_post_word_count', 'post_word_count');


?>