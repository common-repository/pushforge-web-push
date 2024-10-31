<?php
/*
Plugin Name: Pushforge Web Push
Plugin URI:  https://wordpress.org/plugins/pushforge-web-push
Description: Integrate pushforge.com web push in you wordpress site
Version:     1.1.2
Author:      Pushforge.com
Author URI:  https://pushforge.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pushforge Web Push is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Pushforge Web Push is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Pushforge Web Push. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function pushforge_install () {
	$version='1.1.2';
	add_option( 'pushforge_userkey', '', '', 'no');
	add_option( 'pushforge_apikey', '', '', 'no');
	add_option( 'pushforge_title_length', '100', '', 'no');
	add_option( 'pushforge_message_length', '150', '', 'no');
	add_option( 'pushforge_ttl', '3600', '', 'no');
	add_option( 'pushforge_replace', 'No', '', 'no');
	add_option( 'pushforge_version', $version, '', 'no');
	add_option( 'pushforge_use_button', 'Yes', '', 'no');
	add_option( 'pushforge_keys_validate', 'No', '', 'no');
}

register_activation_hook( __FILE__, 'pushforge_install' );

if (is_admin()) {
	add_action( 'admin_menu', 'pushforge_web_push_menu' );
}
function pushforge_web_push_menu() {
	add_options_page( 'Pushforge Web Push Options', 'Pushforge Web Push', 'manage_options', 'pushforge-options', 'pushforge_web_push_options' );
}


function pushforge_web_push_options() {
    // variables for the field and option names 
    $pushforge_apikey = 'pushforge_apikey';
	$pushforge_userkey = 'pushforge_userkey';
	$pushforge_title_length = 'pushforge_title_length';
	$pushforge_message_length = 'pushforge_message_length';
	$pushforge_ttl = 'pushforge_ttl';
	$pushforge_replace = 'pushforge_replace';
	$hidden_field_name = 'pushforge_hidden';
	$pushforge_use_button = 'pushforge_use_button';
	// See if the user has posted us some information

    if(isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y' && is_admin()) {
		check_admin_referer( 'update_pushforge_options' );
        // Read their posted value
		
		if (!isset($_POST[$pushforge_replace])){
			update_option( $pushforge_replace,'No' );
		}else{
			if ($_POST[$pushforge_replace]=='on'){
				//$replace_val = 'Yes';
				update_option( $pushforge_replace, "Yes" );
			}
		}
		
		if (!isset($_POST[$pushforge_use_button])){
			update_option( $pushforge_use_button,'No' );
		}else{
			if ($_POST[$pushforge_use_button]=='on'){
				$use_button_val = $_POST[$pushforge_use_button];
				update_option( $pushforge_use_button,'Yes');
			}
		}

		$apikey_val = sanitize_key($_POST[$pushforge_apikey]);
		$userkey_val = sanitize_key($_POST[$pushforge_userkey]);
		$args = array(
			'method'	=> 'POST',
			'blocking'  => true,
			'timeout'	=> 45,
			'body'		=> 'apikey='.$apikey_val.'&user_key='.$userkey_val,
		);
		
		$url='https://pushforge.com/api/key_check.php';
		$response = wp_remote_post( $url, $args );

		$title_length_val = intval($_POST[$pushforge_title_length]);
		$message_length_val = intval($_POST[$pushforge_message_length]);
		$ttl_val = intval($_POST[$pushforge_ttl]);

		update_option($pushforge_apikey, $apikey_val);
		update_option($pushforge_userkey, $userkey_val);
		update_option($pushforge_title_length, $title_length_val);
		update_option($pushforge_message_length, $message_length_val);
		update_option($pushforge_ttl, $ttl_val);
		
		if ($response['body']=="OK"){
			update_option('pushforge_keys_validate','Yes');
			?>
			<div class="updated"><p><strong><?php _e('Settings saved.', 'pushforge-web-push'); ?></strong></p></div>
			<?php
		}else{
			update_option('pushforge_keys_validate','No');
			?>
			<div class="notice notice-error"><p><strong><?php _e('Wrong Api or User key. Pushing not enabled', 'pushforge-web-push' ); ?></strong></p></div>
			<?php
		}
    }

    echo '<div class="wrap">';
    echo "<h2>" . __( 'Pushforge Web Push Settings', 'pushforge-web-push' ) . "</h2>";

    // Get new option values from database
    $apikey_val = get_option( $pushforge_apikey );
	$userkey_val = get_option( $pushforge_userkey );
	$title_length_val = get_option( $pushforge_title_length );
	$message_length_val = get_option( $pushforge_message_length );
	$ttl_val = get_option( $pushforge_ttl );
	$replace_val = get_option( $pushforge_replace );
	$use_button_val = get_option( $pushforge_use_button );



?>
<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<?php wp_nonce_field('update_pushforge_options'); ?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php _e("Pushforge API key:", 'pushforge-web-push' ); ?></th>
			<td><input type="text" name="<?php echo $pushforge_apikey; ?>" value="<?php echo $apikey_val; ?>" size="64"  minlength="66" maxlength="66"></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Pushforge USER key:", 'pushforge-web-push' ); ?></th>
			<td><input type="text" name="<?php echo $pushforge_userkey; ?>" value="<?php echo $userkey_val; ?>" size="32" minlength="32" maxlength="32"></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Notification MAX title length:", 'pushforge-web-push' ); ?></th>
			<td><input type="number" name="<?php echo $pushforge_title_length; ?>" value="<?php echo $title_length_val; ?>" min="1" max="100"></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Notification MAX message length:", 'pushforge-web-push' ); ?></th>
			<td><input type="number" name="<?php echo $pushforge_message_length; ?>" value="<?php echo $message_length_val; ?>" min="1" max="150"></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Message Time to Live:", 'pushforge-web-push' ); ?></th>
			<td><input type="number" name="<?php echo $pushforge_ttl; ?>" value="<?php echo $ttl_val; ?>" min="0" max="2419200"></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Replace previous push message:", 'pushforge-web-push' ); ?></th>
			<td><input type="checkbox" name="<?php echo $pushforge_replace;?>"<?php if(($replace_val=="Yes") || (isset($_POST[$pushforge_replace]) && $_POST[$pushforge_replace]=='on')){echo ' checked="checked"';}else{}; ?>></td>
		</tr>
		<tr>
			<th scope="row"><?php _e("Use Pushforge's subscribe button:", 'pushforge-web-push' ); ?></th>
			<td><input type="checkbox" name="<?php echo $pushforge_use_button;?>"<?php if(($use_button_val=="Yes") || (isset($_POST[$pushforge_use_button]) && $_POST[$pushforge_use_button]=='on')){echo ' checked="checked"';}else{}; ?>></td>
		</tr>
	</tbody>
</table>
<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>
</div>

<?php
}
add_action('save_post', 'send_pushforge_push', 10 , 2);

function send_pushforge_push($post_ID, $post) {
	if (isset($_POST['_pushforge_custom_field']) && $_POST['_pushforge_custom_field']==1){
		$api_key=get_option('pushforge_apikey');
		$pushforge_title_length=get_option('pushforge_title_length');
		$pushforge_message_length=get_option('pushforge_message_length');
		$pushforge_ttl=get_option('pushforge_ttl');
		$post_url = get_permalink($post_id);
		$pushforge_replace=get_option('pushforge_replace');
		$args = array(
			'method'	=> 'POST',
			'blocking'  => false,
			'timeout'	=> 45,
			'body'		=> 'key='.$api_key.'&title='.strip_tags(substr($post->post_title,0,$pushforge_title_length)).'&message='.strip_tags(substr( $post->post_content,0,$pushforge_message_length)).'&action='.$post_url.'&ttl='.$pushforge_ttl.'&type=global&replace='.$pushforge_replace,
			);
		$url='https://pushforge.com/api/';
		//$url='https://pushforge.com/api/key='.$api_key.'&title='.substr(strip_tags( $post->post_title),0,$pushforge_title_length).'&message='.substr(strip_tags( $post->post_content),0,$pushforge_message_length).'&action='.$post_url.'&ttl='.$pushforge_ttl.'&type=global&replace='.$pushforge_replace;
		$response = wp_remote_post( $url, $args );
		unset($_POST['_pushforge_custom_field']);
	}
}


add_action('post_submitbox_misc_actions', 'pushforge_push_check');

function pushforge_push_check(){
	$key_validate = get_option('pushforge_keys_validate');
	if($key_validate=="Yes"){
		$post_id = get_the_ID();
		if (get_post_type($post_id) != 'post') {
			return;
		}
		$value = get_post_meta($post_id, '_pushforge_custom_field', true);
		wp_nonce_field('my_pushforge_nonce_'.$post_id, 'my_pushforge_nonce');
		?>
		<div class="misc-pub-section misc-pub-section-last">
			<label><input type="checkbox" value="1" <?php checked($value, true, true); ?> name="_pushforge_custom_field" /><?php _e('Push this post via Pushforge', 'pmg'); ?></label>
		</div>
		<?php
	}
}


$use_button_val = get_option('pushforge_use_button');
$key_validate = get_option('pushforge_keys_validate');

if ($use_button_val=="Yes" && $key_validate=="Yes"){
	//Add header's code
	add_action('wp_head', 'add_pushforge_button');
	function add_pushforge_button() {
		wp_enqueue_script( 'font-awesome-style', 'https://use.fontawesome.com/00eecbe114.js');
		wp_enqueue_style( 'pushforge_button-style', 'https://pushforge.com/static/'.get_option('pushforge_userkey').'.css' );
		wp_enqueue_script( 'pushforge_button-script', 'https://pushforge.com/static/'.get_option('pushforge_userkey').'.js');
	}
}
