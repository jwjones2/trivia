<?php

set_time_limit(120);  // from 30 seconds to 2 minutes

// define the url for Dropbox folder on the local machine (needs to change each computer implemented on)
$url = 'C:\Users\jones\Dropbox\YAT_Backups\\';

$p = 'paris1257';
exec('C:\xampp\mysql\bin\mysql --user=root --password='.$p.' < ' . $url . 'backup.sql');

// update the 'l' timestamp to match the current
$ts = $url . 'ts.txt';
$l = $url . 'l.txt';
if ( !$file_ts = fopen($ts, "r") ) {
	echo "error|Couldn't open the timestamp file.";
	exit();
}
/**********NEED TO CONSIDER THIS -- MAY ALLOW FOR DELAY BECAUSE OF SERVER RESPONSE AND CAUSE A PROBLEM
	IF AN UPDATE TO TS HAPPENED BETWEEN THE CALL TO CHECKRESTORE AND RESTORE.
	This is probably not a problem now because of the limited number of users to this program but would
	not be sufficient for larger usage environments.
********************************************************************************************************/
if ( !$backup_timestamp = fread($file_ts, filesize($ts)) ) {
	echo "error|Could not get backup timestamp.";
	exit();
}
file_put_contents($l, $backup_timestamp);

echo "success|";

?>
