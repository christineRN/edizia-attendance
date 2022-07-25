<?php

/*
 
Plugin Name: Edizia Attendance
 
Plugin URI: 
 
Description: A plugin to track attendance of members at events created using The Events Calendar plugin.
 
Version: 1.0.0
 
Author: Edizia, LLC
 
Author URI: https://edizia.com/
 
License: GPLv2 or later
 
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
{
	die;
}

/**
 * Current plugin version.
 */
define( 'EDIZIA_ATTENDANCE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-edizia-attendance-activator.php
 */
function activate_edizia_attendance()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-edizia-attendance-activator.php';
	Edizia_Attendance_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-edizia-attendance-deactivator.php
 */
function deactivate_edizia_attendance()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-edizia-attendance-deactivator.php';
	Edizia_Attendance_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/class-edizia-attendance-uninstaller.php
 */
function uninstall_edizia_attendance()
{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-edizia-attendance-uninstaller.php';
	Edizia_Attendance_Uninstaller::uninstall();
}

register_activation_hook( __FILE__, 'activate_edizia_attendance' );
register_deactivation_hook( __FILE__, 'deactivate_edizia_attendance' );
register_uninstall_hook(__FILE__, 'uninstall_edizia_attendance');


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-edizia-attendance.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_edizia_attendance() {

	$plugin = new Edizia_Attendance();
	$plugin->run();

}
run_edizia_attendance();


?>