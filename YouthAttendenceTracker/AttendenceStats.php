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
	$title = "Attendence Stats";
	require_once('Header.php');
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	require_once('Misc.php');
	
	if ( !isset($_GET['year']) )
		$year = date('Y');
	else
		$year = $_GET['year'];
	if ( !isset($_GET['range']) )
		$range = 1;
	else 
		$range = $_GET['range']
?>

<div id="stat-container">
	<div id="stat-scale"><img src="site_images/attendence_stats_scale.png" /></div>
	
	<!-- stat bars for jan - dec -->
	<?php
		// output the stat bars for attendence records.  use template for easier updating in the future
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		$month_classes = array("jan", "feb", "march", "april", "may", "june", "july", "aug", "sep", "oct", "nov", "dec");
		$months_int = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
		
		// don't output text for attendence for multiple years because the text is on top of other bars/text/etc.
		if ( $range == 1 ) 
			$template = '<div id="stat-bar" class="%s center" style="width: %dpx; height: %dpx; %s"><div class="vertical-text">%s&nbsp;%s&nbsp;-&nbsp;%s</div></div>';
		else
			$template = '<div id="stat-bar" class="%s center" style="width: %dpx; height: %dpx; %s"></div>';
			
		$multiplier = 20;  // multiplier for bar height
		for ( $i = 1; $i < 13; $i++ ) {
			// determine the bar width by range and 20px available
			$bar_width = 20 / $range;
			
			// print the bar, print the range number of years for each month
			for ( $j = $range - 1; $j >= 0; $j-- ) {
				// get average attendence from function
				$avg_attendence = get_average_attendence($db, $i, $year - $j);
				// just multiply avg_attendence by the multiplier to get the bar_height
				$bar_height = $avg_attendence * $multiplier;
			
				// if bar_height is 0, don't print (no attendence or more likely a month in the future)
				if ( $bar_height == 0 )
					continue;
				
				$offset = -($j * $bar_width) + 15;
				$extra_css = "margin-left: " . $offset . "px;";
				$range_class = "";
				if ( $j == 0 )
					$range_class = " cur-year";
				else if ( $j == 1 ) 
					$range_class = " last-year";
				else if ( $j == 2 )
					$range_class = " two-year";
				else
					$range_class = " three-year";
			
				if ( $range == 1 )
					printf($template, $month_classes[$i-1] . $range_class, $bar_width, $bar_height, $extra_css, $months[$i-1], $year - $j, $avg_attendence);
				else
					printf($template, $month_classes[$i-1] . $range_class, $bar_width, $bar_height, $extra_css);
			}
		}
	?>
	<div id="stat-year-scale">
		Current Year <div class="cur-year"></div>
		Last Year <div class="last-year"></div> 
		2 Years Ago <div class="two-year"></div>
		3 Years Ago <div class="three-year"></div>
	</div>
</div>

<div id="stat-bar-bottom"><img src="site_images/attendence_stats_bottom_bar.png" /></div>

<?php require_once('footer.php'); ?>
