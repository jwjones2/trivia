<?php
    // Turn off all error reporting
    error_reporting(0);
        
    /***********************************************
     * DeleteTeamExtraPoints.php
     *
     * Description:  deletes extra point value from
     * 		table.
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

    // get the variable from get
    if ( isset($_GET['id']) && $_GET['id'] != '' ) 
        $id = $_GET['id'];
    else
        echo "error|Could not delete the extra points.";
        
    // query db and return results
    $query = "DELETE FROM servicepoints WHERE ID=$id;";
    if ( $db->query($query) )
        echo "success|$id";
    else
        echo "error|Could not delete the extra points.";
    
    $db->close();
    exit();
?>