<?php
/*
Plugin Name: OnWebChat Live Chat
Plugin URI: https://www.onwebchat.com/wp_plugin.php
Description: OnWebChat is a live chat system, that helps you communicate with your website's visitors.
Author: OnWebChat
Version: 1.0
Author URI: https://www.onwebchat.com
*/

// get logo url
define('ONWEBCHAT_SMALL_LOGO', plugins_url( 'images/onwebchat-logo.png' , __FILE__ ));

// call function on hook admin_menu
add_action('admin_menu','onwebchat_setup_menu');

// define the settings
add_action('admin_init','onwebchat_register_setttings');

// add widget plugin to the footer
add_action('wp_footer', 'onwebchat_add_script_at_footer');

// add menu page on admin menu with function onwebchat_init_menu_page()
function onwebchat_setup_menu() {
	add_menu_page( 'OnWebChat Settings', 'OnWebChat', 'administrator', 'onwebchat_settings', 'onwebchat_init_menu_page', ONWEBCHAT_SMALL_LOGO);
}

// display admin option page
function onwebchat_init_menu_page() {
?>
<div>
	<h2>OnWebChat Settings Page</h2>
</br>
	<form action="options.php" method="post">
		<?php settings_fields('onwebchat_plugin_option'); ?>
		<!-- prints all settings inputs we need -->
		<?php do_settings_sections('onwebchat_plugin'); ?>
		<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
</div>
<?php
}

// register the settings to store data
function onwebchat_register_setttings() {
	register_setting( 'onwebchat_plugin_option', 'onwebchat_plugin_option', 'plugin_options_validate' );
	//* plugin
	add_settings_section('plugin_main', 'Install Chat Widget', 'onwebchat_section_text', 'onwebchat_plugin');	
	//* plugin
	add_settings_field('plugin_text_string', 'Chat Id:', 'onwebchat_setting_field_text', 'onwebchat_plugin', 'plugin_main');
}

// prints the text of sections
function onwebchat_section_text() {
	echo '<p>To install OnWebChat on your Wordpress site please insert the Chat Id to the following field.</p>'.
	'<p>You will find Chat Id in settings-page of OnWebChat admin, <a href="https://www.onwebchat.com/login.php" target="_blank">OnWebChat - Admin</a> </p>';
}

// print the text of section field
function onwebchat_setting_field_text() {
	$options = get_option('onwebchat_plugin_option');
	echo "<input id='plugin_text_string' name='onwebchat_plugin_option[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

// validate data input
function plugin_options_validate($input) {
	$newinput['text_string'] = trim($input['text_string']);

	if(!preg_match('/^[a-f0-9\\/]{35,50}$/i', $newinput['text_string'])) {
        $newinput['text_string'] = '';
    }

	return $newinput;
}

// print chat widget code
function onwebchat_add_script_at_footer() {

	$options = get_option('onwebchat_plugin_option');
	$chatId = $options['text_string'];
    
    $widgetCode = "<script type='text/javascript'>
    var onWebChat={ar:[], set: function(a,b){if (typeof onWebChat_==='undefined'){this.ar.
    push([a,b]);}else{onWebChat_.set(a,b);}},get:function(a){return(onWebChat_.get(a));},w
    :(function(){ var ga=document.createElement('script'); ga.type = 'text/javascript';ga.
    async=1;ga.src='//www.onwebchat.com/clientchat/$chatId';
    var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})()}
    </script>";
    

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
    
    echo $widgetCode;
}
?>