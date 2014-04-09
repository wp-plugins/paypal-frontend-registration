<div class="wrap">
  <?php  global $wpdb;	 ?>
	
   
   <?php if($_GET['user']=='exist')
   {
		echo '<h5>Please try another Username,This Username is already exist.</h5>';	   
} ?>
   
    
    
<?php 
if($_GET['return']=='true') // user successfully pay registratin charges.
{
 	
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix
		$req .= "&$key=$value";
	}
	
	if($data['payment_status']='Completed')
	{
		// assign posted variables to local variables
		$data['item_name']			= $_POST['item_name']; 
		$data['item_number'] 		= $_POST['item_number']; 
		$data['payment_status'] 	= $_POST['payment_status'];
		$data['payment_amount'] 	= $_POST['mc_gross'];
		$data['payment_currency']	= $_POST['mc_currency'];
		$data['txn_id']			= $_POST['txn_id'];
		$data['receiver_email'] 	= $_POST['receiver_email'];
		$data['payer_email'] 		= $_POST['payer_email'];
		$tempuserid01 = $data['custom'] 	= $_POST['custom'];echo "<br>";

		// select value from wp_pro_temp_users table
		$selectdataquery = "SELECT * FROM wp_pro_temp_users WHERE id='$tempuserid01'";
		$resulvalues = $wpdb->get_results($selectdataquery); 
		$tempuserid = $resulvalues[0]->id;	
				
		// insert value in wp_users table
		$insertquery='insert into wp_users (user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name) 
					values ("'.$resulvalues[0]->username.'","'.$resulvalues[0]->password.'","'.$resulvalues[0]->username.'","'.$resulvalues[0]->email.'","",NOW(),"","0","'.$resulvalues[0]->username.'")';
		$result=mysql_query($insertquery)or die(mysql_error());
		
		// select userid from wp_users table
		$selectuid='select *  from wp_users where user_login = "'.$resulvalues[0]->username.'"';
		$selectuidresult=mysql_query($selectuid)or die(mysql_error());
		$rowuid = mysql_fetch_array($selectuidresult);
		$uid = $rowuid["ID"];
				
				// insert value in wp_usermeta table
				$insertmeta = "insert into wp_usermeta (user_id,meta_key,meta_value)values
				('".$uid."','first_name','".$resulvalues[0]->firstname."'),
				('".$uid."','last_name','".$resulvalues[0]->lastname."'),
				('".$uid."','nickname','".$resulvalues[0]->username."'),
				('".$uid."','description',''),
				('".$uid."','rich_editing','true'),
				('".$uid."','comment_shortcuts','false'),
				('".$uid."','admin_color','fresh'),
				('".$uid."','use_ssl',0),
				('".$uid."','show_admin_bar_front','true'),
				('".$uid."','wp_capabilities','a:1:{s:10:\"subscriber\";b:1;}'),
				('".$uid."','wp_user_level',0)";
				$resultmeta = mysql_query($insertmeta)or die(mysql_error());
		
		

		//Delete entry from wp_pro_temp_users table.
		$wpdb->query("DELETE FROM wp_pro_temp_users WHERE id = '$tempuserid01'");
			
			
		echo '<h1>Your Registartion is Completed Successfully</h1>'; 
		
		
	}

}

 

