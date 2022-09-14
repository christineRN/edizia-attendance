<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://edizia.com
 * @since      1.0.0
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Edizia_Attendance
 * @subpackage Edizia_Attendance/admin
 * @author     Christine Larsen <christine@edizia.com>
 */
class Edizia_Attendance_Admin
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
	private $attendance_table;
	private $attendance_role_slug;
	private $attendance_role_display;
	private $attendance_role_capability;
	
	// Setup functions

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $edizia_attendance       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $edizia_attendance, $version )
	{

		$this->edizia_attendance = $edizia_attendance;
		$this->version = $version;
		
		$this->attendance_table = "Edizia_Attendance";
		$this->attendance_role_slug = "attendance_manager";
		$this->attendance_role_display = "Attendance Manager";
		$this->attendance_role_capability = "manage_attendance";
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->edizia_attendance, plugin_dir_url( __FILE__ ) . 'css/edizia-attendance-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->edizia_attendance, plugin_dir_url( __FILE__ ) . 'js/edizia-attendance-admin.js', array( 'jquery' ), $this->version, false );
	}
	
	function attendance_menu_setup()
	{
		// set up the attendance menu for this plugin
		// tell it to allow anyone with the role of $attendance_role_capability to see these menu items
		add_menu_page('Attendance', 'Attendance', $this->attendance_role_capability, 'attendance', [$this, 'display_attendance_options_html'], 'dashicons-forms', 6);
		add_submenu_page('attendance', 'Report', 'Report', $this->attendance_role_capability, 'report', [$this, 'display_report_options_html']);
		// enable this when needing to repair the attendance table; otherwise, leave commented out
		add_submenu_page('attendance', 'Repair', 'Repair', $this->attendance_role_capability, 'repair', [$this, 'repair_attendance_table']);
	}
		
	function attendance_activate_admin_actions()
	{
		// this method getes called when the plugin is activated
		
		// create attendance records for all the users currently in the system for any events that are in the system
		$eventList = $this->get_event_list();
		foreach ($eventList as $event)
		{
			$this->new_event_create_attendance_records($event->ID);
		}
		
		// create a new role of attendance manager and give it the capability to manage attendance
		add_role($this->attendance_role_slug, $this->attendance_role_display, [$this->attendance_role_capability => true]);
		
		// make any site administrators able to manage attendance
		$roles = wp_roles();
		$role = $roles->role_objects['administrator'];
		$role->add_cap($this->attendance_role_capability);
	}
	
	function attendance_deactivate_admin_actions()
	{
		// delete the role of attendance manager
		remove_role($this->attendance_role_slug);
		
		// remove attendance capability from site administrators
		$roles = wp_roles();
		$role = $roles->role_objects['administrator'];
		$role->remove_cap($this->attendance_role_capability);
	}
	
	// Housekeeping functions
	
	function new_event_create_attendance_records ($postID)
	{
		// create an attendance record for every member for the event
		if (wp_is_post_revision($post_id))
		{
			// if it's just a revision we don't care about it
			return;
        }
		
		// add an attendance record with that eventID for all members and default their attendance to false
		$memberList = $this->get_member_list();
		foreach ($memberList as $member)
		{
			$memberID = $member->ID;
			$this->create_attendance_record($postID, $memberID);
		}
	}
	
	function deleted_event_delete_attendance_records ($postID)
	{
		// find out if this was a post of type tribe_event
		if (get_post_type($postID) == "tribe_events") // ***** should probably figure out how to make this use the tribe class to pull this slug *****
		{
			// an event was deleted so we need to delete all the attendance records with that eventID
			$this->delete_event_attendance_records($postID);
		}
		return;
	}
	
	function new_user_create_attendance_records($memberID)
	{
		// a new user was created, so we need to add attendance records for this user for all the already created events
		$event_list = $this->get_event_list();
		foreach ($event_list as $event)
		{
			$eventID = $event->ID;
			$this->create_attendance_record($eventID, $memberID);
		}
	}
	
	function deleted_user_delete_attendance_records($memberID)
	{
		// a user was deleted, so we need to delete all their attendance records
		$this->delete_member_attendance_records($memberID);
	}
	
	function required_plugin_not_active()
	{
		// error message to display when The Events Calendar plugin is not activated
		printf("%s\n", "<div class='notice notice-error'><p>Please install and activate The Events Calendar - it is required for this plugin to work properly!</p></div>");
	}
	
	function repair_attendance_table()
	{
		// used to ensure all users have attendance records in the attendance table for all events
		$all_users = get_users();
		foreach ($all_users as $user)
		{
			$userID = $user->ID;
			$this->new_user_create_attendance_records($userID);
		}
	}
	
	// Display Functions
	
	function display_attendance_options_html($eventID = NULL) 
	{
		if (!is_plugin_active('the-events-calendar/the-events-calendar.php'))
		{
			// The Events Calendar plugin is not active so this plugin won't work
			$this->required_plugin_not_active();
			return;
		}
		
		// the page that shows when the user clicks on the Attendance menu or submenu item
		if ($eventID == NULL)
		{
			// check to see if they came here by clicking an Edit Attendance link
			$eventID = $_GET['eventID'];
		}
		if ($eventID == NULL)
		{
			// didn't get here via Edit Attendance link, so check to see if they selected a specific event
			$eventID = $_POST['event'];
		}
		
		if ($eventID == NULL)
		{
			// haven't posted to the form yet or clicked an edit attendance link, so show the initial screen
			$event_list = $this->get_event_list(NULL, 'today -3 day', 'today +3 day'); // this selects events that have start dates within the last 3 or the next 3 days
			if ($event_list == NULL)
			{
				?>
				<H1>No Events Found +/- 3 days.</H1>
				<H2>Add Events or use "Report" to Specify a Time Frame</H2>
				<?php
				return;
			}
			
			if ($_POST['event_category'] == NULL)
			{
				// have not yet received a category option - show the category selection
				?>
				<H1>Select a Category to Show Events</H1>
				<form method="POST">
					<?php
					// show all the different categories in a dropdown list, but default to selecting "Show All"
					$categoryDropList = $this->get_category_list_html("Show All", "post_title", "event_category");
					echo $categoryDropList;
					?>
				<input type = "submit" value = "Show Events">
				</form>
				<?php
			}
			else
			{
				// we have something in $_POST['event_category']
				$categoryID = $_POST['event_category'];
				$event_list = $this->get_event_list(NULL, 'today -3 day', 'today +3 day', $categoryID); // this selects events that have start dates within the last 3 or the next 3 days
				if ($event_list == NULL)
				{
					?>
					<H1>No Events Found +/- 3 days.</H1>
					<H2>Add Events or Use "Report" to Specify a Time Frame</H2>
					<?php
					return;
				}
				else
				{
					// form that allows the user to choose which event's attendance they want to edit
					?>
					<H1>Select an Event to Update Attendance</H1>
					<form method="POST">
					<?php
					$eventList = $this->get_event_list(NULL, 'today -3 day', 'today +3 day', $categoryID); // this selects events that have start dates within the last 3 or the next 3 days
					$eventDropList = $this->get_event_list_html($eventList);
					echo $eventDropList;
				}
					?>
					<BR><BR>
					<input type="submit" value="Update Attendance">
					<!-- pressing Update Attendance will capture the ID of the event they wish to update the attendance for !-->
				</form>
				<?php
			}
		}
		elseif ($_POST['submitted'] == "submitted")
		{
			// they've submitted changes to an attendance report
			$members = $this->get_member_list();
			// create an associative array that uses the member's ID as the key and whether or not they attended as the value
			$attendanceArray = array();
			foreach ($members as $member)
			{
				$memberID = $member->ID;
				$showedUP = NULL;
				$showedUP = $_POST["$memberID"];
				if ($showedUP == NULL)
				{
					// they didn't come
					$showedUP = "0";
				}
				else
				{
					$showedUP = "1";
				}
				$attendanceArray += [$memberID => $showedUP];
			}
			// save them to the database
			$this->update_attendance_records($eventID, $attendanceArray);
			// then show the report in a non-editable view
			$this->display_report_html(1, $eventID);
			// ***** want it to select the report menu item on the left hand menu *****
		}
		else
		{
			// show the report with ID of $eventID
			$this->display_editable_report_html($eventID);
		}
	}
	
	function display_report_options_html()
	{
		if (!is_plugin_active('the-events-calendar/the-events-calendar.php'))
		{
			// The Events Calendar plugin is not active so this plugin won't work
			$this->required_plugin_not_active();
			return;
		}
		
		// page that shows when the user clicks on the Report submenu item
		// form that will allow the user to specify category and dates for the report
		$eventID = $_GET['eventID']; // there will be something here only if they came to this through an Edit Attendance link
		
		if ($eventID == NULL)
		{
			// they didn't click an Edit Attendance link
			$startDate = NULL;
			$endDate = NULL;
			$categoryID = NULL;
			if ($_POST['start_date'] != NULL && $_POST['end_date'] != NULL)
			{
				$startDate = date_create($_POST['start_date'] . " 00:00:00", wp_timezone()); // start at midnight of the start date
				$endDate = date_create($_POST['end_date'] . " 23:59:59", wp_timezone()); // end at 11:59 pm of the end date
				$categoryID = $_POST['event_category'];
			}
			if ($startDate == NULL || $endDate == NULL)
			{
				// they haven't submitted the form yet so show the initial screen
				$event_list = $this->get_event_list(NULL, NULL, 'now', $categoryID);
				if ($event_list == NULL)
				{
					?>
					<H1>No Events Found. Please Add Events.</H1>
					<?php
					return;
				}
				?>
				<H1>Enter Selections to View Report</H1>
				Category:
				<form method="POST">
					<?php
					// show all the different categories in a dropdown list, but default to selecting "Show All"
					$categoryDropList = $this->get_category_list_html("Show All", "post_title", "event_category");
					echo $categoryDropList;
					?>
					<BR><BR>
					Between <input type = "date" name = "start_date" id = "start_date" required> and <input type = "date" name = "end_date" id = "end_date" required> (Inclusive)
					<BR><BR>
					<input type = "submit" value = "View Report">
					<!-- pressing View Report will capture category name and start/end dates for the report they've requested !-->
				</form>
				<?php
			}
			else
			{
				// show the report of events for the selected category and dates
				$this->display_report_html(NULL, NULL, $startDate, $endDate, $categoryID);
			}
		}
		else
		{
			// they clicked an Edit Attendance link so call the display_attendance_options_html method and send it $eventID
			$this->display_attendance_options_html($eventID);
		}
	}
	
	function display_report_html($count, $eventID = NULL, $startDate = NULL, $endDate = NULL, $categoryID = NULL)
	{
		// show a spreadsheet of all the members for the specified events, depicting if they attended or not
		// also build an array to send to array_to_csv_download() in case they want to save to csv
		$csvArray = array();
		?>
		<H1> Attendance Report
		<?php
		if ($eventID == NULL)
		{
			// we don't need to show a specific event - just a list of the events between the startDate and endDate
			?> of <?php
			$lineText = $lineText . " of ";
			if ($categoryID != NULL && $categoryID != "0")
			{
				$categoryName = $this->get_event_category_name_from_id($categoryID);
				echo $categoryName;
			}
			else
			{
				echo "All Events";
			}
			?></H1><?php
			echo "<H2>" . $this->get_pretty_date($startDate) . " - " . $this->get_pretty_date($endDate) . "</H2>";
			// get the events that meet criteria
			$event_list = $this->get_event_list(NULL, $startDate, $endDate, $categoryID);
		}
		else
		{
			// get the specific event
			$event_list = $this->get_event_list($eventID);
			?>
			</H1>
			<?php
		}
		$member_list = $this->get_member_list();
		?>
		<TABLE class="report_table"><TR><TH>Name</TH>
		<?php
		$lineArray = array("Name");
		foreach ($event_list as $event)
		{
			// show the names and dates of all the events
			$pretty_date = tribe_get_start_date($event->ID, true, 'M d, Y @ g:i a');
			echo "<TH>" . $event->post_title . "<BR>" . $pretty_date . "<BR><a href='" . add_query_arg('eventID', $event->ID) . "'>Edit Attendance</a></TH>";
			$arrayText = $event->post_title . " " . $pretty_date;
			array_push($lineArray, $arrayText);
		}
		array_push($csvArray, $lineArray);
		?>
		</TR>
		<?php
		
		// for each member
		foreach($member_list as $member)
		{
			$lineArray = array($member->display_name);
			$arrayText = "";
			$memberID = $member->ID;
			// display their avatar and name
			echo "<TR><TD>" . get_avatar($memberID) . "<BR>" . $member->display_name . "</TD>";
			foreach ($event_list as $event)
			{
				$eventID = $event->ID;
				
				// get the attendance record for this event and this member
				$attended = $this->get_attendance_record($eventID, $memberID);
				// if this user was at the event
				if ($attended)
				{
					// they were there so display a check mark
					$glyph = "&#10003;";
					$bgClass = "report_present";
					$arrayText = "yes";
				}
				else
				{
					// they weren't there so display an X
					$glyph = "X";
					$bgClass = "report_absent";
					$arrayText = "no";
				}
				?>
				<TD class = <?=$bgClass?>><CENTER>
				<?php
				echo $glyph;
				array_push($lineArray, $arrayText);
				?>
				</CENTER></TD>
				<?php
			}
			array_push($csvArray, $lineArray);
		
			// finish the table row
			?>
			</TR>
			<?php
		}
		// finish the table
		?>
		</TABLE>
		<?php
		$linkArray = wp_upload_dir();
		$file_path = $linkArray['path'] . "../../../AttendanceReport.csv";
		$file_url = $linkArray['url'] . "../../../AttendanceReport.csv";
		$this->array_to_csv_download($csvArray, $file_path);
		echo "<a href='$file_url'>Download Report as CSV</a>";
	}
	
	function display_editable_report_html($eventID)
	{
		// displays attendance report for event $eventID in an editable format and allows the user to update attendance for the event
		$member_list = $this->get_member_list();
		$current_event = $this->get_event_list($eventID)[0]; // it returns a list of one element so I want to get that one element
		// display the name of the event
		echo "<H1>" . $current_event->post_title . "</H1>";
		?>
		<FORM method = "POST">
			<TABLE class="report_table"><TR><TH>Name</TH>
			<?php
			// show the date of the current event
			$pretty_date = tribe_get_start_date($current_event->ID, true, 'M d, Y @ g:i a');
			echo "<TH>$pretty_date</TH>";
			?>
			</TR>
			<TR><TD></TD><TD><CENTER><input type = "submit" value = "Save"></CENTER></TD></TR>
			<?php
			// for each member
			foreach($member_list as $member)
			{
				$memberID = $member->ID;
				// display their avatar and name
				echo "<TR><TD>" . get_avatar($memberID) . "<BR>" . $member->display_name . "</TD>";
				// get the attendance record for this event and this member
				$attended = $this->get_attendance_record($eventID, $memberID);
				// name each checkbox with the $memberID of the current $member
				?>
				<TD><input type = "checkbox" id = "<?php echo $memberID; ?>" name = "<?php echo $memberID; ?>" 
				<?php
				// if this user was at the event
				if ($attended)
				{
					// they were there so display a checked checkbox
					?>Checked<?php
				}
				else
				{
					// they wern't there so display an empty checkbox
					?><?php
				}
				?>
				></TD></TR>
				<?php
			}
			// finish the form and table
			?>
			<input type = "hidden" name = "submitted" id = "submitted" value = "submitted">
			<input type = "hidden" name = "event" id = "event" value = "<?php echo $eventID; ?>">
			<TR><TD></TD><TD><CENTER><input type = "submit" value = "Save"></CENTER></TD></TR>
			</TABLE>
		</FORM>
		<?php		
	}
	
	
	// Primary helper functions
	
	function get_category_list_html ($showAll, $orderBy, $selectName, $onChangeMethod = NULL)
	{
		$category_dropdown = wp_dropdown_categories(array(
					'show_option_all'	=> $showAll, // what the show all option says
					'echo'       		=> false,
					'hide_empty' 		=> true,
					'orderby'    		=> $orderBy, // what to sort the list by
					'taxonomy'   		=> Tribe__Events__Main::TAXONOMY, // categories for The Event Calendar events
					'heirarchical'		=> true,
					'name'				=> $selectName, // the name of the select item returned
					'required'			=> true
				));
		
		
		if ($onChangeMethod != NULL)
		{
			// make it so this dropdown will call a method $onChangeMethod when onchange event fires
			// this was wishful thinking when I thought I could figure out how to use javascript
			$category_dropdown = str_replace("<select", "<select onchange='$onChangeMethod'", $category_dropdown);
		}
		return $category_dropdown;
	}
	
	function get_event_list_html ($event_list)
	{
		// return the html for an event list using the given list
		$html = "";
		$html .= "<select name = 'event' id = 'event' size = 1 required>";
		foreach($event_list as $event)
		{
			$html .= "<option value=" . $event->ID . ">" . $event->post_title . " " . tribe_get_start_date($event, true, "M d, Y @ g:i a") . "</option>";
		}
		$html .= "</select>";

		return $html;
	}
	
	function get_event_list ($eventID = NULL, $displayStarting = NULL, $displayThrough = NULL, $categoryID = NULL)
	// send $eventID for a specific event
	// Important: if $eventID != NULL, it will ignore all the other variables
	// send $displayStarting to show events starting after that date
	// send $displayThrough to show events starting before that date
	// send $categoryID to show events of only a specific category
	{
		// get the list of events, sort it by event date, and return it
		if ($eventID != NULL)
		{
			// we just want one specific event, of $eventID, and we're ignoring all the other variables
			$event = tribe_get_events(['ID' => $eventID]);
			return $event;
		}
		else
		{
			$args = array();
			
			// set number of events to see; if this isn't set, it defaults to Events->Settings->Number of events to show per page
			$args += ['posts_per_page' => -1]; // -1 indicates all events
			
			// $displayStarting, $displayThrough, and $categoryID may all have values in them
			if ($displayStarting != NULL)
			{
				$args += ['starts_after' => $displayStarting];
			}
			if ($displayThrough != NULL)
			{
				$args += ['starts_before' => $displayThrough];
			}
			if ($categoryID != NULL && $categoryID != "0") // 0 is the categoryID of "Show All"
			{
				$args += ['tax_query'=> array(array('taxonomy' => 'tribe_events_cat', 'field' => 'term_id', 'terms' => $categoryID))];
			}
			$eventList = tribe_get_events($args);
			// sort the event list by start date of the events
			$eventList = $this->sort_event_list_by_start_date($eventList);
			return $eventList;
		}
	}
	
	function get_event_category_name_from_id($categoryID)
	{
		// get the name of the category using the categoryID
		$terms = get_terms( array(
			'taxonomy' => 'tribe_events_cat',
			'hide_empty' => false,
		) );
		foreach($terms as $term)
		{
			if ($term->term_id == $categoryID)
			{
				$categoryName = $term->name;
				return $categoryName;
			}
		}
		return "";
	}
	
	function get_member_list ()
	{
		// get the list of members in ascending alphabetical order by display name and return it
		$args = array('orderby' => 'display_name', 'order' => 'ASC');
		$userList = get_users($args);
		return $userList;
	}
	
	function create_attendance_record ($eventID, $memberID)
	{
		// creates a new attendance record with the default of attended = 0
		global $wpdb;
		$wpdb->query ("insert into " . $this->attendance_table . " (eventID, userID, attended) values ($eventID, $memberID, 0)");
	}
	
	function get_attendance_record ($eventID, $memberID)
	{
		global $wpdb;
		$query = "select attended from " . $this->attendance_table . " where eventID = $eventID and userID = $memberID";
		$list = $wpdb->get_results($query, ARRAY_A);
		$attended = $list[0]['attended'];
		if($attended == "1")
		{
			return true;
		}
		return false;
	}
	
	function update_attendance_records ($eventID, $attendanceArray)
	{
		global $wpdb;
		// an event's attendance report was updated so resave it to the database
		$memberList = $this->get_member_list();
		foreach ($memberList as $member)
		{
			$memberID = $member->ID;
			$attendance = $attendanceArray["$memberID"];
			$wpdb->query ("update " . $this->attendance_table . " set attended = $attendance where userID = $memberID and eventID = $eventID");
		}
	}
	
	function delete_member_attendance_records ($memberID)
	{
		global $wpdb;
		// delete all attendance records for a user of $memberID
		$wpdb->query ("delete from " . $this->attendance_table . " where userID = $memberID");
	}
	
	function delete_event_attendance_records ($eventID)
	{
		global $wpdb;
		// delete all attendance records for an event of $eventID
		$wpdb->query ("delete from " . $this->attendance_table . " where eventID = $eventID");
	}
	
	function get_attendance_table_name()
	{
		return $this->attendance_table;
	}
	
	
	// Secondary helper functions
	
	function array_to_csv_download($array, $fileLink = "export.csv", $delimiter=";")
    {
		$file = fopen($fileLink, 'w');
		
		foreach ($array as $line)
		{
			// generate csv lines from the array
			fputcsv($file, $line, $delimiter);
		}
		
		fclose($file);
	}
	
	function get_pretty_date ($theDate)
	{
		// take a date object and return a pretty string
		return date_format($theDate, "M d, Y @ g:i a"); // this will output as Jan 5, 2022 @ 8:30 am
	}
	
	function sort_event_list_by_start_date($eventList)
	{
		// takes an event list as provided by The Event Calendar tribe_get_events() method
		// and sorts it according to the start date of each event in the list, putting most recent at the beginning
		uasort($eventList, [$this, 'sort_by_start_date']);
		return $eventList;
	}
	
	function sort_by_start_date($itemA, $itemB)
	{
		// sorts the two items by start date
		$dateA = strtotime(tribe_get_start_date($itemA, false, "Y-m-d H:i:s"));
		$dateB = strtotime(tribe_get_start_date($itemB, false, "Y-m-d H:i:s"));
		if ($dateA > $dateB)
		{
			return 1;
		}
		elseif ($dateA == $dateB)
		{
			return 0;
		}
		else
		{
			return -1;
		}
	}
}
?>
