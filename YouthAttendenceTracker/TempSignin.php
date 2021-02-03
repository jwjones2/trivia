<?php
    /********************************************************************
     * TempSignin
     *
     * Description:  Used to add, update, and remove entries into the tempsignin
     *      table of the Youth Attendence Tracker.  To keep the signin pages concurrent
     *      and allow for bringing back up a service that the page was closed
     *      prematurely.
     ********************************************************************/
    
    /********************************************************
     * Database Connection section.
     ********************************************************/
    // Connect to the db; use MYSQL_helper.php for mysql login
    require_once('MYSQL_helper.php');
    $db = db_connect( );
    
    // process and store the date submitted by get
    // **ERROR CHECK FUTURE TODO -- have fall back for when date is not submitted.
    $dparts = explode('|', $_GET['date']);  // split first
    $date = "$dparts[2]-$dparts[0]-$dparts[1]";  // rebuild date
        
    // Check GET variables and to determine action needed
    if ( $_GET['flag'] == "here" ) {
        // here was clicked, so check if action is add or remove
        if ( $_GET['action'] == "add" ) {
            // ADD, so insert the row in the tempsignin table and return success or failure
            // Use input GET date for date to insert
            
            // ** Instead of checking if row exists so no duplicates of attendee id with date, do an insert
            // ignore -- table has unique index on date and AttendeeID so insert will fail if duplicated
            $query = 'INSERT IGNORE INTO tempsignin (date, AttendeeID) VALUES ("' . $date . '", "' . $_GET['id'] . '");';
            if ( $db->query($query) ) {
                echo "here|add|success";
            } else {
                echo "here|add|fail";
            }
            
            // close the db and exit            
            $db->close();
            exit();
        }
        
        // here was click so check if action is remove
        if ( $_GET['action'] == "remove" ) {
            // remove the row that corresponds to date in GET and attendee ID in GET
            
        }
    }
    
?>