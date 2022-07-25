<?php

/**
 * Fired during plugin uninstall
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
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
	public static function uninstall()
	{
		global $wpdb;
		
		$plugin = new Edizia_Attendance();
		$plugin_admin = new Edizia_Attendance_Admin($plugin->get_edizia_attendance(), $plugin->get_version());

		// get the name of the attendance table for the plugin
		$attendance_table = $plugin_admin->get_attendance_table_name();
		
		// delete the table in the database called $attendance_name
		$query = "DROP TABLE `$attendance_table`";
		$wpdb->query($query);
	}
}