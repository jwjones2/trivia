<?php
	session_start();
	error_reporting(0);
	/********************************************************
	 * Signin.php
	 *
	 * Description:  This is a Master page for signins.  Handles
	 * 	individual signins.  As each student is entered, the
	 * 	database is adjusted (temp table) and then if submitted
	 * 	the data is entered in servicelist.
	 *
	 * 	??Can be called with an id to get summary mode??
	 *
	 ********************************************************/

	// output the header
	$title = "Team Page";
	$body_class = "formBody";
	require_once('Header.php');

	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	include_once('Misc.php');          // for extra functions (like processing the date)


	// check for old teams flag
	if ( isset($_GET['show_old']) && $_GET['show_old'] == "T" )
		$show_old = true;
	else
		$show_old = false;
?>

	   <div class="teamList">
			 <table>
		 	 <?php
		 			require_once('WebControls.php');
		 			$list = new ListView($db, 'attendee', 'FirstName|LastName|PicturePolicy', '<tr><td>%s %s - %s</td></tr>', 'Active="yes" AND PicturePolicy!="no" ORDER BY LastName');
		 			$list->print_results();
		   ?>
		 	</table>
	   </div>


<?php include_once('Footer.php'); ?>
