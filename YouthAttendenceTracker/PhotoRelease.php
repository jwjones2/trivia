<?php
	/********************************************************
	 * AddAttendee.php
	 *
	 * Description:  A form to add a new student.  Self-posting.
	 * 	Inserts data for attendee and student tables.
	 ********************************************************/

	// output the header
	$title = "Photo Release";
	require_once('Header.php');

	/********************************************************
	* Database Connection section.
	********************************************************/
 // Connect to the db; use MYSQL_helper.php for mysql login
 require_once('MYSQL_helper.php');
 $db = db_connect( );

?>


    	  <?php
    	  		//

        ?>
 <!-- Form section -->
        <div id="" class="">
					<table class="photo-release-table">
						<?php
								/***************************************************************
								 * Query the Database for photo release info.
								 * Display who has current and what type.
								 ***************************************************************/
								$query = 'SELECT ID, FirstName, LastName, PicturePolicy FROM attendee WHERE PicturePolicy = "full" OR PicturePolicy = "partial" ORDER BY LastName;';
								if ( $results = $db->query($query) ) {
									while ( $object = $results->fetch_object() ) {
										printf('<tr><td>%s %s</td><td>%s</td></tr>', $object->FirstName, $object->LastName, strtoupper($object->PicturePolicy));
									}
								}
						 ?>
					 </table>

        </div> <!-- end form sectioin -->


    </body>
</html>
