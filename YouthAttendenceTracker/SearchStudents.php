<?php
	error_reporting(0);
	/*******************************************************************
	 * SearchStudents.php
	 *
	 * Description:  Queries database for matches of the letters passed
	 *      in Get.  Uses SQL for matching and returns results.  Packages
	 *      results in an array and json encodes for jquery autocomplete.
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

        $return = array();  // results array to return

        // (1) get data -- need to parse to only use last word of terms
        $letters = $_GET['term'];
        // parse by splitting by whitespace and using last string/word
        $terms = explode(' ', $letters);
        // set letters to last term
		$letters = $terms[count($terms) - 1];
		
		// NEW - 2.1.2021 - Added an optional "all" boolean to search for all students or just the active students
		$searchAll = true;
		if ( isset($_GET['all']) && $_GET['all'] != "" ) {
			if ( $_GET['all'] == "true" ) {
				$searchAll = true;
			} else {
				$searchAll = false;
			}
		}

		// (2) build query
		if ( $searchAll ) {
			$query = 'SELECT FirstName, LastName, NickName, ID FROM attendee WHERE ( FirstName LIKE "%' . $letters . '%" OR LastName LIKE "%' . $letters . '%" OR NickName LIKE "%' . $letters . '%");';
		} else {
			$query = 'SELECT FirstName, LastName, NickName, ID FROM attendee WHERE ( FirstName LIKE "%' . $letters . '%" OR LastName LIKE "%' . $letters . '%" OR NickName LIKE "%' . $letters . '%") AND Active="yes";';
		}

        // (3) execute and return
        if ( $result = $db->query($query) ) {
            // package and return the results
            while ( $object = $result->fetch_object() ) {
		/***
		 * build label and value, check NickName for NULL to add to value.
		 ***/
		if ( $object->NickName != "NULL" ) {
		    $label = $object->FirstName . ' "' . $object->NickName . '" '. $object->LastName;
		    $value = $object->ID . '|' . $object->FirstName . "|" . $object->LastName . '|' . $object->NickName;
		    array_push($return, array(label=>$label, value=>$value));
		} else {
		     $label = $object->FirstName . ' '. $object->LastName;
		    $value = $object->ID . '|' . $object->FirstName . "|" . $object->LastName;
		    array_push($return, array(label=>$label, value=>$value));
		}
            }
        }

        // return the results and close db and exit
        echo json_encode($return);
        $db->close();
        exit();
?>
