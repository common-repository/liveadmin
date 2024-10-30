<?php
/*
Plugin Name: LiveAdmin
Plugin URI: http://www.liveadmin.net/
Description: LiveAdmin is an online customer support chat system, hosted based and ready to use. All you have to do is to signup at http://www.liveadmin.net and get a site key, then use it in configuration section of module.
Version: 1.1
Author: Farhad Malekpour
Author URI: http://www.liveadmin.net/
Text Domain: liveadmin

Copyright 2010  LiveAdmin

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class LiveAdmin {
	var $plugin_options = 'liveadmin_options';

	function LiveAdmin()
	{
		// Define the domain for translations
		load_plugin_textdomain(	'liveadmin', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		// Check installed Wordpress version.
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=') ) {
			$this->init_hooks();
		} else {
			$this->version_warning();
		}
	}

	/**
	* Initializes the hooks for the plugin
	*
	* @returns	Nothing
	*/
	function init_hooks()
	{
		add_action('admin_menu', array(&$this,'wp_admin'));
		add_shortcode('liveadmin', array(&$this,'liveadmin_shortcode'));
		global $wp_version;
		if ( version_compare($wp_version, '2.8', '>=') )
			add_action( 'widgets_init',  array(&$this,'load_widget') );
	}

	/**
	* Displays a warning when installed in an old Wordpress Version
	*
	* @returns	Nothing
	*/
	function version_warning()
	{
		echo '<div class="updated fade"><p><strong>'.__('LiveAdmin requires WordPress version 2.7 or later!', 'liveadmin').'</strong></p></div>';
	}

	/**
	* Register the Widget
	*
	*/
	function load_widget()
	{
		register_widget( 'liveadmin_Widget' );
	}

	/**
	* Create and register the LiveAdmin shortcode
	*
	*/
	function liveadmin_shortcode($atts)
	{
		return $this->generate_html();
	}

	/**
	* Generate the LiveAdmin button HTML code
	*
	*/
	function generate_html()
	{
		$lv_options = get_option($this->plugin_options);

		if(isset($lv_options['site_key']))
			$site_key = $lv_options['site_key'];
		else
			$site_key = 'L1ED164CV46B2D6M131A84';

		if(isset($lv_options['site_addr']))
			$site_addr = $lv_options['site_addr'];
		else
			$site_addr = 'http://client.liveadmin.net/liveadmin.php';

		if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on' && substr($site_addr,0,5)=='http:')
		{
			$site_addr = 'https:'.substr($site_addr,5);
		}

		$random_id = 'mod_id_wp_2_8_0_lv_1_1';

		$target_siteaddr = $site_addr.'?key='.$site_key.'&tag=liveadmin_'.$random_id;

		$code  = '<!-- LiveAdmin Module Begin -->';
		$code .= '<script type="text/javascript"> (function() { var lvs = document.createElement("script"); lvs.type = "text/javascript"; lvs.async = true; lvs.src = "'.$target_siteaddr.'"; (document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(lvs); })(); </script>';
		$code .= '<span id="liveadmin_'.$random_id.'"></span>';
		$code .= '<!-- LiveAdmin Module End -->';

		return $code;
	}

	/**
	* The Admin Page and all it's functions
	*
	*/
	function wp_admin()
	{
		if (function_exists('add_options_page'))
		{
			add_options_page( 'LiveAdmin Options', 'Live Admin', 10, __FILE__, array(&$this, 'options_page') );
		}
	}

	function admin_message($message)
	{
		if ( $message )
		{
			?>
			<div class="updated"><p><strong><?php echo $message; ?></strong></p></div>
			<?php
		}
	}

	function options_page() {
		// Update Options
		if (isset($_POST['Submit'])) {
			$lv_options['site_key'] = trim( $_POST['lv_site_key'] );
			$lv_options['site_addr'] = trim( $_POST['lv_site_addr'] );
			update_option($this->plugin_options, $lv_options);
			$this->admin_message( __( 'The LiveAdmin settings have been updated.', 'liveadmin' ) );
		}
?>
<div class=wrap>
	<h2>Live Admin</h2>

	<form method="post" action="">
	<?php wp_nonce_field('update-options'); ?>
	<?php
		$lv_options = get_option($this->plugin_options);
		if(!isset($lv_options['site_key']) || $lv_options['site_key']=='')
		{
			$lv_options['site_key'] = 'L1ED164CV46B2D6M131A84';
		}
		if(!isset($lv_options['site_addr']) || $lv_options['site_addr']=='')
		{
			$lv_options['site_addr'] = 'http://client.liveadmin.net/liveadmin.php';
		}
	?>
	<table class="form-table">
	<tr valign="top">
	<th scope="row"><label for="lv_site_key"><?php _e( 'Live Admin Site Key', 'liveadmin' ) ?></label></th>
	<td><input name="lv_site_key" type="text" id="lv_site_key" value="<?php echo $lv_options['site_key']; ?>" class="regular-text" /><span class="setting-description"><br/><?php _e( 'Your Live Admin site key, to get a key signup at ', 'liveadmin' ) ?><a href="http://www.liveadmin.net" target="_blank">http://www.liveadmin.net</a></span></td>
	</tr>

	<tr valign="top">
	<th scope="row"><label for="lv_site_addr"><?php _e( 'Live Admin Site Addess', 'liveadmin' ) ?></label></th>
	<td><input name="lv_site_addr" type="text" id="lv_site_addr" value="<?php echo $lv_options['site_addr']; ?>" class="regular-text" /><span class="setting-description"><br/><?php _e( 'Live Admin site address, if you are using the hosted version of LiveAdmin just leave it as is, otherwise please enter the full address to LiveAdmin Standalone installation, for example http://www.mysite.com/liveadmin/client.php', 'liveadmin' ) ?></span></td>
	</tr>

	</table>

	<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'liveadmin' ) ?>" />
	</p>
