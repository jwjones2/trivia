var xmlhttp = null;

if (window.XMLHttpRequest)
{// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
}
else
{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}

/***
 * getResponseObject -- used to get a new object, to renew essentially for IE7
 ***/
function getResponseObject () {
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	} else {// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

var teamIdAdd;                // a global variable to use to pass the team id of the team to add a new visitor to to addVisitorAjax
var attendeeId;                // a global variable to keep track of which attendee is adding a visitor
var visitorAddId = 0;        // the global variable set by showAddVisitor and used by the AJAX response to add a visitor to the SignUp page
var pickStudentContent;  // the global variable to hold the select box for picking a student to add to singup list
var pickVisitorContent;    // the global variable to hold the select box for picking a visitor to add to signup list

var fl0 = false; // for add visitor toggle, to only run once

// global variable to use for showing and hiding search message
var sMessage = new Message("sMessage");
sMessage.setStyle("top: 5px; left: 50%;margin-left: -50px; z-index: 999;");
var ms = new Message("ms");
ms.setStyle("top: 5px; left: 50%;margin-left: -50px; z-index: 999;");

/*******************************************************************************
 * Function addVisitorAjax
 *
 * Description:  Used to add a visitor on the fly for SignUp.php using AJAX.
 *	This is done by building a string to use in POST, to send to PHP page.
 *	The string is built from the form elements.  The function also performs
 *	validation to make sure First and Last Name are not blank and are strings
 *	and also that DOB has been entered.
 *
 * Input variable: n/a
 *
 * Return type: n/a
 ******************************************************************************/
function addVisitorAjax ()
{
	/*  FIRST CALL hideVisitorAdd to clear the page while the request is being processed */
	hideAddVisitor();
	
	/* BUILD THE POST STRING FROM FORM ELEMENTS */
	var postString = "";   // variable to store post string to send
	
	// set the teamId value of postString
	postString += "teamId=" + teamIdAdd + "&";
	
	// set the pickVisitor value
	postString += "pickVisitor=" + document.getElementById('pickVisitor').value + "&";
	
	/* FIRST CHECK THAT THE VISITOR IS NOT ALREADY IN DB, I.E. pickVisitor != "newVisitor"
	   If set postValue of pickVisitor and send the request */
	if ( document.getElementById('pickVisitor').value != 'newVisitor' ) 
	{
		// use ajaxCallPrepare_POST to do the request
		ajaxCallPrepare_POST("./AddVisitor.php", postString);
		
		return;  // exit function since done with request
	}
		
	
	postString += "FirstName=" + document.getElementById('FirstName').value + "&";
	postString += "LastName=" + document.getElementById('LastName').value + "&";
	postString += "MiddleName=" + document.getElementById('MiddleName').value + "&";
	postString += "NickName=" + document.getElementById('NickName').value + "&";
	postString += "DOB=" + document.getElementById('dobYear').value + "-" + document.getElementById('dobMonth').value + "-" + document.getElementById('dobDay').value + "&";
	if ( document.getElementById('male').checked )
		postString += "Sex=male&";
	else if ( document.getElementById('female').checked )
		postString += "Sex=female&";
	else
	{
		alert("You must select a Sex:  male or female.");
		return;
	}
	postString += "Email=" + document.getElementById('Email').value + "&";
	postString += "CellPhone=" + document.getElementById('CellPhone').value + "&";
	if ( document.getElementById('CanReceiveTxt').checked )
		postString += "CanReceiveTxt=yes&";
	else
		postString += "CanReceiveTxt=no&";
	if ( document.getElementById('Facebook').checked )
		postString += "Facebook=yes&";
	else
		postString += "Facebook=no&";
	postString += "StreetAddress=" + document.getElementById('StreetAddress').value + "&";
	postString += "Zip=" + document.getElementById('Zip').value + "&";
	postString += "Notes=" + document.getElementById('Notes').value + "&";
	postString += "BroughtBy=" + attendeeId + "&";
	
	/* VALIDATE THE APPROPRIATE FIELDS */
	// FirstName
	var firstname = document.getElementById('FirstName').value;
	if ( !validateBlank(firstname) ) 
	{
		alert("First name cannot be blank.");
		return;  // break out of function
	}
	
	if ( !validateString(firstname) )
	{
		alert("First name must be a string.");
		return;  // break out of function
	}
	
	// LastName
	var lastname = document.getElementById('LastName').value;
	if ( !validateBlank(lastname) ) 
	{
		alert("Last name cannot be blank.");
		return;  // break out of function
	}
	
	if ( !validateString(lastname) )
	{
		alert("Last name must be a string.");
		return;  // break out of function
	}
	
	// DOB must be changed from default
	var d = new Date();  // date object to use to get the year
	if ( document.getElementById('dobYear').value == d.getFullYear() )  // year has not been changed
	{
		alert("You must enter a date of birth.");
		return;  // break out of function
	}
		
	// use ajaxCallPrepare_POST to do the request
	ajaxCallPrepare_POST("./AddVisitor.php", postString);
}

var fieldCounter; // global variable to use to keep track of last field counter written to sign-up page

/*******************************************************************************
 * Function addVisitorRow
 *
 * Description:  Adds a new visitor to the team of the student that brought the visitor.
 *	This is done by creating the row using DOM elements and then appending the row
 *	as a child to the teams table using teamID to get the table in the DOM.
 *
 * Input variable: Array of Visitor information, document element where to append child (row)
 *
 * Return type: n/a
 ******************************************************************************/
function addVisitorRow ( data )
{
	var tr = document.createElement('tr');   // first create the tr to eventually append to table
	
	var td = document.createElement('td');   // create the first td to add to tr
	
	var txt = "";   // variable to use to hold text of td as building
	
	/* Name */
	txt += data[1] + ' ';  // the first name
	// check for middle name and write if set
	if ( data[3] != 'add' && data[3] != 'noadd' )    // there is a nick name since it is not set to add or noadd (add and noadd are last element sent from php script, if data[3] set to them then there is no nickname
		txt += '"' + data[3] + '" ';
	txt += data[2];        // the last name
	var tNode = document.createTextNode(txt);  // create a text node with content of td
	td.appendChild(tNode);                     // append the text node to td
	tr.appendChild(td);                        // append td to tr
	
	/* Here */
	td = document.createElement('td');            // create a new td
	var input = document.createElement('input');  // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('name', data[0] + '_Here');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('onclick', 'enableFields(' + fieldCounter + ')');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* SunSch */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('name', data[0] + '_SunSch');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('id', ++fieldCounter);
	input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* SunAM */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('name', data[0] + '_SunAM');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('id', ++fieldCounter);
	input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* SunPM */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('name', data[0] + '_SunPM');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('id', ++fieldCounter);
	input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* Bible */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('name', data[0] + '_Bible');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('id', ++fieldCounter);
	input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* Visitor */
	td = document.createElement('td');            // create a new td
	var div = document.createElement('div');      // div for visitor section
	// set the divs attributes
	div.setAttribute('id', 'Visitor_' + ++fieldCounter);
	div.setAttribute('class', 'vistorsAdded');
	td.appendChild(div);                          // append the div to td
	
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'hidden');
	input.setAttribute('name', data[0] + '_Visitors');
	input.setAttribute('id', fieldCounter);
	input.setAttribute('value', '0');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* Extra Points */
	td = document.createElement('td');
	input = document.createElement('input');
	input.setAttribute('type', 'text');
	input.setAttribute('name', data[0] + '_ExtraPoints');
	input.setAttribute('class', 'signup_textbox');
	input.setAttribute('value', '0');
	td.appendChild(input);
	tr.appendChild(td);
	
	/* APPEND THE TR TO TABLE USING teamIdAdd as reference */
	document.getElementById('signup_table').appendChild(tr);
}

