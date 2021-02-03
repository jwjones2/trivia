/*******************************************
 * BACKUPS SECTION
 *******************************************/
 function checkForBackups () {
	 // run ajax to call CheckRestore.php first
	 $.ajax({
		 type: "GET",
		 url: "CheckRestore.php"
	 })
	 .done ( function ( msg ) {
		 // split the msg parts and process by removing student or alerting error
		 var ret = msg.split('|');

		 if ( ret[0] == "error" ) {
			 message.show(ret[1], "", "error");
		 } else if ( ret[0] == "update") {
			 $('.start-message').html("There is an update to the database... Getting that now.");
			 getBackup();
		 } else if ( ret[0] == "none" ) {
			 $('.start-message').html("There was no new updates...  Loading the program.");
			 setTimeout("redirect(\'home.php\')", 1500)
		 }
	 });
 }

 function getBackup () {
	 // run ajax to call CheckRestore.php first
	 $.ajax({
		 type: "GET",
		 url: "Restore.php"
	 })
	 .done ( function ( msg ) {
		 // split the msg parts and process by removing student or alerting error
		 var ret = msg.split('|');

		 if ( ret[0] == "error" ) {
			 message.show(ret[1], "", "error");
		 } else {
			 $('.start-message').html("The Database was updated!  Redirecting to the program...");
			 setTimeout("redirect(\'home.php\')", 1500)
		 }
	 });
 }

 function redirect ( url ) {
	 window.location = url;
 }
