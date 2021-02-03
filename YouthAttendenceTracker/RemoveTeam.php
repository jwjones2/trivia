<?php
    // Turn off all error reporting
    error_reporting(0);
        
    /***********************************************
     * RemoveTeamMember.php
     *
     * Description:  takes a team id and removes
     * 		from table.
     ***********************************************/
    
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
	    echo "error|Couldn't connect to the database.";
    }

    // get the team id from get
    if ( isset($_GET['team_id']) && $_GET['team_id'] != '' ) 
        $team_id = $_GET['team_id'];
    else
        echo "error|Could not get the team id.";
        
    // query db and return results
    $query = "DELETE FROM team WHERE ID=$team_id;";
    if ( $db->query($query) )
        echo "removed|$team_id";
    else
        echo "error|Could not remove the team.";
    
    $db->close();
    exit();
?>