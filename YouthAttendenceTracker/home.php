<?php
	/*************************************************************
	 * home.php
	 *
	 * Description:  The main page for YouthAttendenceTracker.
	 * 	Calendar and other useful data.
	 *************************************************************/

	// output the header
	$title = "Attendence Tracker Homepage";
	require_once('Header.php');

?>

		<div class="topLayer">

			<iframe src="./ReleaseFormList.php?year=<?= $_GET['year'] ?>&month=<?= $_GET['month'] ?>" width="20%" height="100%" class="leftFrame">
				<p>Your browser does not support iframes.</p>
			</iframe>

		</div>

		<!-- Calendar -->
		<div id="calendar-section">
			<?php
				// CALENDAR WRITE SECTION USING CALENDAR CLASS IN CALENDAR.PHP
				require_once('HTMLCalendar.php');

				// get month and year from GET or else set to current
				require_once('Misc.php');
				check_and_assign($_GET['month'], $month, date('n'));
				check_and_assign($_GET['year'], $year, date('Y'));
				$cal = new HTMLCalendar($month, $year);
				$cal->set_header_title('Youth Calendar', true);
				$cal->set_cell_template('<div class="cell %l %F-%i"" style="position: absolute; left: %ppx; width: %wpx; height: %hpx;"><div id="day">%i</div><span class="%c" id="%i"></span></div>');
				// set the width of days/columns
				//$cal->set_column_widths(array("Monday|100", "Tuesday|100", "Wednesday|200", "Thursday|100", "Friday|100", "Saturday|100", "Sunday|200"));
				$cal->print_html(650, 450);
			?>
		</div>

	<?php require_once('footer.php'); ?>
