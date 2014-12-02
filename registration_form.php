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
	
	
	
	if($_POST['payment_status']=='Completed')
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
		$tempuserid01 	= $_POST['custom'];

		// select value from wp_pro_temp_users table
		$selectdataquery = "SELECT * FROM ".PRO_TABLE_PREFIX."temp_users WHERE id='$tempuserid01'";
		$resulvalues = $wpdb->get_results($selectdataquery); 
		$tempuserid = $resulvalues[0]->id;	
		
			
		
		
		// insert value in wp_users table
		$now = date('Y-m-d H:i:s');
		$queryrun01 = $wpdb->query($wpdb->prepare('INSERT INTO '.$wpdb->prefix.'users 
			(user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name)  VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s )',	
			array($resulvalues[0]->username,$resulvalues[0]->password,$resulvalues[0]->username,$resulvalues[0]->email,"",$now,"","0",$resulvalues[0]->username)) );
				
					
		$uid = $wpdb->get_col('select *  from '.$wpdb->prefix.'users where user_login = "'.$resulvalues[0]->username.'"'); 
		$uid = $uid[0];
			
		
				// insert value in wp_usermeta table
				$insertmeta = "insert into ".$wpdb->prefix."usermeta (user_id,meta_key,meta_value)values
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
				$resultmeta = $wpdb->query($insertmeta)or die(mysql_error());
		
		

		//Delete entry from wp_pro_temp_users table.
		$wpdb->query("DELETE FROM ".PRO_TABLE_PREFIX."temp_users WHERE id = '$tempuserid01'");
			
			
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
		
		
		/* Check user is exist or not */
		$user_exist = $wpdb->query('SELECT * FROM '.$wpdb->prefix.'users WHERE user_login="'.$user_login.'"'); 
		$user_exist01 = $wpdb->query('SELECT * FROM '.PRO_TABLE_PREFIX.'users WHERE  username="'.$user_login.'"'); 
		
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
	
	
			
	$queryrun = $wpdb->query($wpdb->prepare("INSERT INTO ".PRO_TABLE_PREFIX."temp_users 
	(username,firstname,lastname,email,password) VALUES ( %s, %s, %s, %s, %s )",	
	array($user_login,$first_name,$last_name,$user_email,$user_pass)) );

	$query = "SELECT * FROM ".PRO_TABLE_PREFIX."temp_users WHERE username='$user_login'";
	$resulvalues = $wpdb->get_results($query); 
	$tempuserid = $resulvalues[0]->id;	
	
	$paypalquery = "SELECT * FROM ".PRO_TABLE_PREFIX."registration_detail";
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
				$data['item_name']			= $_POST['item_name'];  echo "<br>";
				$data['item_number'] 		= $_POST['item_number']; echo "<br>";
				$data['payment_status'] 	= $_POST['payment_status']; echo "<br>";
				$data['payment_amount'] 	= $_POST['mc_gross']; echo "<br>";
				$data['payment_currency']	= $_POST['mc_currency']; echo "<br>";
				$data['txn_id']			= $_POST['txn_id'];echo "<br>";
				$data['receiver_email'] 	= $_POST['receiver_email'];echo "<br>";
				$data['payer_email'] 		= $_POST['payer_email'];echo "<br>";
				$data['custom'] 			= $_POST['custom'];echo "<br>";
					
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

function form_validate()
{
	var e = 0;
	var userchk = isCheckUsername("user_login", "Please Enter User Name", "status")

	if(userchk=='decline')
	{
		e++;
	}
	if(isEmpty("first_name", "Please Enter First Name", "err_first_name"))
	{
		e++;
	}
	if(isEmpty("last_name", "Please Enter Last Number", "err_last_name"))
	{
		e++;
	}
	if(emailcheck("payer_email", "Please Enter Correct Email Id", "err_payer_email"))
	{
		e++;
	}
	if(isEmpty("user_pass", "Please Enter Your Password", "err_user_pass"))
	{
		e++;
	}
	if(isEmpty("con_pass", "Please Enter Your Confirm Password", "err_con_pass"))
	{
		e++;
	}
	if(passCheck("user_pass", "Your Password Not Match", "err_pass_match"))
	{
		e++;
	}
	



if(e > 0)
	{
		//alert("Please fill login details");
		return false;
	}
	else
	{
		return true;
	}

}

function isCheckUsername(e, t, n)
{
	var n = document.getElementById(n);
	var r = document.getElementById(e);
	var msg_length = n.innerHTML.length;


	if(r.value=='')
	{
		n.innerHTML = t;
		return 'decline';
	}
	else if(msg_length>0)
	{
		return 'decline';
	}
	else
	{
		n.innerHTML = "";
		r.focus();
		return 'accept';
	}
}


function emailcheck(e, t, n)
{
	var reg=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
	var r = document.getElementById(e);
	var n = document.getElementById(n);
	
	if(reg.test(r.value) == false)
	{	
		n.innerHTML = t;
		return true;	
	}
	else
	{	
		n.innerHTML = "";
		r.focus();
		return false
		
	}
	
}
function passCheck(e, t, n)
{
	var pass = document.getElementById(e).value;
	var co_pass = document.getElementById('con_pass').value;
	var n = document.getElementById(n);
	
	
	if(pass!='' && co_pass!='')
	{
		if(pass==co_pass)
		{
			n.innerHTML = "";
			return false;	
		}
		else
		{	
			n.innerHTML = t;		
			return true;		
		}
	}
	
	
}
function isEmpty(e, t, n)
{
		var r = document.getElementById(e);
		var n = document.getElementById(n);
		if(r.value.replace(/\s+$/, "") == "")
		{
			n.innerHTML = t;
			return true
		}
		else
		{
			n.innerHTML = "";
			return false
		}
}

    
</script>
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

 
  <form class="regi_form" action="" method="post" >
    <table width="100%" cellspacing="0" cellpadding="0" class="regtable">
      <tbody>
      <input type="hidden" name="cmd" value="_xclick" />
      <input type="hidden" name="no_note" value="1" />
      <input type="hidden" name="lc" value="UK" />
      <input type="hidden" name="currency_code" value="USD" />
      <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
    
      <tr>
        <td>Username<em>&nbsp;*</em>:</td>
        <td><input type="text" onkeyup="checkname(this.value)"  value="" id="user_login" name="user_login" /> <label id="status" ></label></td>
      </tr>
      <tr>
        <td>First Name<em>&nbsp;*</em>:</td>
        <td><input type="text" onkeyup="nospaces(this)" value="" id="first_name" name="first_name" /><label id="err_first_name" ></label></td>
      </tr>
      <tr>
        <td>Last Name<em>&nbsp;*</em>:</td>
        <td><input type="text" id="last_name" value="" name="last_name"><label id="err_last_name" ></label></td>
      </tr>
      <tr>
        <td>Email<em>&nbsp;*</em>:</td>
        <td><input type="text" value="" id="payer_email" name="payer_email"><label id="err_payer_email" ></label></td>
      </tr>
      <tr>
        <td>Password<em>&nbsp;*</em>:</td>
        <td><input type="password" value="" name="user_pass" id="user_pass"><label id="err_user_pass" ></label></td>
      </tr>
      <tr>
        <td>Confirm Password<em>&nbsp;*</em>:</td>
        <td><input type="password" value="" name="con_pass" id="con_pass"><label id="err_con_pass" ></label>
        <label id="err_pass_match" ></label></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" value="Submit" name="submit"  onclick="return form_validate();" /></td>
      </tr>
      </tbody>
      
    </table>
  </form>
</div>
