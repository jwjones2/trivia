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

<div class="start-message">
  Starting... Checking for backups.
</div>

<script>

  $( document ).ready( function () {
    checkForBackups();
  });

</script>


	<?php require_once('footer.php'); ?>
