<?php
    /***************************************************
     * Backup.php
     *
     * Description:  backs up the DB
     ***************************************************/

    // output the header
    $title = "Back Up";
    require_once('header.php');

		// execute the backup
		$p = 'paris1257';
		exec('C:\xampp\mysql\bin\mysqldump --opt --databases attendencetracker --user=root --password='.$p.' --host=localhost > C:\Users\Jason\Dropbox\www\mysql_backup\\' . date('F_d_Y', time()) . '_AttendenceTracker_db_backup.sql');


		/****
 		 *  Add a new section to the backup to be used in the new update function of the program
 	 	 *  when it starts up.  Just create a file--overwrite if exists--that contains a current timestamp.
 	 	 ****/
		 $file = fopen('C:\Users\jones\Dropbox\YAT_Backups\ts.txt', 'w');
		 if ( !fwrite($file, time()) )
		 		echo '<h1 style="position: relative; top: 100px; left: 25px; color: White;">ERROR WRITING THE TIMESTAMP FILE.</h1>';

		 fclose($file);

		 // output success
		 echo '<h1 style="position: relative; top: 100px; left: 25px; color: White;">The Database is backed up!</h1>';

		 require_once('footer.php');
?>