// uses AJAX to pass id to ToggleActivate.php, also passes flag inactive
function inactivate ( id ) {
	/* OPEN AND SEND THE POST REQUEST */
	var url = "./ToggleActivate.php?flag=inactive&id=" + id;
	ajaxCallPrepare_GET(url);
}

// uses AJAX to pass id to ToggleActivate.php, also passes flag inactive
function setActive ( id ) {
	/* OPEN AND SEND THE POST REQUEST */
	var url = "./ToggleActivate.php?flag=active&id=" + id;
	ajaxCallPrepare_GET(url);
}

/*******************************************************************************
 * Function showAddVisitor
 *
 * Description: 
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function showAddVisitor ( aId, teamId )
{
	visitorAddId = aId;    // set global variable for use in adding visitor to page by AJAX response
	teamIdAdd = teamId;   // set global variable for use with addVisitorAjax
	attendeeId = aId;         // set the gloabal variable to keep track of which student is adding the visitor
	
	// get reference to the divs to change transparency
	var div1 = document.getElementById('popup');
	
	div1.style.display = "block";
	
	if ( navigator.appName == "Microsoft Internet Explorer" )
	{
		div1.style.top = document.documentElement.scrollTop;
		div1.style.left = document.documentElement.scrollLeft;
	}
	else
	{
		div1.style.top = window.scrollY + "px";
		div1.style.left = window.scrollX + "px";
	}
}

/*******************************************************************************
 * Function hideAddStudent
 *
 * Description:  Hides the entire student/visitor form section.
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function hideAddStudent ()
{
	// get reference to the divs to change transparency
	var div1 = document.getElementById('popup');
	
	div1.style.display = "none";
}

/*******************************************************************************
 * Function hideAddVisitor
 *
 * Description: Hides the new visitor form section.
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function hideAddVisitor ()
{
	// get reference to the divs to change transparency
	var div1 = document.getElementById('add-visitor-form');
	
	div1.style.display = "none";
}

/*******************************************************************************
 * Function customDate
 *
 * Description: Sets the date value of form for submitting service signups.
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function customDate ()
{
	// variables for function
	var month = -1;                // to hold month
	var days = -1;                 // to hold days
	var year = -1;                 // to hold year
	var isDigit = /^[0-9]+$/;      // regex to validate is an integer
	var d = new Date();            // date object
	var currentYear = d.getFullYear(); // to get the current year to validate year
	// month names for displaying to user
	var m_names = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	
	// prompt for month, validating range and if it is an integer	
	while ( month < 1 || month > 12 || !isDigit.test(month) )
	{
		month = prompt('Please enter a month (01 - 12):');
	}
	
	// prompt for days, validating range and if it is an integer	
	while ( days < 1 || days > 31 || !isDigit.test(days) )
	{
		days = prompt('Please enter a day (01 - 31):');
	}
	
	// prompt for year, validating range and if it is an integer	
	while ( year < 1990 || year > currentYear || !isDigit.test(year) )
	{
		year = prompt('Please enter a year (1990 - ' + currentYear + '):');
	}
	
	// create two date formats, one for displaying on form to user and the other for the form to submit
	var displayDate = m_names[month-1] + ' ' + days + ', ' + year;
	var formDate = year + '-' + month + '-' + days;
	
	// write the display date to the page
	document.getElementById('displayDate').innerHTML = displayDate;
	
	// write the form date to the form's hiddent field 'Date'
	document.getElementById('Date').value = formDate;
}

/*************************************************************
 * function deleteVisitor
 *
 * Description:  Deletes a visitor from the signup page.  Simply
 * 		removes node and decrements the visitory number.
 *
 * Input variables:  DOM obj (parent node of visitor to remove), DOM obj (node to remove), string (Visitory Id to decrement value))
 *
 * Return type: n/a
 ************************************************************/
