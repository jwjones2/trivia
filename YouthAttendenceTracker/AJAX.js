/*******************************************************************************
 * AJAX.js
 *
 * Description:  This Javascript file contains the basic setup for creating
 *	the XMLHttpRequest object for developing AJAX-powered pages.  It performs
 *	browser sniffing and then creates the object.
 *
 * This code was copied from Beginning Ajax with PHP by Lee Babin, page 21.
 * UPDATE -- CODE TAKEN FROM W3SCHOOLS AJAX TUTORIAL.  PREVIOUS CODE APPEARS
 * 		BROKE IN IE9.
 *******************************************************************************/
if (window.XMLHttpRequest)
{// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
}
else
{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}


// Function for return
xmlhttp.onreadystatechange=function()
{	
	if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
		// First split the return string by '|'
		var ret = xmlhttp.responseText.split('|');
		
		// First check that there was not an error, if so alert and return
		if ( ret[0] == 'error' )
		{
			alert(ret[1]);
			return;
		}
		
		// Use function to test for other conditions...
		// CHECK FOR "toggleactive" to prompt and reload the AttendeeGridView page
		if ( ret[0] == "toggleactive" ) {
			alert(ret[1]);                 // alert the passed message
			window.location.reload(true);  // reload the page
			return;                        // exit out of the function so next code doesn't execute
		}
		
		// CHECK FOR "here" to alert user of action returned from clicking here in page
		if ( ret[0] == "here" ) {
			if ( ret[2] == "add" ) 
			alert("changed here, now check");
			return;
		}
		
		// CHECK FOR a returned student
		if ( ret[0] == "student" ) {
			// use addStudentRow to add to page
			addStudentRow(ret);
			
			// Need to check if the student added was added as a visitor by
			//   checking visitorAddId
			if ( visitorAddId != 0 )  // if visitorAddId is zero then the student was added rather than a visitor of a student
			{
				var visitorNameLocation = document.getElementById('Visitor_' + visitorAddId);    // location to write visitor's name in page
				
				// Check for nickname and write the name to the page
				if ( ret[4] != 'add' && ret[4] != 'noadd' ) { // no NickName since  
					// create a text node with the name of the student, then a delete button
					// place both in a div and append to location in page referenced by visitorNameLocation
					var div = document.createElement('div');
					div.setAttribute('id', ret[1]+ret[2]+ret[3]);
					var visitorName = document.createTextNode(ret[2] + ' "' + ret[4] + '" ' + ret[3]); 
					div.appendChild(visitorName);
					
					/* ADD A DELETE BUTTON FOR THE VISITOR */
					var delete_button = document.createElement('input');
					delete_button.setAttribute('type', 'button');
					delete_button.setAttribute('value', 'X');
					delete_button.setAttribute('class', 'visitor-delete-button');
					delete_button.setAttribute('onclick', 'deleteStudentVisitor("Visitor_' + visitorAddId + '", "' + ret[1]+ret[2]+ret[3] + '", ' + ret[1] + ')');
					
					// add delete button to page
					div.appendChild(delete_button);
					
					// add the div to visitorNameLocation
					visitorNameLocation.appendChild(div);
				} else {
					// create a text node with the name of the student, then a delete button
					// place both in a div and append to location in page referenced by visitorNameLocation
					var div = document.createElement('div');
					div.setAttribute('id', ret[1]+ret[2]+ret[3]);
					var visitorName = document.createTextNode(ret[2] + ' ' + ret[3]); 
					div.appendChild(visitorName);
					
					/* ADD A DELETE BUTTON FOR THE VISITOR */
					var delete_button = document.createElement('input');
					delete_button.setAttribute('type', 'button');
					delete_button.setAttribute('value', 'X');
					delete_button.setAttribute('class', 'visitor-delete-button');
					delete_button.setAttribute('onclick', 'deleteStudentVisitor("Visitor_' + visitorAddId + '", "' + ret[1]+ret[2]+ret[3] + '", ' + ret[1] + ')');
					
					// add delete button to page
					div.appendChild(delete_button);
					
					// add the div to visitorNameLocation
					visitorNameLocation.appendChild(div);
				}
			}
			
			if ( visitorAddId != 0 ) {
				// NOW, increment the visitors value for the student and call toggleSigninValue to process the value in temp_signin
				document.getElementById(attendeeId + '_Visitors').value = ++document.getElementById(attendeeId + '_Visitors').value;
				/*** NOT COMPATIBLE WITH OLD VERSION!! ***/
				toggleSigninValue(attendeeId + "_Visitors");
			}
		
			return;  
		}
		
		
		/*** remove student section
		 * if ret[0] == "removestudent" then alert success or failure
		 *  **if failure, the page needs refreshed by calling again with date
		 *  **since the student that was deleted by still be in the table due
		 *  **to the procedure of deleting a visitor on the page.
		 ***/
		if ( ret[0] == "removestudent" ) {
			if ( ret[1] == "true" ) {
				sMessage.show("Removed.");
				setTimeout('sMessage.hide()', 2000);
			} else {
				alert("There was an error with removing student from the database. The page will reload.");
				// reload the page by calling page with date again
				
			}
		}
		
		if ( ret[0] == "update" ) {
			if ( ret[1] == "true" ) {
				if ( sMessage )
					sMessage.hide();
			} else {
				sMessage.show(ret[1]);
				setTimeout('sMessage.hide()', 2000);
			}
			
			return;
		}
		
		/***************************************************************
		 * PROCESS THE AJAX RESPONSE
		 *
		 * If ret[0] == 'error', then alert the error and return.
		 * Else, check for nickname (ret[3]) and print the visitor's name in
		 * 	the Visitor signup sheet section of the student that brought them
		 * 	and then call addVisitorRow passing the correct location to add the
		 * 	row; location derived from visitorAddId.
		 **************************************************************/
		var visitorNameLocation = document.getElementById('Visitor_' + visitorAddId);    // location to write visitor's name in page
		var visitorRowLocation = document.getElementById('VisitorAdd_' + visitorAddId);  // location in page to add visitor row
		
		// create a break tag to use in adding visitor
		var br = document.createElement("br");
		
		if ( visitorAddId != 0 )  // if visitorAddId is zero then the visitor was added for the team and not a particular student
		{
			// Check for nickname and write the name to the page
			if ( ret[3] != 'add' && ret[3] != 'noadd' )  // no NickName since  
			{
				// create a text node with the name of the student, then a delete button
				// place both in a div and append to location in page referenced by visitorNameLocation
				var div = document.createElement('div');
				div.setAttribute('id', ret[0]+ret[1]+ret[2]);
				var visitorName = document.createTextNode(ret[1] + ' "' + ret[3] + '" ' + ret[2]); 
				div.appendChild(visitorName);
				/* ADD A DELETE BUTTON FOR THE VISITOR */
				var delete_button = document.createElement('input');
				delete_button.setAttribute('type', 'button');
				delete_button.setAttribute('value', 'X');
				delete_button.setAttribute('class', 'visitor-delete-button');
				delete_button.setAttribute('onclick', 'deleteVisitor("Visitor_' + visitorAddId + '", "' + ret[0]+ret[1]+ret[2] + '", ' + ret[0] + ')');
				// add delete button to page
				div.appendChild(delete_button);
				
				// add the div to visitorNameLocation
				visitorNameLocation.appendChild(div);
			}
			else
			{
				// create a text node with the name of the student, then a delete button
				// place both in a div and append to location in page referenced by visitorNameLocation
				var div = document.createElement('div');
				div.setAttribute('id', ret[0]+ret[1]+ret[2]);
				var visitorName = document.createTextNode(ret[1] + ' ' + ret[2]); 
				div.appendChild(visitorName);
				/* ADD A DELETE BUTTON FOR THE VISITOR */
				var delete_button = document.createElement('input');
				delete_button.setAttribute('type', 'button');
				delete_button.setAttribute('value', 'X');
				delete_button.setAttribute('class', 'visitor-delete-button');
				delete_button.setAttribute('onclick', 'deleteVisitor("Visitor_' + visitorAddId + '", "' + ret[0]+ret[1]+ret[2] + '", ' + ret[0] + ')');
				// add delete button to page
				div.appendChild(delete_button);
				
				// add the div to visitorNameLocation
				visitorNameLocation.appendChild(div);
			}
		}
		
		// Add the visitor row by calling addVisitorRow if ret[3] || ret[4] == 'add'
		if ( ret[3] == 'add' || ret[4] == 'add' )  // have to check 3 and then 4 because of number of elements passed back by php script
			addVisitorRow(ret, visitorRowLocation);
		
		// NOW, increment the visitors value for the student and call toggleSigninValue to process the value in temp_signin
		document.getElementById(attendeeId + '_Visitors').value = ++document.getElementById(attendeeId + '_Visitors').value;
		/*** NOT COMPATIBLE WITH OLD VERSION!! ***/
		toggleSigninValue(attendeeId + "_Visitors");
		
		// Finally, call clickSignin so that the value will be changed in the tempsignin table
		//***UPDATE--NOT USING THIS ANYMORE -- clickSignIn(ret[0], "Visitors");
	}
}


