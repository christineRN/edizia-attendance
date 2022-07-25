<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/includes
 * @author     Christine Larsen <christine@edizia.com>
 */
class Edizia_Attendance_i18n
{
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'edizia-attendance',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}