function deleteVisitor ( pId, cId, vId ) {
	// remove the visitor section
	var p = document.getElementById(pId);
	var c = document.getElementById(cId);
	p.removeChild(c);
	
	// decrement the value in page
	var el = document.getElementById(vId);
	el.value = --el.value;
}

/*** SECTION FOR TEMP SIGNIN VALUE CHANGING ***/

/*************************************************************
 * function here
 *
 * Description:  Checks whether the here checkbox is checked or
 * 		not and then calls TempSignin.php accordingly.  Reason
 * 		this function checks value of here is to simplify calling it
 * 		from the signin page.  It is passed the id of the student on
 * 		which to operate.
 *
 * Input variables:  n/a
 *
 * Return type: n/a
 ************************************************************/
function here ( id ) {
	// get the date from the page to send to TempSignin.php
	var date = processDate(document.getElementById('displayDate').innerHTML);
	
	// get reference to checkbox object in page using id and "_Here"
	var checkbox = document.getElementById(id+"_Here");
	
	// the url to call to request from PHP page
	var url = "./TempSignin.php?flag=here&id=" + id + "&date=" + date;
	
	// now check the status of checked and build url to send
	/*** FUTUTURE UPDATE??? THIS IS REALLY REDUNDANT
	 * The php page can check for existence of a row and remove it
	 * if it exists (technically calling here results in toggling here or not
	 * here).  At this time will do this check, however, to ensure no other
	 * unforseen variables exist to where either the page or the script
	 * triggers adding or removing an entry out of place.
	 ***/
	if ( checkbox.checked ) {
		// send "action=add" since checkbox is checked
		url += "&action=add";
	} else {
		// send "action=remove" since checkbox is not checked
		url += "&action=remove";
	}
	
	// make the request	
	ajaxCallPrepare_GET(url);
}

/** Processes a date in format January 1, 1999 to MM|DD|YY format */
function processDate ( date ) {
	// split the current date up to process
	var parts = date.split(', ');         // parts contains [0] = January 1, [1] = 1999
	var fparts = parts[0].trim().split(' ');   // fparts contains [0] = January, [1] = 1

	// months array to parse full month name
	var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	
	// convert months to numeric
	var month = months.indexOf(fparts[0]) + 1;  // add one since Months are stored zero-based in array
	
	// now concatenate and return
	return month + "|" + fparts[1] + "|" + parts[1].trim();
}

/*  removes leading and trailing whitespace */
String.prototype.trim  = function ( ) {
	// code from http://www.somacon.com/p355.php
	return this.replace(/^\s+|\s+$/g, "");
}

/* checks for index of an input element of an array */
if ( !Array.prototype.indexOf ) {
	Array.prototype.indexOf = function ( search ) {
		for ( i = 0; i < this.length; i++ ) {
			if ( search == this[i] ) {
				// if match short-circuit and return i
				return i;				
			}
		}
		
		// hasn't returned so match found, return -1
		return -1;
	}
}

/******************************************************************
 * function clickSignIn
 *
 * Description:  Calls TempSignin to do an update on a value in the
 * 	signin page.
 *
 * Input variables:  string (attendee id), string (value that was changed)
 *
 * Return type:  n/a
 ******************************************************************/
function clickSignIn ( id, value ) {
	// the url to call to request from PHP page
	var url = "./TempSignin.php?flag=update&id=" + id + "&date=" + date + "&valueId=" + value;
	
	// check if the value is from a checkbox or other input type and set action accordingly
	if ( value == "_SunSch" || value == "_SunAM" || value == "_SunPM" || value == "_Bible" ) {
		url += "&action=checkbox&value=";
		
		// if checked set value to yes, else no
		if ( document.getElementById(id + value).checked )
			url += "yes";
		else
			urel += "no";
	} else if ( value == "_Visitors" || value == "_ExtraPoints" ) {
		// other, just need to pass value
		url += "&action=normal&value=" + document.getElementById(id + value).value;
	}
	
	// make the request	
	ajaxCallPrepare_GET(url);
}

