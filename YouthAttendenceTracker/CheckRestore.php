<?php

/**
 * Functionality
 *
 * 1. Check the timestamp in ts.txt and then compare to l.txt to see if this update is
 *    newer.
 * 2. If newer, run mysql to import the db backup.
 */

 // define the url for Dropbox folder on the local machine (needs to change each computer implemented on)
 $url = 'C:\Users\jones\Dropbox\YAT_Backups\\';

// Get timestamp from ts and l
$ts = $url . 'ts.txt';
$l = $url . 'l.txt';
if ( !$file_ts = fopen($ts, "r") ) {
	echo "error|Couldn't open the timestamp file.";
	exit();
}
if ( !$file_l = fopen($l, "r") ) {
	echo "error|Couldn't open the 'last' timestamp file.";
	exit();
}

if ( !$backup_timestamp = fread($file_ts, filesize($ts)) ) {
	echo "error|Could not get backup timestamp.";
	exit();
}

if ( !$current_timestamp = fread($file_l, filesize($l)) ) {
	echo "error|Could not get 'last' timestamp.";
	exit();
}

// close handles
fclose($file_l);
fclose($file_ts);

// now see if current (or last loaded timestamp of backup) is smaller than the
// backup's timestamp;  if so do the restore

if ( $current_timestamp < $backup_timestamp ) {
	//*** Split the actual restore up to be able to interactively show to the user
	echo "update|";
} else {
	echo "none|";
}

?>
