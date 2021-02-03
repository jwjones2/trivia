<?php
/************************************************************
 * restore_inc.php
 *
 * Description: This is the starting point for the updating
 *    process for checking for updates to the main db.
 *    This file will include all needed files.
 ************************************************************/

?>

// start by outputing the javascript
<script src="restore.js"> </script>

<script>
// Restore if there is an update for the db -- this is the starting point for the interaction for Restore
$ ( function () {
  $ ( document ).ready( showUpdateRecordsDialogue() );
});
</script>

<!--  Update Records Dialogue section -->
<div id="update-records-dialogue" style="position: absolute; left: 0px; bottom: 0px; width: 100%; height: 75px; text-align: center; font-size: 22px; font-weight: bold; color: #ffffff; background-color: rgba(241, 157, 0, 0.6); display: none;"> </div>