/***************************************************************************************************************
 * SIGNIN SECTION
 *
 * Functions for Signin page.  Many will be variations of those above.
 *
 ***************************************************************************************************************/
/*******************************************************************************
 * Function addStudent
 *
 * Description:  Sends the student's id to AddStudent.php script.  No other data
 * 	necessary.
 *
 * Input variable: n/a
 *
 * Return type: n/a
 ******************************************************************************/
function addStudent ()
{
	// first check that add button was not accidentally pushed and default is the value
	if ( document.getElementById('pickVisitor').value == "default" )
		return;  // don't do anything yet since nothing selected
	
	// get student's data from pickVisitor value
	var postString = "pickVisitor=" + document.getElementById('pickVisitor').value;
	
	// also send aId in case this student is being added as a visitor
	postString += "&aId=" + attendeeId;
		
	// use ajaxCallPrepare_POST to do the request
	ajaxCallPrepare_POST("./AddStudent.php", postString);
}

/*******************************************************************************
 * Function addVisitor
 *
 * Description:  Gathers and sends data to add a new Visitor to the program.
 *
 * Input variable: n/a
 *
 * Return type: n/a
 ******************************************************************************/
function addVisitor ()
{
	/* BUILD THE POST STRING FROM FORM ELEMENTS */
	var postString = "";   // variable to store post string to send
	
	// set the pickVisitor value and the visitor add id
	postString += "pickVisitor=" + document.getElementById('pickVisitor').value + "&visitoraddid=" + visitorAddId + "&";
	
	/* FIRST CHECK THAT THE VISITOR IS NOT ALREADY IN DB, I.E. pickVisitor != "newVisitor"
	   If set postValue of pickVisitor and send the request */
	if ( document.getElementById('pickVisitor').value != 'default' ) 
	{
		/***
		 * Build the rest of the POST string from form in page, since using
		 * AJAX, must build it manually.
		 ***/
		// array to hold fields to loop over and get values
		var studentFields = new Array();
		studentFields[0] = "FirstName";
		studentFields[1] = "LastName";
		studentFields[2] = "MiddleName";
		studentFields[3] = "NickName";
		studentFields[4] = "StreetAddress";
		studentFields[5] = "Zip";
		studentFields[6] = "CellPhone";
		studentFields[7] = "CanReceiveTxt";
		studentFields[8] = "Facebook";
		studentFields[9] = "Email";
		studentFields[10] = "Sex";
		studentFields[11] = "DOB";
		studentFields[12] = "Notes";
		
		for ( var i = 0; i < studentFields.length; i++ ) {
			if ( studentFields[i] == "Sex" ) { // check for radio buttons to handle differently
				if ( document.addVisitorForm.Sex[0].checked )
					postString += studentFields[i] + "=male&";
				else
					postString += studentFields[i] + "=female&";
			} else if ( studentFields[i] == "CanReceiveTxt" || studentFields[i] == "Facebook" ) {  // check box
				if ( document.getElementById(studentFields[0]).checked ) 
					postString += studentFields[i] + "=yes&";
				else
					postString += studentFields[i] + "=no&";
			} else if ( studentFields[i] == "DOB" ) {  // have to locget the values from 3 seaparate
				// get month, day, year
				var month = document.getElementById('dobMonth').value;
				var day = document.getElementById('dobDay').value;
				var year = document.getElementById('dobYear').value;
				postString += studentFields[i] + "=" + year + "-" + month + "-" + day + "&";
			} else {
				postString += studentFields[i] + "=" + document.getElementById(studentFields[i]).value + "&";
			}
		}
		
		// use ajaxCallPrepare_POST to do the request
		ajaxCallPrepare_POST("./AddStudent.php", postString);
		
		return;  // exit function since done with request
	}
		
	
	postString += "FirstName=" + document.getElementById('FirstName').value + "&";
	postString += "LastName=" + document.getElementById('LastName').value + "&";
	postString += "MiddleName=" + document.getElementById('MiddleName').value + "&";
	postString += "NickName=" + document.getElementById('NickName').value + "&";
	postString += "DOB=" + document.getElementById('dobYear').value + "-" + document.getElementById('dobMonth').value + "-" + document.getElementById('dobDay').value + "&";
	if ( document.getElementById('male').checked )
		postString += "Sex=male&";
	else if ( document.getElementById('female').checked )
		postString += "Sex=female&";
	else
	{
		alert("You must select a Sex:  male or female.");
		return;
	}
	postString += "Email=" + document.getElementById('Email').value + "&";
	postString += "CellPhone=" + document.getElementById('CellPhone').value + "&";
	if ( document.getElementById('CanReceiveTxt').checked )
		postString += "CanReceiveTxt=yes&";
	else
		postString += "CanReceiveTxt=no&";
	if ( document.getElementById('Facebook').checked )
		postString += "Facebook=yes&";
	else
		postString += "Facebook=no&";
	postString += "StreetAddress=" + document.getElementById('StreetAddress').value + "&";
	postString += "Zip=" + document.getElementById('Zip').value + "&";
	postString += "Notes=" + document.getElementById('Notes').value;
	
	/* VALIDATE THE APPROPRIATE FIELDS */
	// FirstName
	var firstname = document.getElementById('FirstName').value;
	if ( !validateBlank(firstname) ) 
	{
		alert("First name cannot be blank.");
		return;  // break out of function
	}
	
	if ( !validateString(firstname) )
	{
		alert("First name must be a string.");
		return;  // break out of function
	}
	
	// LastName
	var lastname = document.getElementById('LastName').value;
	if ( !validateBlank(lastname) ) 
	{
		alert("Last name cannot be blank.");
		return;  // break out of function
	}
	
	if ( !validateString(lastname) )
	{
		alert("Last name must be a string.");
		return;  // break out of function
	}
	
	// DOB must be changed from default
	var d = new Date();  // date object to use to get the year
	if ( document.getElementById('dobYear').value == d.getFullYear() )  // year has not been changed
	{
		alert("You must enter a date of birth.");
		return;  // break out of function
	}
		
	// use ajaxCallPrepare_POST to do the request
	ajaxCallPrepare_POST("./AddVisitor.php", postString);
}