?>




  <?php  if(isset($_POST["submit"]))
 	{ 
		$user_login=$_POST['user_login'];
		$first_name=$_POST["first_name"];
	 	$last_name=$_POST["last_name"];
	  	$user_email=$_POST['payer_email'];
	 	$user_pass=md5($_POST["user_pass"]);
		
		$user_exist = mysql_num_rows(mysql_query('SELECT * FROM wp_users WHERE user_login="'.$user_login.'"')); 
		$user_exist01 = mysql_num_rows(mysql_query('SELECT * FROM wp_pro_temp_users WHERE  username="'.$user_login.'"')); 
		
		$alwaysurl = get_permalink();
		if (strpos($alwaysurl, '?') !== false)
		{
		    $connectvar = '&';
		}
		else
		{
		    $connectvar = '?';
		}
			
			
		
		
	if($user_exist > 0) //if username exist in wp_user table
	{ 
		?>
	 <script type="text/javascript">
		 	window.location='<?php echo $alwaysurl.$connectvar."user=exist"; ?>';
	 </script>
  		<?php 
	}
	else if($user_exist01 > 0) //if username exist in wp_pro_temp_users table
	{ ?>
 	        <script type="text/javascript">
				window.location='<?php echo $alwaysurl.$connectvar."user=exist"; ?>';
		 	 </script>
			
 <?php }
	else
	{
		
	$queryrun = $wpdb->query($wpdb->prepare("INSERT INTO wp_pro_temp_users 
	(username,firstname,lastname,email,password) VALUES ( %s, %s, %s, %s, %s )",	
	array($user_login,$first_name,$last_name,$user_email,$user_pass)) );

	$query = "SELECT * FROM wp_pro_temp_users WHERE username='$user_login'";
	$resulvalues = $wpdb->get_results($query); 
	$tempuserid = $resulvalues[0]->id;	
	
	$paypalquery = "SELECT * FROM wp_pro_registration_detail";
	$paypalvalues = $wpdb->get_results($paypalquery); 	
	$sandboxenable =$paypalvalues[1]->value;


			// PayPal settings
		$alwaysurl = get_permalink();
		if (strpos($alwaysurl, '?') !== false)
		{
		    $connectvar = '&';
		}
		else
		{
		    $connectvar = '?';
		}
			
			//$declareurl = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
			//echo $alwaysurl.$connectvar."user=exist"; 
			
			$paypal_email = $paypalvalues[2]->value;
			$return_url = $alwaysurl.$connectvar."return=true";
			$cancel_url = $alwaysurl.$connectvar."&cancel";
			$notify_url = $alwaysurl.$connectvar."&notify";
			
			$item_name = 'Registration Charge';
			$item_amount = $paypalvalues[0]->value;
			
			// Check if paypal request or response
			if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])){
			
				// Firstly Append paypal account to querystring
				$querystring .= "?business=".urlencode($paypal_email)."&";	
				
				// Append amount& currency (£) to quersytring so it cannot be edited in html
				
				//The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
				$querystring .= "item_name=".urlencode($item_name)."&";
				$querystring .= "amount=".urlencode($item_amount)."&";
				
				//loop for posted values and append to querystring
				foreach($_POST as $key => $value){
					$value = urlencode(stripslashes($value));
					$querystring .= "$key=$value&";
				}
				
				$querystring.= "custom=$tempuserid&";
				// Append paypal return addresses
				$querystring .= "return=".urlencode(stripslashes($return_url))."&";
				$querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
				$querystring .= "notify_url=".urlencode($notify_url);
				
				// Append querystring with custom field
				//$querystring .= "&custom=".USERID;
				
				// Redirect to paypal IPN
				
				if($sandboxenable==1)
				{
					header('location:https://www.sandbox.paypal.com/cgi-bin/webscr'.$querystring);
				}
				else
				{
					header('location:https://www.paypal.com/cgi-bin/webscr'.$querystring);
				}
			
			
			
				exit();
			
			}else{
				
				// Response from Paypal
			
				// read the post from PayPal system and add 'cmd'
				$req = 'cmd=_notify-validate';
				foreach ($_POST as $key => $value) {
					$value = urlencode(stripslashes($value));
					$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix
					$req .= "&$key=$value";
				}
				
				// assign posted variables to local variables
				echo $data['item_name']			= $_POST['item_name'];  echo "<br>";
				echo $data['item_number'] 		= $_POST['item_number']; echo "<br>";
				echo $data['payment_status'] 	= $_POST['payment_status']; echo "<br>";
				echo $data['payment_amount'] 	= $_POST['mc_gross']; echo "<br>";
				echo $data['payment_currency']	= $_POST['mc_currency']; echo "<br>";
				echo $data['txn_id']				= $_POST['txn_id'];echo "<br>";
				echo $data['receiver_email'] 	= $_POST['receiver_email'];echo "<br>";
				echo $data['payer_email'] 		= $_POST['payer_email'];echo "<br>";
				echo $data['custom'] 			= $_POST['custom'];echo "<br>";
					
				// post back to PayPal system to validate
				$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
				
				if($sandboxenable==1)
				{
					$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);	
				}
				else
				{
					$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);	
				}
				
				
				if (!$fp) {
					// HTTP ERROR
				} else {	
			
					fputs ($fp, $header . $req);
					while (!feof($fp)) {
						$res = fgets ($fp, 1024);
						if (strcmp($res, "VERIFIED") == 0) {
						
							// Used for debugging
							//@mail("you@youremail.com", "PAYPAL DEBUGGING", "Verified Response<br />data = <pre>".print_r($post, true)."</pre>");
									
							// Validate payment (Check unique txnid & correct price)
							$valid_txnid = check_txnid($data['txn_id']);
							$valid_price = check_price($data['payment_amount'], $data['item_number']);
							// PAYMENT VALIDATED & VERIFIED!
							if($valid_txnid && $valid_price){				
								$orderid = updatePayments($data);		
								if($orderid){					
									// Payment has been made & successfully inserted into the Database								
								}else{								
									// Error inserting into DB
									// E-mail admin or alert user
								}
							}else{					
								// Payment made but data has been changed
								// E-mail admin or alert user
							}						
						
						}else if (strcmp ($res, "INVALID") == 0) {
						
							// PAYMENT INVALID & INVESTIGATE MANUALY! 
							// E-mail admin or alert user
							
							// Used for debugging
							//@mail("you@youremail.com", "PAYPAL DEBUGGING", "Invalid Response<br />data = <pre>".print_r($post, true)."</pre>");
						}		
					}		
				fclose ($fp);
				}	
			}
	}
		
		
 }
 
 ?>
