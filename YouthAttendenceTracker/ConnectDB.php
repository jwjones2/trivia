<?php
    // First connect to the database or display an error
    $db = mysqli_connect('127.0.0.1', 'root', '', 'sfasc');  
	if ( !$db ) {
		// if there is an error, set the $error variable to use later in the page
		// That way at least the page is shown rather than it failing at startup
		$error = "<h1 class='bold red pageContent center'>Error:  There was a problem reading the Database.  Please check back later for the Club Activities information.</h1>";
	}
	
?>
	