/*******************************************************************************
 * Function showAddStudent
 *
 * Description: 
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function showAddStudent ( aId )
{
	visitorAddId = aId;    // set global variable for use in adding visitor to page by AJAX response
	attendeeId = aId;     // set the gloabal variable to keep track of which student is adding the visitor
	
	// Fill the content of the pickVisitorStudentSection div to show correct student/visitor choices by using javascript to fill innerHTML
	if ( visitorAddId == 0 ) 	
		document.getElementById('pickVisitorStudentSection').innerHTML = pickStudentContent;
	else
		document.getElementById('pickVisitorStudentSection').innerHTML = pickVisitorContent;
	
	// get reference to the divs to change transparency
	var div1 = document.getElementById('popup');
	
	div1.style.display = "block";
	
	if ( navigator.appName == "Microsoft Internet Explorer" )
	{
		div1.style.top = document.documentElement.scrollTop;
		div1.style.left = document.documentElement.scrollLeft;
	}
	else
	{
		div1.style.top = window.scrollY + "px";
		div1.style.left = window.scrollX + "px";
	}
}

/*******************************************************************************
 * Function addStudentRow
 *
 * Description:  Adds a student to the signin page using DOM elements to append to table.
 *
 * Input variable: Array of Visitor information
 *
 * Return type: n/a
 ******************************************************************************/
function addStudentRow ( data )
{
	var tr = document.createElement('tr');   // first create the tr to eventually append to table
	
	// set the id of the row as StudentRow_ID
	tr.setAttribute('id', 'StudentRow_' + data[1]);
	
	var td = document.createElement('td');   // create the first td to add to tr
	
	var txt = "";   // variable to use to hold text of td as building
	
	/***UPDATE -- Before Name, append a delete button to remove the student
		BUT, only if the visitorAddId is 0, if 0 don't add delete since delete is handled
		by button under add visitor button in student's row that brought visitor.
	 ***/
	if ( visitorAddId == 0 ) {
		var input = document.createElement('input');
		input.setAttribute('id', 'student-row-delete-button');
		input.setAttribute('value', 'X');
		input.setAttribute('class', 'delete-button');
		input.setAttribute('onclick', 'deleteStudent(' + data[1] + ')');
		td.appendChild(input);
	}
	
	/* Name */
	txt += data[2] + ' ';  // the first name
	// check for middle name and write if set
	if ( data[4] != 'add' && data[4] != 'noadd' )    // there is a nick name since it is not set to add or noadd (add and noadd are last element sent from php script, if data[3] set to them then there is no nickname
		txt += '"' + data[4] + '" ';
	txt += data[3];        // the last name
	var tNode = document.createTextNode(txt);  // create a text node with content of td
	// if this is a visitor, color the name green to specify in the table
	if ( visitorAddId != 0 )
		td.setAttribute('style', 'color: Green;');
	td.appendChild(tNode);                     // append the text node to td
	tr.appendChild(td);                        // append td to tr
	
	/***UPDATE -- NO LONGER NEED HERE***/
	/***UPDATE -- NO LONGER NEED BUTTONS DISABLED***/
	
	/* SunSch */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('id', data[1] + '_SunSchAttend');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('onchange', 'toggleSigninValue("' + data[1] + '_SunSchAttend")');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* SunAM */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('id', data[1] + '_SunMornAttend');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('onchange', 'toggleSigninValue("' + data[1] + '_SunMornAttend")');
	//input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* SunPM */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('id', data[1] + '_SunEvenAttend');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('onchange', 'toggleSigninValue("' + data[1] + '_SunEvenAttend")');
	//input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* Bible */
	td = document.createElement('td');            // create a new td
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'checkbox');
	input.setAttribute('id', data[1] + '_Bible');
	input.setAttribute('class', 'signup_checkbox');
	input.setAttribute('onchange', 'toggleSigninValue("' + data[1] + '_Bible")');
	//input.setAttribute('disabled', 'disabled');
	td.appendChild(input);                        // append input to td
	tr.appendChild(td);                           // append td to tr
	
	/* Visitor */
	td = document.createElement('td');            // create a new td	
	input = document.createElement('input');      // create an input element to append to td
	// set the input attributes
	input.setAttribute('type', 'hidden');
	input.setAttribute('id', data[1] + '_Visitors');
	input.setAttribute('value', '0');
	td.appendChild(input);                        // append input to td
	
	// add the "add visitor" button
	input = document.createElement('input');
	input.setAttribute('type', 'button');
	input.setAttribute('id', data[1] + '_Visitor');
	input.setAttribute('value', 'Add Visitor');
	//input.setAttribute('disabled', 'disabled');
	input.setAttribute('onclick', 'showAddStudent(' + data[1] + ')');
	td.appendChild(input);
	// add a div section to hold names of added visitors
	div = document.createElement('div');
	div.setAttribute('id', 'Visitor_' + data[1]);
	div.setAttribute('class', 'visitorsAdded');
	td.appendChild(div);	
	
	tr.appendChild(td);                           // append td to tr
	
	/* Extra Points */
	td = document.createElement('td');
	input = document.createElement('input');
	input.setAttribute('type', 'text');
	input.setAttribute('id', data[1] + '_ExtraPoints');
	input.setAttribute('class', 'signup_textbox');
	input.setAttribute('onchange', 'toggleSigninValue("' + data[1] + '_ExtraPoints")');
	input.setAttribute('value', '0');
	td.appendChild(input);
	tr.appendChild(td);
	
	/* APPEND THE TABLE */
	document.getElementById('signup_table').appendChild(tr);
}

