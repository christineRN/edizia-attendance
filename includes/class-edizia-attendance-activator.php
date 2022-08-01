<?php

/**
 * Fired during plugin activation
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 * @author     Christine Larsen <christine@edizia.com>
 */
class Edizia_Attendance_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	
	 private $attendance_table;
	
	 public static function activate()
	 {
		global $wpdb;
		
		// get the name of the attendance table for the plugin
		$plugin = new Edizia_Attendance();
		$plugin_admin = new Edizia_Attendance_Admin($plugin->get_edizia_attendance(), $plugin->get_version());

		// This plugin only works if The Events Calendar plugin is installed and activated
		if (!is_plugin_active('the-events-calendar/the-events-calendar.php'))
		{
			// The Events Calendar is not active - display a notice and don't activate this plugin
			//$plugin_admin->required_plugin_not_active();
			// ***** for some reason, this is not working properly - it creates an error but not the error I've asked it to create *****
			return;
		}
		
		$attendance_table = $plugin_admin->get_attendance_table_name();
		
		// create a new table in the database, called $attendance_name
		
		$query = "CREATE TABLE IF NOT EXISTS `$attendance_table` (
			`id` int unsigned NOT NULL AUTO_INCREMENT,
			`eventID` int NOT NULL,
			`userID` int NOT NULL,
			`attended` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `Event_Attendance` (`eventID`,`userID`)
		) ENGINE=InnoDB AUTO_INCREMENT=403 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
		$wpdb->query($query);
		
		// tell the plugin admin class to do the activate actions
		$plugin_admin->attendance_activate_admin_actions();
	}
}