<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 * @author     Christine Larsen <christine@edizia.com>
 */
class Edizia_Attendance_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		// anything that needs to happen when the plugin is deactivated goes here
		
		$plugin = new Edizia_Attendance();
		$plugin_admin = new Edizia_Attendance_Admin($plugin->get_edizia_attendance(), $plugin->get_version());

		// call deactivate actions
		$plugin_admin->attendance_deactivate_admin_actions();
		
		// attendance table doesn't get deleted until the plugin is uninstalled
	}
}