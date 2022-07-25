<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/public
 * @author     Christine Larsen <christine@edizia.com>
 */
class Edizia_Attendance_Public
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $edizia_attendance    The ID of this plugin.
	 */
	private $edizia_attendance;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $edizia_attendance       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $edizia_attendance, $version )
	{
		$this->edizia_attendance = $edizia_attendance;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Edizia_Attendance_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Edizia_Attendance_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->edizia_attendance, plugin_dir_url( __FILE__ ) . 'css/edizia-attendance-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Edizia_Attendance_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Edizia_Attendance_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->edizia_attendance, plugin_dir_url( __FILE__ ) . 'js/edizia-attendance-public.js', array( 'jquery' ), $this->version, false );
	}
}