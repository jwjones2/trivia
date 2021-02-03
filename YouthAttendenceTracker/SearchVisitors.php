<?php
	error_reporting(0);
	/*******************************************************************
	 * SearchVisitors.php
	 * 
	 * Description:  Identical to SearchStudents.php except returns the DOB
	 * 	along with the students name.
	 *******************************************************************/
	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );
	
	/***
	 * Functionality:
	 *
	 * 1. Get data from GET
	 * 2. Build query.
	 * 3. Execute and return results
	 ***/
	
	include_once('Misc.php'); // for DOB parsing
     
        $return = array();  // results array to return
        
        // (1) get data -- need to parse to only use last word of terms
        $letters = $_GET['term'];
        // parse by splitting by whitespace and using last string/word
        $terms = explode(' ', $letters);
        // set letters to last term
        $letters = $terms[count($terms) - 1];
       
        // (2) build query
        $query = 'SELECT FirstName, LastName, NickName, ID, DOB FROM attendee WHERE FirstName LIKE "%' . $letters . '%";';

        // (3) execute and return
        if ( $result = $db->query($query) ) {
            // package and return the results
            while ( $object = $result->fetch_object() ) {
		/***
		 * build label and value, check NickName for NULL to add to value.
		 ***/
		if ( $object->NickName != "NULL" ) {
		    $label = $object->NickName . ' '. $object->LastName;
		    $value = $object->ID . '|' . date_mysql_to_form($object->DOB) . '|' . $object->FirstName . "|" . $object->LastName . '|' . $object->NickName;
		    array_push($return, array(label=>$label, value=>$value));
		} else {
		    $label = $object->FirstName . ' '. $object->LastName;
		    $value = $object->ID . '|' . date_mysql_to_form($object->DOB) . '|'  . $object->FirstName . "|" . $object->LastName;
		    array_push($return, array(label=>$label, value=>$value));
		}
            }
        }
    
        // return the results and close db and exit
        echo json_encode($return);
        $db->close();
        exit();
?>