<?php
/*
Plugin Name: onWebChat Live Chat
Plugin URI: https://www.onwebchat.com/wp_plugin.php
Description: onWebChat is a live chat system, that helps you communicate with your website's visitors.
Author: onWebChat
Version: 2.0.0
Author URI: https://www.onwebchat.com
*/


/* ---------------------------------------------------- *
 * Create Settings Page
 * ---------------------------------------------------- */

// get logo url
define('ONWEBCHAT_SMALL_LOGO', plugins_url( 'images/onwebchat-logo.png' , __FILE__ ));
define('ONWEBCHAT_SERVER_URL','https://www.onwebchat.com/get-chatid.php');

// add styles
add_action( 'admin_enqueue_scripts', 'register_plugin_styles' );

// call function on hook admin_menu
add_action('admin_menu','onwebchat_setup_menu');

// create login error notice
add_action( 'admin_notices', 'onwebchat_login_error' );

// define the settings
add_action('admin_init','onwebchat_register_setttings');


/* ---------------------------------------------------- *
 * Display onWebChat on footer
 * ---------------------------------------------------- */

// add widget plugin to the footer
add_action('wp_footer', 'onwebchat_add_script_at_footer');


/* ----------------------------------------------------- *
 * Admin Page registration
 * ----------------------------------------------------- */
	
function onwebchat_setup_menu() {
	add_menu_page( 'onWebChat Settings', 'onWebChat', 'administrator', 'onwebchat_settings', 'onwebchat_init_menu_page', ONWEBCHAT_SMALL_LOGO);
}


/* ------------------------------------------------------ *
 * Admin Page Display Function
 * ------------------------------------------------------ */

function onwebchat_init_menu_page() {
	
	$options = get_option('onwebchat_plugin_option');
	$chatId = $options['text_string'];

	$isConnected = false;
	if($chatId !="") {
		$isConnected = true;
	}
	
	
	/*****************************************************************
	 * Ask server for chatId
	 *****************************************************************/
	if ( isset( $_POST["action"] ) && $_POST["action"] == "login" ) {
		
		//retrive again chatId *******
		$options = get_option('onwebchat_plugin_option');
		$chatId = $options['text_string'];
		//****************************
		
		// get username, password and chatId
 		$userName = $_POST["onWebChatUser"];
		$userPass = $_POST["onWebChatPass"];
		$chatId = $_POST["chatId"];
		$isSecondPage = $_POST["isSecondPage"];
	
		// if user give chatId save it directly
		if($chatId != '') {
			
			//----------- update chatId option ------------
			//Get entire array
			$my_options = get_option('onwebchat_plugin_option');
			
			//Alter the options array appropriately
			$my_options['text_string'] = $chatId;
			
			//Update entire array
			update_option('onwebchat_plugin_option', $my_options);
			//-----------------------------------------------
			
			// remove user email from db
			if(!$isSecondPage)
				update_option( 'onwebchat_plugin_option_user', '');
			
			// move to next page
			print('<script>window.location.href="admin.php?page=onwebchat_settings"</script>');
		}
		
		// else ask server 
		else {
			
			// save username at options
			update_option( 'onwebchat_plugin_option_user', $userName );
			
			// ask server for chatId
			$response = wp_remote_post(ONWEBCHAT_SERVER_URL, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array( 'email' => $userName, 'pass' => $userPass ),
				'cookies' => array()
				)
			);
			
			if ( is_wp_error( $response ) ) {
			   $error_message = $response->get_error_message();
			} else {
		
				// If no chatId returned
				if($response['body'] == '-1') {
					// display an error for wrong username or password
					onwebchat_login_error(true);
				}
				
				// If we have the chatId
				else {
					//----------- update chatId option ------------
					//Get entire array
					$my_options = get_option('onwebchat_plugin_option');
					
					//Alter the options array appropriately
					$my_options['text_string'] = $response['body'];
					
					//Update entire array
					update_option('onwebchat_plugin_option', $my_options);
					//--------------------------------------------
					
					// move to next page
					print('<script>window.location.href="admin.php?page=onwebchat_settings"</script>');
				}
			}	
		}
		
		// save hideCheckBox
		update_option('onwebchat_plugin_option_hide', $_POST['onwebchat_plugin_option_hide']);
	 }
	//********************************************************************
	
	
	/*********************************************************************
	 * Disconnect account 
	 *********************************************************************/
	if( isset($_GET["action"]) && $_GET["action"] == "deactivate" && $isConnected) {
		$isConnected = false;
		
		//Get entire array
		$my_options = get_option('onwebchat_plugin_option');
		
		//Alter the options array appropriately
		$my_options['text_string'] = '';
		
		//Update entire array
		update_option('onwebchat_plugin_option', $my_options);
	
		$chatIdOption = get_option('onwebchat_plugin_option');
		$chatId = $chatIdOption['text_string'];
		
		//redirects
		print('<script>window.location.href="admin.php?page=onwebchat_settings"</script>');
		
	}	
	//***********************************************************************
	
	
	
?>
<div>
	<h2>onWebChat Settings</h2>