// handles passing the value to php script to update student's
// signin information
function toggleSigninValue ( id ) {
	// first show message to alert user updating value
	sMessage.show('Updating...');
	
	var parts = id.split('_');
	var url = './UpdateStudentSignin.php?id=' + parts[0] + '&field=' + parts[1] + '&value=';
	
	//**check which value to send and append to url
	// checkboxes are yes, no for checked or not (SunSch, SunMorn, SunEven, Bible)
	// Visitors value is in page at ATTENDEEID_Visitors, get value and send
	// Extrapoints value is in page at ATTENDEEID_ExtraPoints, get value and send
	if ( parts[1] == "SunSchAttend" || parts[1] == "SunMornAttend" || parts[1] == "SunEvenAttend" || parts[1] == "Bible" ) {
		if ( document.getElementById(id).checked == 1 )
			url += "yes";
		else
			url += "no";
	} else if ( parts[1] == "Visitors" ) {
		url += document.getElementById(id).value;
	} else if ( parts[1] == "ExtraPoints" ) {
		url += document.getElementById(id).value;
	}
	
	// make the request
	ajaxCallPrepare_GET(url);
}

/*************************************************************
 * function deleteStudentVisitor
 *
 * Description:  Deletes a visitor from the signup page.  Simply
 * 		removes node and decrements the visitory number
 * 		and removes the row of the added student.
 *
 * Input variables:  DOM obj (parent node of visitor to remove), DOM obj (node to remove), string (Visitor Id to decrement value))
 *
 * Return type: n/a
 ************************************************************/
function deleteStudentVisitor ( aId, cId, vId ) {
	if ( confirm("Are you sure you want to delete the student?") ) {
		// remove the visitor section
		var p = document.getElementById("Visitor_" + aId);
		var c = document.getElementById(cId);
		p.removeChild(c);
		
		// decrement the value in page	
		// NOW, decrement the visitors value for the student and call toggleSigninValue to process the value in temp_signin
		document.getElementById(aId + '_Visitors').value = --document.getElementById(aId + '_Visitors').value;
		toggleSigninValue(aId + "_Visitors");
		
		// to finish deleteVisitorOfStudent needs to be called to remove the visitor's row in the table
		/***UPDATE -- NEED TO WAIT SHORT TIME BECAUSE DELETESTUDENT AND TOGGLESIGNINVALUE
		 *                    ARE EXECUTING AJAXCALL SIMULTANEOUSLY...??BETTER SOLUTION?  THE AJAX STACK
		 *                    SYSTEM IS SUPPOSED TO REMEDY THIS NEED, BUT THEY ARE CALLING THAT FUNCTION
		 *                    SIMULTANEOUSLY IT SEEMS.
		 *******************************************************************************************************/
		setTimeout('deleteVisitorOfStudent(' + aId + ', ' + vId + ')', 1000);
		
		ms.popup('Removing...', 900);
	}
}

/*************************************************************
 * function deleteStudent
 *
 * Description:  Removes the student row from the table and
 * 	calls RemoveStudent.php passing the id to remove from
 * 	temp_signin table.
 *
 * Input variables:  int (id of the student to remove)
 *
 * Return type: n/a
 ************************************************************/
