<?php
	/***************************************************
	 * AttendeeGridView.php
	 *
	 * Description:  Shows all the active students in the
	 * 	database and allows for editing, setting active,
	 * 	etc.
	 ***************************************************/
	
	// output the header
	$title = "Student List";
	require_once('Header.php');
?>

		<table class="ListViewTable">
			<tr>
				<th align="left">Picture</th>
				<th align="left">Name</th>
				<th align="left">Birthday</th>
				<td align="left" class="right"><input type="text" id="search-students" value="Search Students" onclick="$(this).val('')" /></td>
			</tr>
			<?php          
				/**
					Get the rows from Attendee (fields:  ID, PictureURL, FirstName, LastName, and DOB)
					and print a tr with field.  Order by Last name using SQL query.
					
					UPDATE -- Check for GET variable 'showactive', if not set is false as default
				 **/
				if ( isset($_GET['showinactive']) && $_GET['showinactive'] == "true" ) 
					$query = "SELECT ID, PictureURL, FirstName, LastName, DOB, Sex, Active FROM attendee ORDER BY LastName";    // construct the query
				else
					$query = "SELECT ID, PictureURL, FirstName, LastName, DOB, Sex, Active FROM attendee WHERE Active='yes'  ORDER BY LastName";    // construct the query
					
    	  			if ( $result = $db->query($query) )  // query the db and get the result object (mysqli_result)
    	  			{  
    	  				// counter for styling every other row
    	  				$counter = 0;
    	  				while ( $data = $result->fetch_object() ) 
    	  				{  // get row as object
    	  					if ( $counter % 2 == 0 ) // even row
    	  						echo '<tr class="even">'; 			              
    	  					else
    	  						echo '<tr>';
    	  					// check if picture is null and print alternative if so 
    	  					if ( $data->PictureURL == 'NULL' )
    	  					{
    	  						if ( $data->Sex == 'female' ) 
    	  							echo '<td><img src="./profile_pictures/girl-icon.png" width="60px" height="60px" id="picture" /></td>';
    	  						else
    	  							echo '<td><img src="./profile_pictures/boy-icon.png" width="60px" height="60px" id="picture" /></td>';
    	  					}
    	  					else
    	  						printf('<td><img src="./%s" id="picture" /></td>', $data->PictureURL);       // Put the picture at start of record
    	  					printf('<td>%s %s</td>', $data->FirstName, $data->LastName);                 // Print the full name:  FirstName LastName
    	  					printf('<td>%s</td>', $data->DOB);				             // Print the DOB
    	  					printf('<form name="ViewProfile" id="ViewProfile" method="POST" action="./ViewProfile.php"><input type="hidden" id="ID" name="ID" value="%s" />', $data->ID); // set up form to submit the ID to ViewProfile when button is clicked
    	  					echo '<td><input name="submit" id="formSubmit" type="submit" value="View Profile" /></td></form>';  // print the view profile button
    	  					// print the SetActive button or the Inactive? button depending on value of Active
    	  					if ( $data->Active == "yes" ) 
								printf('<td><input type="button" value="Inactive?" onclick="inactivate(%d)" /></td>', $data->ID);
							else
								printf('<td><input type="button" value="Set Active" onclick="setActive(%d)" /></td>', $data->ID);
    	  					echo '</tr>';  							     // end the row and the form
    	  					
    	  					$counter++;  // increment the counter
    	  				}
    	  			}
		    	  ?>
		</table>
	
	<!-- show inactive students button -->
	<div class="show-inactive"><input type="button" value="Show Inactive Students" onclick="window.location = window.location + '?showinactive=true';" /></div>
	
	<script>		
		$( "#search-students" ).autocomplete({
		    minLength: 1,
			delay: 500,
			source: function( request, response ) {
				lastXhr = $.ajax({
					url: "./SearchStudents.php",
					dataType: "json",
					data: {
						term: $("#search-students").val().trim(),
					},
					success: function( data, status, xhr ) { 
					    if (xhr === lastXhr) {
					         response( $.map( data, function( item ) {
					            return {
					                label: item.label,
					                value: item.value,
					                id: item.id
					            };
					        }));
					    }
					}
				});
			},
			focus: function ( event, ui ) {
				event.preventDefault();
				$('#search-students').val(ui.item.label);
			},
			search: function ( event, ui ) {
				$("#search-students").css("background-image", "url('./site_images/ajax-loader.gif')");
				$("#search-students").css("background-position", "right");
				$("#search-students").css("background-repeat", "no-repeat");
			},
		    select: function ( event, ui ) {
				$("#search-students").css("background-image", "url('')");
				// split value and use id to set hidden value in page and submit the form
				var data = ui.item.value.split('|');
				$('#ID').val(data[0]);
				$('#formSubmit').trigger("click");
		    }
		});
		
		$("#search-students").keypress( function ( e ) {
			if (!e) e = window.event;   
			if (e.keyCode == '13'){
			  $('#quick-add').autocomplete('close');
			  $("#quick-add").css("background-image", "url('')");
			  $('#quick-add').val("");
			  return false;
			}
		});
	</script>
	
	</body>
</html>
