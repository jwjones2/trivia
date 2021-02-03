/************************************************************
 * function showUpdateRecordsDialogue
 *
 * Description:  Starts the update records process by showing
 * 		a message on screen and calling restore.
 *
 * Input variable:  n/a
 *
 * Return:  n/a
 ************************************************************/
function showUpdateRecordsDialogue ( ) {
	var tag = '#update-records-dialogue';
	$(tag).html("Checking for any updates to the database.<br />Please wait.<br />");
	$(tag).fadeIn();

	$.ajax({
		type: "GET",
		url: "CheckRestore.php"
	})
	.done ( function ( msg ) {
		var ret = msg.split('|');

		if ( ret[0] == "success" ) {
			$(tag).html("There is an update to the database.<br />Attempting to update the records...<br />")
			var go = setInterval('addPeriod("' + tag + '")', 500);
			// call the server again to do the actual restore
			$.ajax({
				type: "GET",
				url: "Restore.php"
			})
			.done ( function ( msg ) {
				var ret = msg.split('|');

				if ( ret[0] == "success" ) {
					$(tag).html("The database is updated!");
					$(tag).delay(5000).fadeOut();
					clearInterval(go);
				} else {
					message.show(ret[1], "", "error");
				}
			});
		} else if ( ret[0] == "none" ) {
			$(tag).html("The database is up to date!");
			$(tag).delay(5000).fadeOut();
		} else {
			message.show(ret[1], "", "error");
		}
	});
}

function addPeriod ( id ) {
	$(id).html($(id).html() + '.');
}