function deleteStudent ( id ) {
	if ( confirm("Are you sure you want to delete the student?") ) {
		var el = document.getElementById('StudentRow_' + id);
		el.parentNode.removeChild(el);
	
		var url = './RemoveStudent.php?id=' + id;
	
		ajaxCallPrepare_GET(url);
	}
}

/*************************************************************
 * function deleteVisitorOfStudent
 *
 * Description:  Removes the student row from the table and
 * 	calls RemoveStudent.php passing the attendee's id of the
 * 	student whose visitor is being deleted (so can roll back
 * 	if unsuccessful) and the visitor's id to delete.
 *
 * Input variables:  int, (id of the student whose visitor is being removed), int (id of the student to remove)
 *
 * Return type: n/a
 ************************************************************/
function deleteVisitorOfStudent ( aId, vId ) {
	var el = document.getElementById('StudentRow_' + vId);
	el.parentNode.removeChild(el);
	
	var url = './RemoveStudent.php?aId=' + aId + "&id=" + vId;
	
	ajaxCallPrepare_GET(url);
}

/*************************************************************
 * function submit
 *
 * Description:  Calls Submit.php if user verifies to do the submit.
 *
 * Input variables:  n/a
 *
 * Return type: n/a
 ************************************************************/
function submit ( ) {
	if ( confirm("Are you sure you want to submit the sign in list?") ) {
		ajaxCallPrepare_GET("./Submit.php");
	}
}

/************************************************************
 * function addService
 *
 * Description:  Shows a popup that is used to call Sigin.php
 *     with necessary GET variables to create a new service
 *     and sign in page.
 *
 * Input variables:  string (date of service to add)
 *
 * Return type: n/a
 ************************************************************/
function addService ( date ) {
	alert(date);
}

/************************************************************
 * Function:  setStudentSelect
 *
 * Description:  Takes a value to set the pickVisitor select box
 * 	to.  Then calls addStudent.  This function is a
 * 	sortof quick button for quickly adding students that are
 * 	commonly in attendence.
 *
 * Input variables:  int (index to set select box to)
 *
 * Return type:  n/a
 ************************************************************/
function setStudentSelect ( val ) {
	document.getElementById('pickVisitor').value = val;
	addStudent();
}

/************************************************************
 * EXTRAS
 *
 * EXPERIMENTAL -- Test out function for AJAX call lock.
 * 		Seems that when using multiple AJAX calls
 * 		there are times when calls overlap.  The
 * 		return function is generic.  I could fix with chaning
 * 		that, but it gets too tedious and hard to maintain.
 * 		The generic function listens to all calls and thereby
 * 		ignores or blocks overlapping...??  This is the intial
 * 		assessment.  Try to function that checks for a flag
 * 		variable that is set when AJAX call begins and cleared
 * 		when the response function runs.  This is also a manually
 * 		managed functionality right now so could pose later
 * 		problems.  It should, however, not break anything
 * 		explicitly.
 *
 *************************************************************/

// variables to use
var ajaxCallStack = new Array();
var ajaxCallActive = false;

/**************************************************************
 * Funcion ajaxCallPrepare_GET ()
 *
 * Description:  Takes a string (the url with values of GET variables
 * 	if any) and creates a function that makes the ajax call.
 * 	Push this function (as a variabled) onto the ajaxCallStack
 * 	and then call ajaxLock.
 *
 * Input variables:  string (function call including any variables)
 *
 * Return type:  n/a
 **************************************************************/
function ajaxCallPrepare_GET ( url ) {
	// create a function based on what needs to happed to make
	// the ajax call using the input url to open the request
	var newAjaxFunction = function ( ) {
		xmlhttp = getResponseObject();
		xmlhttp.onreadystatechange = response;
		xmlhttp.open("GET", url, true);	  
		xmlhttp.send(); 
	}
	
	// push onto front of call stack using unshift
	ajaxCallStack.unshift(newAjaxFunction);
	
	// call ajaxLock to do the call and checking
	ajaxLock();
}

/**************************************************************
 * Funcion ajaxCallPrepare_POST ()
 *
 * Description:  Takes a string (the url) and another string (POST variables
 * 	if any) and creates a function that makes the ajax call.
 * 	Push this function (as a variabled) onto the ajaxCallStack
 * 	and then call ajaxLock.
 *
 * Input variables:  string (function call including any variables)
 *
 * Return type:  n/a
 **************************************************************/