</br>
	<form action="admin.php?page=onwebchat_settings" method="post">
		<input type="hidden" name="action" value="login">
		<?php
	
			// Login Page (1st page)
			if($isConnected != true) {

				//get chatId from db
				$chatId = get_option( 'onwebchat_plugin_option' );
				$chatId = $chatId['text_string'];
			?>
				
				<h3 class="header-1">Connect with your onWebChat account</h3>
				
				<div class="username-div">
					<strong>Email: </strong><input class="username-text-field" type="text" name="onWebChatUser" value="<?php echo get_option( 'onwebchat_plugin_option_user' ); ?>"/>
				</div>
				<div class="password-div">
					<strong>Password: </strong><input class="password-text-field" type="password" name="onWebChatPass" value="<?php echo get_option( 'onWebChatPass' ); ?>"/>
				</div>
				
				<h3 class="header-2">Or paste your onWebChat Chat Id</h3>
				<div class="chatid-div">
					<strong>Chat Id:</strong> <input class="chatid-text-field" type="text" name="chatId" value="<?php echo $chatId; ?>"/>
				</div>

				<div class="new-account-link">
					<strong>*</strong> If you don't have an account on onWebChat live chat service, you should create one <a href="https://www.onwebchat.com/signup.php" target="_blank">here</a>
				</div>
			<?php
			}
			
			// Deactivate Page (2nd page)
			else {
				$options = get_option('onwebchat_plugin_option_user');
				
					// display user email as account identification
					if($options!=''){
						$html = '<h3 class="header-1-p2">Activated for onWebChat account: </h3>';
						$html .= "<strong class='account-id'>$options</strong> ";
					}
					
					// display chatId as account identification
					else {
						$chatId = get_option( 'onwebchat_plugin_option' );
						$chatId = $chatId['text_string'];
						$html = '<h3 class="header-1-p2">Activated for onWebChat Chat Id: </h3>';
						$html .= "<strong class='account-id'>$chatId</strong> ";
					}
					$html .= ' <a href="admin.php?page=onwebchat_settings&amp;action=deactivate">Deactivate</a>';
					$html .= ' <div> To connect on onWebChat operator console click <a target="_blank" href="https://www.onwebchat.com/login.php">here</a> </div>';
				echo $html;
	
				$checkBoxValue = checked(1, get_option('onwebchat_plugin_option_hide'), false);
				
				$options = get_option('onwebchat_plugin_option_hide');
				
				//get chatId from db
				$chatId = get_option( 'onwebchat_plugin_option' );
				$chatId = $chatId['text_string'];
				
				?>
				<div class="hide-div">
				<strong>Hide Chat Widget:</strong> <input type="checkbox" id="plugin_text_checkbox" name="onwebchat_plugin_option_hide" value="1" <?php checked( $options, 1 ); ?> />
				</div>
				
				<!-- hiden fields -->
				<input class="chatid-text-field-hide" type="text" name="chatId" value="<?php echo $chatId; ?>"/>
				<input class="chatid-text-field-hide" type="text" name="onWebChatUser" value="<?php echo get_option( 'onwebchat_plugin_option_user' ); ?>"/>
				<input class="chatid-text-field-hide" type="text" name="isSecondPage" value="1"/>
				<?php
				
			}	
		
		// Display the Save Button
		$html = '<input class="button button-primary" type="submit" value="Save Changes"/>';
		echo $html;
		?>
	</form>
</div>
<?php
}


/* ----------------------------------------------------- *
 * Admin Page Styles Registration
 * ----------------------------------------------------- */

function register_plugin_styles() {
	wp_register_style( 'onwebchat', plugins_url( 'onwebchat/css/onwebchat.css' ) );
	wp_enqueue_style( 'onwebchat' );
}


/* ----------------------------------------------------- *
 * Tabs Settings Registration  
 * ----------------------------------------------------- */

function onwebchat_register_setttings() {
	
	/******************** Account Tab ************************/
	//* register the Chat Id
	register_setting( 'onwebchat_plugin_option', 'onwebchat_plugin_option');
	
	//* register the username
 	register_setting( 'onwebchat_plugin_option', 'onwebchat_plugin_option_user');	
	
	//* register the hide (checkbox)
 	register_setting( 'onwebchat_plugin_option_checkbox', 'onwebchat_plugin_option_hide');
}


/* ----------------------------------------------------- *
 * onWebChat Display Function
 * ----------------------------------------------------- */

// print chat widget code
function onwebchat_add_script_at_footer() {

	$options = get_option('onwebchat_plugin_option');
	$chatId = $options['text_string'];
	
	$hideWidget = get_option('onwebchat_plugin_option_hide');
	
    $widgetCode = "<script type='text/javascript'>
    var onWebChat={ar:[], set: function(a,b){if (typeof onWebChat_==='undefined'){this.ar.
    push([a,b]);}else{onWebChat_.set(a,b);}},get:function(a){return(onWebChat_.get(a));},w
    :(function(){ var ga=document.createElement('script'); ga.type = 'text/javascript';ga.
    async=1;ga.src='//www.onwebchat.com/clientchat/$chatId';
    var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})()}
    </script>";
    
	$apiCode = get_option('onwebchat_plugin_option_api');

	$widgetCode .= $apiCode;
		
	
    //check if user is logged if yes get username, email (all users registered have them)
    if ( is_user_logged_in() ) { 

    	global $current_user;
    	get_currentuserinfo();

	    // get user name
	    $onwebchat_user = $current_user->user_login;

	    // get user email
	    $onwebchat_email = $current_user->user_email;

	    //add api info
    	$widgetCode = $widgetCode."<script type='text/javascript'>onWebChat.set('name','$onwebchat_user');onWebChat.set('email','$onwebchat_email'); </script>";
   	}
	
	if(!$hideWidget) {
		echo $widgetCode;
	}   
}

/************************************************************************
 * display error function
 ***********************************************************************/
function onwebchat_login_error($contition = false) {
    if($contition) {
	?>
    <div class="error">
        <p>Wrong Username or Password!</p>
    </div>
    <?php
	}
	else {
		//display nothing
	}
}

?>