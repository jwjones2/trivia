<?php
	/********************************************************
	 * SignupSetup.php
	 *
	 * Description:  The setup page for Signup.php.  Calls Signup.php
	 *	with all the TeamIDs to process a signup page for.
	 ********************************************************/

	 
	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";	
	
	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		$error = "There was a problem connecting to the database.";
	}
	
	/*** INCLUDE SECTION for helper classes  ***/
	require_once('DBTable.php');       // the DBTable superclass
	require_once('Table_helper.php');  // the sub classes of DBTable to handle db table interactions
	require_once('HTML_helper.php');   // functions for handling HTML output and manipulation
?>

<html>

	<head>
		<title>Team Sign-up Setup</title>
		
		<!--favicon icon-->
		<link rel="shortcut icon" href="./favicon.ico" />
   
		<!-- Metadata Section:  Description of Page and Keywords for Site. -->
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		
		<!-- Link to Stylesheet -->
		<link rel="stylesheet" href="./main.css" type="text/css" />
		
		<!-- Link to Javascript code for page -->
		<script type="text/javascript" language="Javascript" src="./main.js"> </script>
		
		<script type="text/javascript" language="Javascript">
		
		</script>
		
		<style type="text/css">
	        	
		</style>
		
	</head>
	
	<body style="background-color: Gray;">
		<?php
    			/* OUTPUT THE SITE'S MENU */
    			write_menu();
    		?>
    		
		<div class="signup_setup_form">
			<form name="signupsetup" action="./SignUp.php" method="post">
					<br />
				<b>Please Select the teams to Signup.</b>
					<br /><br /><br />
				<select name="TeamID[]" multiple="multiple">
					<?php
                    				/***
						 * Query the db activeteam and get the IDs of active teams to then use to query the
                    				 * team db and get all team names to populate the select box.
                    				 ***/
                    				$query = 'SELECT TeamID FROM activeteam;';
                    				
                    				if ( $result = $db->query($query) ) 
                    				{
                    					while ( $obj = $result->fetch_object() ) 
                    					{
                    						// Use TeamID to query the team DB and get team Name
                    						$q = 'SELECT Name FROM team WHERE ID = ' . $obj->TeamID . ';';
                    						
                    						if ( $r = $db->query($q) ) 
                    						{
                    							while ( $o = $r->fetch_object() ) 
                    							{
                    								// Build the select <option> element with TeamID and name
                    								printf('<option value="%s">%s</option>', $obj->TeamID, $o->Name);    // output:  <option value="teamID">Team Name</option>
                    							}
                    						}
                    					}
                    				}
					?>
				</select>
					
					<br />
				
				<input type="submit" value="Start Signing Up" name="submit" />
			</form>
			
				<h4 class="center"><a href="./IndividualSignup.php">Or, go to Individual Signup Page</a></h3>
			
			<b class="fine_print">Hold down the control key when selecting to select more than one team.</b>
		</div>
	</body>
</html>