</div>
<?php
	}
}


/**
 * The Class for the Widget
 *
 */
if (class_exists('WP_Widget')) :
class LiveAdmin_Widget extends WP_Widget
{
	/**
	* Constructor
	*
	*/
	function LiveAdmin_Widget()
	{
		// Widget settings.
		$widget_ops = array ( 'classname' => 'widget_liveadmin', 'description' => 'LiveAdmin online customer support system' );

		// Widget control settings.
		$control_ops = array( 'id_base' => 'liveadmin' );

		// Create the Widget
		$this->WP_Widget( 'liveadmin', 'Live Admin', $widget_ops );
	}

	/**
	* Output the Widget
	*
	*/
	function widget( $args, $instance )
	{
		extract( $args );
		global $liveadmin;

		// Get the settings
		$title = apply_filters('widget_title', $instance['title'] );
		$text = $instance['text'];

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		if ( $text )
			echo wpautop( $text );
		echo  $liveadmin->generate_html();
		echo $after_widget;
	}

	/**
	  * Saves the widgets settings.
	  *
	  */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['text'] = $new_instance['text'];

		return $instance;
	}

	/**
	* The Form in the Widget Admin Screen
	*
	*/
	function form( $instance )
	{
		// Default Widget Settings
		$defaults = array( 'title' => __('Live Support', 'liveadmin'), 'text' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'liveadmin'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:', 'liveadmin'); ?>
			<textarea class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_attr($instance['text']); ?></textarea>
			</label>
		</p>

		<?php
	}
}
endif;

/**
 * Uninstall
 * Clean up the WP DB by deleting the options created by the plugin.
 *
 */
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'liveadmin_deinstall');

function liveadmin_deinstall() {
	delete_option('liveadmin_options');
	delete_option('widget_liveadmin');
}

// Start the Plugin
add_action( 'plugins_loaded', create_function( '', 'global $liveadmin; $liveadmin = new LiveAdmin();' ) );

?>