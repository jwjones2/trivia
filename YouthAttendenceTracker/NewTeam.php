<?php
	/********************************************************
	 * NewTeam.php
	 *
	 * Description:  
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
	
	/** FLAG FOR SUBMISSION CHECK AND FORM VALIDATION BY PHP **/
	$failed_submit = false;
	
	function checkForFail ( $name )
	{
		if ( isset($failed_submit) && $failed_submit )
                {
                	printf(' value="%s" ', $_POST[$name]);  // put the name submitted back in form 
                	if ( isset($blank_name) )  // if blank_name is set then highlight the form field
                		echo ' style="background-color: yellow;" ';
                }
        }
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
        <title>New Team</title>
        
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
    
    <body class="formBody">
    	<!-- MENU SECTION -->
    	<?php
    		require_once('HTML_helper.php');
    		write_menu();
    	?>
		
    	<?php
    		/***************************************************************
    		 * Main PHP Functionality
    		 * 
    		 * If form was submitted:
    		 * 1. Validate form elements and display any errors
    		 * 2. Create the bound query from form elements
    		 * 2. Execute the query
    		 * 3. Get the new Team ID
    		 * 4. Redirect to PickTeamMembers passing new ID
    		 *
    		 **************************************************************/
    		 // FIRST, check that form was submitted
    		 if ( isset($_POST['_submit_check']) )
    		 {
    		 	 // THEN, validate the form
    		 	 if ( !$_POST['Name'] == "" )  // Name cannot be blank
    		 	 {
    		 	 	 // Get global variables and escape and bind variables
    		 	 	 $clean = array();  // clean array will hold escaped variables
    		 	 	 
    		 	 	 $clean['Name'] = $db->real_escape_string($_POST['Name']);  // use mysqli_real_escape_string to prepare variable for db insert
    		 	 	 $clean['Notes'] = $db->real_escape_string($_POST['Notes']);
    		 	 	 
    		 	 	 /**  Build the statement and bind parameters  **/
    		 	 	 $statement = $db->prepare("INSERT INTO team (Name, Start, Notes) VALUES (?, ?, ?)");
    		 	 	 $statement->bind_param('sss', $name, $start, $notes);	
    		 	 	 
    		 	 	 $name = $clean['Name'];
    		 	 	 $notes = $clean['Notes'];
    		 	 	 // Prepare the Start date by using PHP now date
    		 	 	 $start = date('Y-m-d');   // get date in format 0000-00-00 
     		 	 	 
    		 	 	 // Now, execute the statement and make sure is success.
    		 	 	 $success = $statement->execute();  // $success holds true if successfully executed.
    		 	 	 
    		 	 	 // check that query was successful and team was created
    		 	 	 // if so, insert the team into the activeteam table and redirect passing ID
    		 	 	 // else, fall through to form that will have validation suggestions
    		 	 	 if ( $success ) 
    		 	 	 {	
    		 	 	 	 // get the id of the new Team from insert_id
    		 	 	 	 $team_id = $db->insert_id;
    		 	 	 	 
    		 	 	 	 // insert the new team into the activeteam table
    		 	 	 	 $q = 'INSERT INTO activeteam (TeamID) VALUES (' . $team_id . ');';  // create query statement
    		 	 	 	 $db->query($q);  // query the db    		 	 	 	 
    		 	 	 	 
    		 	 	 	 /*** ATTENDEE AND STUDENT ADDED SUCCESSFULLY!  REDIRECT TO SUCCESS PAGE ***/    		 	 	 	 
    		 	 	 	 /* Redirect browser */
    		 	 	 	 header("Location: ./PickTeamMembers.php?ID=" . $team_id);
    		 	 	 	 /* Make sure that code below does not get executed when we redirect. */
    		 	 	 	 exit;
    		 	 	 }
    		 	 	 else
    		 	 	 {
    		 	 	 	 $failed_submit = true;  // set failed_submit to true for validation check within form below
    		 	 	 }
    		 	 }
    		 	 else   // name was blank
    		 	 {
    		 	 	 $failed_submit = true;
    		 	  	$blank_name = true;  // used by form validator to highlight the name form element
    		 	 }
    		 }	
	?>  	
        
 <!-- ERROR section -->
 	<div id="showErrors" class="showErrors">

 	</div>
 
 <!-- Form section -->
        <div id="formSection" class="profileFormSection">
            <!-- top of form -->
            <span class="profileFormTop center"><span class="moveDown">New Team <?php if ( $failed_submit ) echo '<br /><span style="color:red;">The Team was not created.  Please try again.'; ?></span></span>
        			
            <!-- Bottom of form **Placed at top so that form contents will overlap it placing its content behind form body content -->
            <span class="profileFormBottom"></span>
           
            
            
			<form id="teamForm" name="teamForm" class="teamForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validateTeamForm()">
				<table cellpadding="3px">
                    <!-- Row 1 -->
                    			<tr>
                    				<td class="right label">Team Name:  </td>
                    				<td><input type="text" name="Name" id="Name" size="30" <?php checkForFail('Name'); ?> /></td>
                    			</tr>
                   
                    <!-- Row 2 -->
                    			<tr>
                    				<td class="label">Notes</td>
                    			</tr>
                    			
                    <!-- Row 3 -->
                    			<tr>
                    				<td colspan="2" class="center"><textarea id="Notes" name="Notes" cols="40"><?php populate_form('Notes'); ?></textarea></td>
                    			</tr>
                    			
                    <!-- Row 4 -->
                    			<tr>
                    				<td class="center"><a href="./Home.html"><input type="button" value="Cancel" /></td>
                    				<td class="center"><input type="submit" value="Create Team" id="submit" name="submit" /></td>
                    			</tr>
                    			
                    		</table>
                    		
                    		<input type="hidden" name="_submit_check" value="1" /> 
                    	</form>
            </div>
            
        <!-- HOME LOGO SECTION -->
        <div class="homeLogoButton"><a href="./home.php"><img src="./site_images/Homelogo.gif" /></a></div>
            
	</body>
<html>