<script type="text/javascript">

function nospaces(t)
{
	if(t.value.match(/\s/g))
	{
		alert('Sorry, you are not allowed to enter any spaces');
		t.value=t.value.replace(/\s/g,'');
	}
}

</script>
 <script type="text/javascript">
 function test()
{
	
	
	var user_login = document.getElementById("user_login").value;
	var first_name = document.getElementById("first_name").value;
	var last_name = document.getElementById("last_name").value;
	var payer_email = document.getElementById("payer_email").value;
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	var user_pass = document.getElementById("user_pass").value;
	
	
	if(user_login == '')
	{
		alert("Please Enter Username");	
		return false;
	}
	else if(first_name == '')
	{
		 alert("Please Enter Firstname");	
		 return false;
	}
	else if(last_name == '')
	{
		 alert("Please Enter Lastname");	
		 return false;
	}
	else if(payer_email == '')
	{
		 alert("Please Enter Email Address");	
		 return false;
	}
	else if(reg.test(payer_email) == false)
	{
		alert("Please Enter Valid Email");
		return false;	
	}
	else if(user_pass == '')
	{
		 alert("Please Enter Password");	
		 return false;
	}
	return true;
}
 </script>
  <form action="" method="post" >
    <table width="100%" cellspacing="0" cellpadding="0" class="regtable">
      <tbody>
      <input type="hidden" name="cmd" value="_xclick" />
      <input type="hidden" name="no_note" value="1" />
      <input type="hidden" name="lc" value="UK" />
      <input type="hidden" name="currency_code" value="USD" />
      <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
    
      <tr>
        <td>Username:</td>
        <td><input type="text" onkeyup="nospaces(this)" value="" id="user_login" name="user_login" /><p id="usernameerror" style="color:red;  display:none;">Please enter Username.</p></td>
      </tr>
      <tr>
        <td>First Name:</td>
        <td><input type="text" onkeyup="nospaces(this)" value="" id="first_name" name="first_name" /></td>
      </tr>
      <tr>
        <td>Last Name:</td>
        <td><input type="text" id="last_name" value="" name="last_name"></td>
      </tr>
      <tr>
        <td>Email:</td>
        <td><input type="text" value="" id="payer_email" name="payer_email"></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td><input type="password" value="" name="user_pass" id="user_pass"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" value="Submit" name="submit"  onclick="return test();" /></td>
      </tr>
      </tbody>
      
    </table>
  </form>
</div>
