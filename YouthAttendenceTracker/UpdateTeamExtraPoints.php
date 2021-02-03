<?php
    // Turn off all error reporting
    error_reporting(0);
        
    /***********************************************
     * UpdateTeamExtraPoints.php
     *
     * Description:  updates table for team extra
     *       points
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
    
    // get the variables from get
    if ( isset($_GET['team_id']) && $_GET['team_id'] != '' ) 
        $team_id = $_GET['team_id'];
    else
        echo "error|Could not get the team id.";
    if ( isset($_GET['service_id']) && $_GET['service_id'] != '' ) 
        $service_id = $_GET['service_id'];
    else
        echo "error|Could not get the services's id.";
    if ( isset($_GET['value']) && $_GET['value'] != '' ) 
        $points = $_GET['value'];
    else
        echo "error|Could not get the services's points value.";
    if ( isset($_GET['desc']) && $_GET['desc'] != '' ) 
        $description = $_GET['desc'];
    else
        echo "error|Could not get the services's description.";
        
    // query db and return results
    $query = "INSERT INTO servicepoints (ServiceID, TeamID, Points, Description) VALUES ($service_id, $team_id, $points, '$description');";
    if ( $db->query($query) )
        echo "success|";
    else
        echo "error|Could not update the service points.";
    
    $db->close();
    exit();
?>