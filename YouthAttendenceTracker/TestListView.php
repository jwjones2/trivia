<?php
	 /********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and send if not
	if ( $db->connect_error ) {
		echo 'error|There was a problem connecting to the database.';
		exit();  // cannot continue since no connection established
	}
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
	
	
?>

<html>
	<head>
		<title></title>
		
		<style type="text/css">
			.backRed
			{
				background-color: Red;
			}
		</style>
	</head>
	
	<body>
		<div style="text-align: center;font-size: 22px;font-weight: bold;">
		  <table>
			<tr><th align="center">The names</th></tr>
		
		<?php
			require_once('WebControls.php');
			$list = new ListView($db, 'attendee', 'FirstName|LastName', '<td align="center">%s %s</td>', 'DATE(ReleaseFormUpdate) BETWEEN "2011-01-01" AND "2011-12-31" ORDER BY LastName');
			
			if ( !$list->is_error() ) // if there are no errors
			{
				// print every other row with background-color yellow
				$counter = 1;
				echo '<tr class="backRed">';
				if ( $ret = $list->pop_results() ) {}
				echo '</tr>';
				while ( $ret ) 
				{
					if ( $counter % 2 == 0 )  // if even row
						echo '<tr class="backRed">';
					else
						echo '<tr>';
						
					$ret = $list->pop_results();
					
					echo '</tr>';
					
					$counter++;
				}
			}
			
		?>
		</div>
	</body>
</html>
