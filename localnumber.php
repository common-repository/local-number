<?php

/**
 * Local Number for Wordpress
 *
 * A plugin for Wordpress to automagically show a phone number that is
 * geo-located to be near each visitor - so they are more likely to call you!
 *
 * @link              https://localnumber.app/
 * @since             1.0.0
 * @package           Localnumber
 *
 * @wordpress-plugin
 * Plugin Name:       Local Number
 * Plugin URI:        https://localnumber.app/integrate#wordpress
 * Description:       Automagically show a phone number that is geo-located to be near each visitor - so they are more likely to call you!
 * Version:           1.0.0
 * Author:            Local Number
 * Author URI:        https://localnumber.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       localnumber
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Localnumber_Plugin {

    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );

        // Add master script into document head
        add_action( 'wp_head', array( $this, 'setup_script' ) );

        // Add Shortcode
        add_shortcode( 'localnumber', array( $this, 'setup_shortcode' ) );

        // Do redirect-to-settings on first activation
        register_activation_hook(__FILE__, array( $this, 'setup_plugin_activate'));
        add_action('admin_init', array( $this, 'setup_plugin_redirect'));

    }

    public function setup_plugin_activate() {
        add_option('localnumber_do_activation_redirect', true);
    }

    public function setup_plugin_redirect() {
        if (get_option('localnumber_do_activation_redirect', false)) {
            delete_option('localnumber_do_activation_redirect');
            if(!isset($_GET['activate-multi'])){
                wp_redirect("options-general.php?page=localnumber");
            }
        }
    }

    public function setup_script(){
        // Add master script into document head
        $tag = get_option('localnumber_tag');
        if(strlen($tag) > 0){
            echo '<script async src="https://js.localnumber.app/v1/'.$tag.'.js"></script>';
        }
    }

    public function setup_shortcode() {
        // Add Shortcode
        return '<span class="replaceWithLocalNumber"></span>';
    }

    public function create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Local Number Settings';
    	$menu_title = 'Local Number';
    	$capability = 'manage_options';
    	$slug = 'localnumber';
    	$callback = array( $this, 'plugin_settings_page_content' );
    	$icon = 'dashicons-admin-plugins';
    	$position = 100;
        add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }

    public function plugin_settings_page_content() {?>
    	<div class="wrap">
    		<h2>Local Number for Wordpress</h2>
            <p>Welcome to Local Number for Wordpress. We let you show every visitor a phone number that is local to them.</p>
            <?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] && ( !count( get_settings_errors() ) )){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'localnumber' );
                    do_settings_sections( 'localnumber' );
                    submit_button();
                ?>
    		</form>
            <p>Once this is done you can make a Local Number appear anywhere by inserting the shortcode <strong>[localnumber]</strong> in your posts, pages or templates.</p>
            <p>To customize your local number settings, sign in at <a href="https://localnumber.app/signinup" target="_blank">localnumber.app</a>. Got questions? Simply <a href="mailto:info@localnumber.app">email us</a>.</p>
            <p>If you close your Local Number account then set up another you might need to update your magic tag - you can do that by returning here from 'Local Number' tab in the 'Settings' menu within Wordpress Admin.</p>
    	</div> <?php
    }
    
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

    public function setup_sections() {
        add_settings_section( 'our_first_section', '', array( $this, 'section_callback' ), 'localnumber' );
    }

    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'our_first_section':
    			echo 'Simply <a href="https://localnumber.app/integrate#wordpress" target="_blank">click here</a> to get your magic tag and then enter it in the box below, then click the <strong>Save Changes</strong> button.';
    			break;
    	}
    }

    public function setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'localnumber_tag',
        		'label' => 'My Magic Tag',
        		'section' => 'our_first_section',
        		'type' => 'text',
        		'placeholder' => 'e.g. Dw5sw6f',
        		'helper' => '',
        		'supplimental' => 'Tip: be sure to copy and paste it exactly as shown in your account.',
        	)
        );
    	foreach( $fields as $field ){

        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'localnumber', $field['section'], $field );
            register_setting( 'localnumber', $field['uid'], ['sanitize_callback'=>[$this, 'field_validate']]);
    	}
    }
    public function field_validate( $input ) {
        $old_option = get_option('localnumber_tag');
        if(strlen($input) < 4){
            $test = false;
        } else {
            $test = substr(md5(substr($input, 0, -1)), 0, 1);
        }
        if (($test == false) || ($test != substr($input, -1))) {
            $input = $old_option;
            add_settings_error('localnumber_tag','localnumber_tag_error','Incorrect magic tag! Please check and try again.','error');
        }
        return $input;
    }
    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] );

        if( ! $value ) {
            $value = $arguments['default'];
        }

        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }

        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper );
        }

        if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }

    }

}
new Localnumber_Plugin();