function ajaxCallPrepare_POST ( url, postString ) {
	// create a function based on what needs to happed to make
	// the ajax call using the input url to open the request
	var newAjaxFunction = function ( ) {
		xmlhttp = getResponseObject();
		xmlhttp.onreadystatechange = response;
		xmlhttp.open("POST", url,true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");			
		xmlhttp.send(postString); 
	}
	
	// push onto front of call stack using unshift
	ajaxCallStack.unshift(newAjaxFunction);
	
	// call ajaxLock to do the call and checking
	ajaxLock();
}

/**************************************************************
 * Funcion ajaxCall ()
 *
 * Description:  Main function to make an ajax call using the
 * 	ajax call stack method.  This takes a function call
 * 	as a string and appends it to the call stack.  Then it
 *      calls ajaxLock.
 *
 * Input variables:  string (function call including any variables)
 *
 * Return type:  n/a
 **************************************************************/
function ajaxCall ( functionName ) {
	ajaxCallStack.unshift(functionName);
	ajaxLock();
}

/**************************************************************
 * Funcion ajaxLock ()
 *
 * Description:  Checks for the flag (ajaxCallActive):  if set it calls itself
 * 		500 milliseconds later repeating the process.  The function
 * 		to call is on the top of the call stack.
 *
 * Input variables:  n/a
 *
 * Return type:  boolean (called or waited -- true or false)
 **************************************************************/
function ajaxLock () {
	if ( ajaxCallActive ) {
		setTimeout('ajaxLock', 500);
	} else {
		ajaxCallActive = true;
		callNextAjaxFunction();  // use function to call the next function on the stack
	}
}

/***
 * Utitlity functions for ajax lock functionality.
 ***/

function setAjaxFlag ( value ) {
	if ( value === "true" || value === "t" || value === "True" || value === "TRUE" )
		ajaxCallActive = true;
	else if ( value === "false" || value === "f" || value === "False" || value === "FALSE" )
		ajaxCallActive = false;
	else  // default to false to catch bad input
		ajaxCallActive = false;
}

/*******************************************************
 * Function callNextAjaxFunction
 *
 * Description:  Calls the next function on the top of
 * 	the ajaxCallStack array.  Removes the function.
 *
 * Input variables:  n/a
 *
 * Return type:  boolean (true if called, false if no function to call)
 *******************************************************/
function callNextAjaxFunction () {
	// check that function is there, if not return false
	if ( ajaxFunction = ajaxCallStack.shift() ) {
		ajaxFunction();
		return true;
	} else {
		return false;
	}
}

// Function for return
function response ( ) {	
	if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
		//  FIRST -- set the ajax call flag used in ajax call lock system to false since an
		//    active request has now returned
		setAjaxFlag("false");
		
		// First split the return string by '|'
		var ret = xmlhttp.responseText.split('|');
		
		// First check that there was not an error, if so alert and return
		if ( ret[0] == 'error' )
		{
			alert(ret[1]);
			return;
		}
		
		// check for submit
		if ( ret[0] == 'submit' )
		{
			alert(ret[1]);
			window.location.reload(true);  // reload the page
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
					delete_button.setAttribute('onclick', 'deleteStudentVisitor(' + visitorAddId + ', "' + ret[1]+ret[2]+ret[3] + '", ' + ret[1] + ')');
					
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
					delete_button.setAttribute('onclick', 'deleteStudentVisitor(' + visitorAddId + ', "' + ret[1]+ret[2]+ret[3] + '", ' + ret[1] + ')');
					
					// add delete button to page
					div.appendChild(delete_button);
					
					// add the div to visitorNameLocation
					visitorNameLocation.appendChild(div);
				}
			
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
				sMessage.popup("Removed.", 2000);
				// show that service is now not submitted
				setSubmitted(false);
			} else if ( ret[1] == "error" ) {
				alert(ret[2]);
			} else if ( ret[1] == "removeerror" ) {
				// alert the returned error
				alert(ret[2]);
				
				// reload the page by calling page with date again
				window.location = window.location;
			}else {
				// alert 
				alert("There was an error with removing student from the database. The page will reload.");
				
				// reload the page by calling page with date again
				window.location = window.location;
			}
			
			return;
		}
		
		if ( ret[0] == "update" ) {
			if ( ret[1] == "true" ) {
				if ( sMessage )
					sMessage.hide();
				
				// check return for service submitted update
				if ( ret[2] == "true" )  // service was set to submitted="no"
					setSubmitted(false);
				else
					setSubmitted();
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

function repopulateStudents () {
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
			
				// NOW, increment the visitors value for the student and call toggleSigninValue to process the value in temp_signin
				document.getElementById(attendeeId + '_Visitors').value = ++document.getElementById(attendeeId + '_Visitors').value;
				/*** NOT COMPATIBLE WITH OLD VERSION!! ***/
				toggleSigninValue(attendeeId + "_Visitors");
			}
}

/**********************************************************
 * function setSubmitted
 *
 * Description:  Sets the service state as submitted (or not)
 * 	by changing the state of the page.  Defaults to true,
 * 	but if false passed, will set to not submitted.
 *
 * Input variables:  boolean
 *
 * Return type: n/a
 **********************************************************/
function setSubmitted ( state ) {
	// check if true or no parameter passed (default action, i.e. - true)
	if ( state || state === undefined ) {
		// set the flag in page
		document.getElementById('serviceInformation').innerHTML = '<span class="is_submitted">Submitted</span>';
		// show the edit protections div
		document.getElementById('editProtection').style.display = "block";
	} else {  // set to not submitted
		// set the flag in page
		document.getElementById('serviceInformation').innerHTML = '<span class="not_submitted">Not Submitted</span>';
		// show the edit protections div
		document.getElementById('editProtection').style.display = "none";
	}
}


