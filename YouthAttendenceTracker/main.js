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

// create a global ToasterHelper object to use to make all toastr calls
var lastToast = null;
var message = new ToastrHelper();

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
	var div1 = document.getElementById('student_popup');

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
	if ( $('#pickStudent').val() == "default" ) {
		message.show("Show must select a student first.", "", "warning");
		return;
	}

	// get student's data from pickStudent value
	var postString = "pickStudent=" + $('#pickStudent').val();

	// also send aId in case this student is being added as a visitor
	postString += "&aId=" + attendeeId;

	// make the call to server
	ajaxCallPrepare_POST("./AddStudent.php", postString);
}

/*******************************************************************************
 * Function quickPickStudent
 *
 * Description:  Sends the student's id to AddStudent.php script.  No other data
 * 	necessary.
 *
 * Input variable: n/a
 *
 * Return type: n/a
 ******************************************************************************/
function quickPickStudent ( data )
{

	// get student's data from pickStudent value
	var postString = "pickStudent=" + data;

	// make the call to server
	ajaxCallPrepare_POST("./AddStudent.php", postString);
}

/*******************************************************************************
 * Function quickAddStudent
 *
 * Description:  Sends the students data to AddStudent.php
 *
 * Input variable: data (ID|FirstName|LastName|NickName[optional])
 *
 * Return type: n/a
 ******************************************************************************/
function quickAddStudent ( value )
{
	if ( value === "undefined" || value == "" ) {
		message.show("There is no student selected.", "", "warning");
		return;
	}

	// set post string with value
	var postString = "pickStudent=" + value;

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
function addVisitor ( )
{
	/* BUILD THE POST STRING FROM FORM ELEMENTS */
	var postString = "";   // variable to store post string to send

	// add visitor add id to the post string, was set already by showAddVisitor
	postString += "&visitoraddid=" + visitorAddId + "&";

	// the the data from form and send
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

	message.show("Adding the Visitor...");
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

	$('#pickVisitorStudentSection').html(pickVisitorContent);

	// get reference to the divs to change transparency
	var div1 = document.getElementById('student_popup');

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
 * Function showAddVisitor
 *
 * Description: Creates a popup to add a new visitor to the page.
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function showAddVisitor ( )
{
	var header = "Add a Visitor";
	var content = 'Name: <input type="text" id="visitor-name" />';
	content += '<br /><br />DOB <input type="text" id="visitor-dob" />';
	content += '<br /><br /><div class="center bold italic large-text">Type the name of the visitor here.  If they are already';
	content += ' in the system select their name from the dropdown list.  If not, enter their name and date-of-birth and press "Add" below.</div>';
	var buttons = '<input type="button" value="Add" onclick="addNewVisitor(' + attendeeId + ')" />';

	setPopupContent(header, content, buttons);
	resizePopup(300, 400);
	showPopup();

	$( "#visitor-name" ).autocomplete({
		minLength: 1,
		delay: 500,
		source: function( request, response ) {
			lastXhr = $.ajax({
				url: "./SearchVisitors.php",
				dataType: "json",
				data: {
					term: $("#visitor-name").val().trim(),
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
			$('#visitor-name').val(ui.item.label);
		},
		search: function ( event, ui ) {
			$("#visitor-name").css("background-image", "url('./site_images/ajax-loader.gif')");
			$("#visitor-name").css("background-position", "right");
			$("#visitor-name").css("background-repeat", "no-repeat");
		},
		select: function ( event, ui ) {
			$("#visitor-name").css("background-image", "url('')");
			$('#visitor-name').val(ui.item.label);
			// get the DOB from value and add to DOB field
			var parts = ui.item.value.split('|');
			$('#visitor-dob').val(parts[1]);
			return false;  // prevent default behavior
		}
	});

	$("#visitor-name").keypress( function ( e ) {
		if (!e) e = window.event;
		if (e.keyCode == '13'){
			$('#visitor-name').autocomplete('close');
			$("#visitor-name").css("background-image", "url('')");
			$('#visitor-name').val("");
			return false;
		}
	});

	$('#visitor-dob').datepicker({dateFormat: "mm-dd-yy", defaultDate: "-12y"});
}

/*******************************************************************************
 * Function addNewVisitor
 *
 * Description: Calls the database and handles adding the new visitor.
 *
 * Input Variables: n/a
 *
 * Returns: n/a
 ******************************************************************************/
function addNewVisitor ( ) {
	// first verify name is not blank
	if ( !checkForBlank('visitor-name', "Visitor's Name") )
		return;

	// verify that DOB is not blank or is a valid DOB
	if ( !checkForDate('visitor-dob', "DOB") )
		return;

	// send to AddNewVisitor.php to complete the task and handle the response
	// to add the student
	$.ajax({
		type: "POST",
		url: "AddNewVisitor.php",
		data: { "name": $('#visitor-name').val(), "dob": $('#visitor-dob').val(), "sex": $('#visitor-sex:checked').val() }
	})
	.done ( function ( msg ) {
		var ret = msg.split('_');
		if ( ret[0] == "true" ) {
			// call AddStudent.php to handle th rest
			ajaxCallPrepare_POST("AddStudent.php", "pickStudent=" + ret[1] + "&aId=" + attendeeId);
			message.show("Adding the visitor...");
		} else {
			message.show("Could not add the visitor to the database.  Please try again.", "", "error");
		}
	});
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

		Also, if they have a PictureURL add that
	 ***/
	if ( visitorAddId == 0 ) {
		var input = document.createElement('input');
		input.setAttribute('id', 'student-row-delete-button');
		input.setAttribute('value', 'X');
		input.setAttribute('class', 'delete-button');
		input.setAttribute('onclick', 'deleteStudent(' + data[1] + ')');
		td.appendChild(input);

		// need to double check for nickname since it makes data[5] be the add
		if ( data[5] == "add" || data[5] == "noadd" ) {
			if ( data[6] != "NULL" ) {
				var img = document.createElement('img');
				img.setAttribute('src', data[6]);
				img.setAttribute('width', '50px');
				img.setAttribute('height', '50px');
				td.appendChild(img);
			}
		} else {
			if ( data[5] != "NULL" ) {
				var img = document.createElement('img');
				img.setAttribute('src', data[5]);
				img.setAttribute('width', '50px');
				img.setAttribute('height', '50px');
				td.appendChild(img);
			}
		}
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
	message.show('Updating...');

	var parts = id.split('_');
	var getString = './UpdateStudentSignin.php?id=' + parts[0] + '&field=' + parts[1] + '&value=';

	//**check which value to send and append to url
	// checkboxes are yes, no for checked or not (SunSch, SunMorn, SunEven, Bible)
	// Visitors value is in page at ATTENDEEID_Visitors, get value and send
	// Extrapoints value is in page at ATTENDEEID_ExtraPoints, get value and send
	if ( parts[1] == "SunSchAttend" || parts[1] == "SunMornAttend" || parts[1] == "SunEvenAttend" || parts[1] == "Bible" ) {
		if ( document.getElementById(id).checked == 1 )
			getString += "yes";
		else
			getString += "no";
	} else if ( parts[1] == "Visitors" ) {
		getString += document.getElementById(id).value;
	} else if ( parts[1] == "ExtraPoints" ) {
		getString += document.getElementById(id).value;
	}

	// make the request and handle the response
	$.ajax({
		type: "GET",
		url: getString
	})
	.done ( function ( msg ) {
		var ret = msg.split('|');
		if ( ret[1] == "true" )
			message.show("Successfully updated the database.");
		else
			message.show(ret[1], "", "error");
	});
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
	if ( confirm("Are you sure you want to delete the visitor?") ) {
		// remove the visitor section
		$('#' + cId).remove();

		// decrement the value in page
		// NOW, decrement the visitors value for the student and call toggleSigninValue to process the value in temp_signin
		$('#' + aId + '_Visitors').val( $('#' + aId + '_Visitors').val() - 1 );
		toggleSigninValue(aId + "_Visitors");

		// to finish deleteVisitorOfStudent needs to be called to remove the visitor's row in the table
		$.ajax({
			type: "GET",
			url: "RemoveVisitor.php?aId=" + aId + "&vId=" + vId
		})
		.done ( function ( msg ) {
			var ret = msg.split('|');

			if ( ret[0] == "error" ) {
				message.show(ret[1], "", "error");
			} else {
				message.show("Successfully removed the visitor.");

				// need to remove the visitor's row
				$('#StudentRow_' + vId).remove();
			}
		});
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
	document.getElementById('pickStudent').value = val;
	addStudent();
}


/************************************************************
 * Function: validateTeamCreate
 *
 * Description:  Make sure form data is OK for create team
 * 	form.  Verify start is less than end
 * 	date; verify name is not blank; and, verify start date
 * 	is not blank and is a valid date.  Also need to make sure
 * 	if end date is given (it is optional) that it is valid.
 *
 * Input variables:  n/a
 *
 * Return type: bool
 ************************************************************/
function verifyTeamCreate ( ) {
	// start by checking for blanks on name and start date
	if ( !checkForBlank('teamName', "Team Name") ) {
		return false;
	}
	if ( !checkForBlank('startDate', "Start Date")) {
		return false;
	}
	// now check that start date is a date
	if ( !checkForDate('startDate', "Start Date" ) ) {
		return false;
	}
	// end  date must be a valid date if not blank
	if ( $('#endDate').val() != "" ) {
		if ( !checkForDate('endDate', "End Date" ) ) {
			return false;
		}
	}
	var dParts1 = $('#startDate').val().split('-');
	var dParts2 = $('#endDate').val().split('-');
	var date1 = new Date().setFullYear(dParts1[2], dParts1[0] - 1, dParts1[1]);
	var date2 = new Date().setFullYear(dParts2[2], dParts2[0] - 1, dParts2[1]);

	// need to verify that date2 is not less than or equal to date1, but date2 is optional
	// so don't check if date2 nan
	if ( date2 && date1 >= date2 ) {
		message.show("The Ending Date must come after the Start Date.");
		// highlight the field
		$('#endDate').css('background-color', 'Yellow');
		return false;
	} else {
		return true;
	}
}

/************************************************************
 * Function removeTeamMember
 *
 * Description:  Sends request to server and receives response
 * 	to remove a member of a team.
 *
 * Input variables:  int (team id), int (attendee id)
 *
 * Return type:  n/a
 ************************************************************/
function removeTeamMember ( teamId, attendeeId ) {
	// verify the user's action
	if ( confirm("Are you sure you want to remove the team member?") ) {
		// use jquery ajax to make the request and get the response
		$.ajax({
			type: "GET",
			url: "RemoveTeamMember.php",
			data: { team_id: teamId, attendee_id: attendeeId }
		})
		.done ( function ( msg ) {
			// split the msg parts and process by removing student or alerting error
			var ret = msg.split('|');

			if ( ret[0] == "error" ) {
				message.show(ret[1], "", "error");
			} else {
				$('#attendee_' + ret[1]).remove();
			}
		});
	}
}

/************************************************************
 * Function removeTeam
 *
 * Description:  Sends request to server and receives response
 * 	to remove a team.
 *
 * Input variables:  int (team id)
 *
 * Return type:  n/a
 ************************************************************/
function removeTeam ( teamId ) {
	// verify the user's action
	if ( confirm("Are you sure you want to remove the team?") ) {
		// use jquery ajax to make the request and get the response
		$.ajax({
			type: "GET",
			url: "RemoveTeam.php",
			data: { team_id: teamId }
		})
		.done ( function ( msg ) {
			// split the msg parts and process by removing student or alerting error
			var ret = msg.split('|');

			if ( ret[0] == "error" ) {
				message.show(ret[1], "", "error");
			} else {
				$('#team_' + ret[1]).remove();
			}
		});
	}
}

/************************************************************
 * Function addTeamPoints
 *
 * Description:  Shows a popup to get the team extra points
 * 	data.
 *
 * Input variables:  int (service id), int (team id)
 *
 * Return type:  n/a
 ************************************************************/
function addTeamPoints ( serviceId, teamID ) {
	var info = 'Points:  <input type="text" id="points_' + teamID + '" /><br />';
	info += '<textarea id="description_' + teamID + '"></textarea>';
	var buttons = '<input type="button" value="Add" onclick="updateTeamExtraPoints(' + serviceId + ', ' + teamID + ', $(\'#points_' + teamID + '\').val(), $(\'#description_' + teamID + '\').val())" />';
	setPopupContent("Add Extra Points", info, buttons);
	showPopup();
}

/************************************************************
 * Function updateTeamExtraPoints
 *
 * Description:  Updates the extra points by sending value to server
 * 	and getting a response.
 *
 * Input variables:  int (service id), int (team id), int (points), string (description)
 *
 * Return type:  n/a
 ************************************************************/
function updateTeamExtraPoints ( serviceId, teamId, points, description ) {
	// trim the value and make sure it is at least 4 in length
	if ( points.trim().length < 4 )
		return;

	// use jquery ajax to make the request and get the response
	$.ajax({
		type: "GET",
		url: "UpdateTeamExtraPoints.php",
		data: { service_id: serviceId, team_id: teamId, value: points, desc: description }
	})
	.done ( function ( msg ) {
		// split the msg parts and process by removing student or alerting error
		var ret = msg.split('|');

		if ( ret[0] == "error" ) {
			message.show(ret[1], "", "error");
		} else {
			message.show("Updated the team's extra points.");
			// reload the page
			setTimeout('location.reload()', 1500);
		}
	});
}

/************************************************************
 * Function deleteExtraPoints
 *
 * Description:  Removes an extra point value for a team.
 *
 * Input variables:  int (extra points id)
 *
 * Return type:  n/a
 ************************************************************/
function deleteExtraPoints ( extraPointsId ) {
	// use jquery ajax to make the request and get the response
	$.ajax({
		type: "GET",
		url: "DeleteTeamExtraPoints.php",
		data: { id: extraPointsId}
	})
	.done ( function ( msg ) {
		// split the msg parts and process by removing student or alerting error
		var ret = msg.split('|');

		if ( ret[0] == "error" ) {
			message.show(ret[1], "", "error");
		} else {
			message.show("Deleted the extra points.");
			// now remove the element
			$('#' + ret[1]).remove();
		}
	});
}

/************************************************************
 * Function showStudentPopup
 *
 * Description:  Gets data from php script for student popup
 * 	and shows.
 *
 * Input variables:  int (student id)
 *
 * Return type:  n/a
 ************************************************************/
function showStudentPopup (id) {
	$.ajax({
		type: "POST",
		url: "get-student-data-popup.php",
		data: {ID: id}
	})
	.done ( function ( msg ) {
		// split msg to get the student's name and data to display
		var ret = msg.split('|');
		setPopupContent(ret[0], ret[1], "");
		resizePopup(275, 500);
		showPopup();
	});
}

/************************************************************
 * Function showTeamPopup
 *
 * Description:  Gets data from php script for team popup
 * 	and shows.
 *
 * Input variables:  int (team id)
 *
 * Return type:  n/a
 ************************************************************/
function showTeamPopup (id) {
	$.ajax({
		type: "GET",
		url: "get-team-data-popup.php",
		data: {team_id: id}
	})
	.done ( function ( msg ) {
		// split the msg parts
		var ret = msg.split('|');
		setPopupContent(ret[0], ret[1], "");
		resizePopup(600, 200);
		showPopup();
	});
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
			message.show(ret[1], "", "error");
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
			 $('#quick-add').val("");  // to make sure quick add is now blank

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

			hideAddVisitor();
			hideAddStudent();
			message.show("Added the Visitor!");
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




function validateAttendeeForm() {
    // Checks all of the form fields for the NewAttendee form to verify that the correct
    // format of data has been entered.

    // first name cannont be blank and must be a string
    if (!checkForBlank("firstName", "First Name"))
        return false;

    if (!checkForString("firstName", "First Name"))
        return false;

    // last name cannont be blank and must be a string
    if (!checkForBlank("lastName", "Last Name"))
        return false;

    if (!checkForString("lastName", "Last Name"))
        return false;

    // Date of birth must not be blank and must be a valid date
    if (!checkForBlank("dob", "Date of Birth"))
        return false;

    if (!checkForDate("dob", "Date of Birth"))
        return false;

    // Home phone must be a valid phone number, if it is not blank
    if (validateBlank(document.getElementById("homePhone").value) && !checkForPhone("homePhone", "Home Phone"))
        return false;

    // Cell phone must be a valid phone number, if it is not blank
    if (validateBlank(document.getElementById("cellPhone").value) && !checkForPhone("cellPhone", "Cell Phone"))
        return false;

    // Check that email is valid, if it is not blank
    if (validateBlank(document.getElementById("email").value) && !checkForEmail("email", "Email"))
        return false;


    // if all tests passed return true so form can submit
    return true;
}

function validateTeamForm() {
    // Checks the form fields for the New Team form
    if ( !checkForBlank('Name', 'Team Name') )
    	    return false;

    return true;
}

// checks for empty string, alerts user, and returns boolean
function checkForBlank(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateBlank(val)) {  // use validateBlank to test for empty string
        alert("Error:  " + name + " cannot be blank.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}

// checks if value of id is a string, alerts user, and returns boolean
function checkForString(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateString( trim(val) )) {  // use function to test value; use trim to get rid of leading or trailing whitespace
        alert("Error:  " + name + " must be a string.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}

// checks if value of id is a valid date, alerts user, and returns boolean
function checkForDate(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateDate(val)) {  // use function to test value
        alert("Error:  " + name + " must be a date in format MM-DD-YYYY.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}

// checks if value of id is a valid date, alerts user, and returns boolean
function checkForDate(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateDate(val)) {  // use function to test value
        alert("Error:  " + name + " must be a date in format MM-DD-YYYY.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}

// checks if value of id is a valid phone number, alerts user, and returns boolean
function checkForPhone(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validatePhone(val)) {  // use function to test value
        alert("Error:  " + name + " must be in format ###-###-####.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}

// checks if value of id is a valid email, alerts user, and returns boolean
function checkForEmail(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateEmail(val)) {  // use function to test value
        alert("Error:  " + name + " is not valid.");  // alert user to error
        return false;   // return valid
    }
    else {
        return true;
    }
}


// Trim function, take from   http://www.irt.org/script/1310.htm
// removes leading and trailing whitespace
function trim(str) {
    str.replace(/^\s*/, '').replace(/\s*$/, '');

   return str;
}


/*
 * Validator functions
 * Description:  Abstract the individual validation of empty string (blank), email, date, etc
 *      from the main functions that the form returns.
 * Input variables:  the value of the document element being validated
 * Return variables:  boolean
 */

 // validate empty string
function validateBlank(value) {
    if (value == "")
        return false;
    else
        return true;
}

// validate phone number:  ###-###-####
function validatePhone(value) {
    var phoneRegEx = /^\d{3}-\d{3}-\d{4}$/;
    return phoneRegEx.test(value);
}

// validate that value is a digit
function validateDigit(value) {
    var digitRegEx = /^\d+$/;
    return digitRegEx.test(value);
}

// validate that value is a string
function validateString(value) {
    var stringRegEx = /^[a-zA-Z]+$/;  // match a-z or A-Z (all letters lower or upper case) one or more times
    return stringRegEx.test(value);
}

// validate email address
function validateEmail(value) {
    var emailRegEx = /^[a-zA-Z0-9\._]+@[a-zA-Z1-9\._]+\.[a-zA-Z]{2,4}$/;
    return emailRegEx.test(value);
}

// validate date in format MM-DD-YYYY
function validateDate(value) {
    var dateRegEx = /^(0[1-9]|1[012])[- \.](0[1-9]|[12][0-9]|3[01])[- \.](19|20)\d\d$/;  // used from http://www.regular-expressions.info/dates.html
    return dateRegEx.test(value);
}

// function verifyDelete
//
// Returns true or false from a prompt to verify the user
// wants to follow through with the action of the submitted form.
function verifyDelete ()
{
	return confirm('Are you sure you want to remove the team member?');
}

var fieldsEnabled = new Array();

// function enableFields
//
// Checks a global variable stored in fieldsEnabled array for each
// start_id input, to see if the signup fields after "Here" are enabled or not.
// If start_id hasn't been added, then add and set to false at start.
// They must be disabled if "Here" checkbox is not checked
// or enabled if it is checked.  The function simply toggles
// the fields disabled or not by incrementing the starting id
// and disabling or enabling the next 6 ids (such as 1, 2, 3, 4, 5, 6)
// following the number represented by the starting id.
//
// Input variables:  The starting id of the fields
//
// Return:  n/a
/*function enableFields ( start_id )
{
	// check that start_id has been checked and is in fieldsEnabled array or add as false
	if ( !fieldsEnabled[start_id] ) // doesn't exist
		fieldsEnabled[start_id] = false;

	if ( fieldsEnabled[start_id]  ) // fields are enabled so disable them
	{
		for ( var i = start_id + 1; i <= start_id + 6; i++ )
		{
			// set the fields to disabled and clear the values so
			// that no previously selected values are still checked
			document.getElementById(i).disabled="disabled";
			document.getElementById(i).checked="";
		}

		// set the hidden box with the id of input id + 5 back to zero selection
		document.getElementById(start_id + 6).value = 0;

		// disable the Visitor button using its id based on start_id:  Visitor_(start_id + 5)_button
		document.getElementById("Visitor_" + (start_id+5) + "_button").disabled="disabled";

		fieldsEnabled[start_id] = false;
	}
	else
	{
		for ( var i = start_id + 1; i <= start_id + 6; i++ )
		{
			document.getElementById(i).disabled="";
		}

		// enable the Visitor button using its id based on start_id:  Visitor_(start_id + 5)_button
		document.getElementById("Visitor_" + (start_id+5) + "_button").disabled="";

		fieldsEnabled[start_id] = true;
	}
}
*/

/*************************************************************************
 * function enableFields **revised version**
 *
 * Description:  enableFields sets the input elements to enabled or disabled
 * 	based on whether the student is present or not to prevent unnecessary
 * 	input (i.e. if student not "here" then no input can be received further.)
 * 	Uses the attendee id to reference elements in page,  checking if
 * 	enabled or not and toggling value.  If a checkbox, then clear check if
 * 	is disabled.
 *
 * Input variables:  string (attend id)
 *
 * Return type: n/a
 ************************************************************************/
function enableFields ( id ) {
    // store values in array
    var values = [id+"_SunSch", id+"_SunAM", id+"_SunPM", id+"_Bible", id+"_Visitor", id+"_ExtraPoints"];

    // variable to use to reference element in page
    var el;

    // loop over values and toggle enabled/disabled
    for ( i = 0; i < 6; i++ ) {
	el = document.getElementById(values[i]);
	if ( el.disabled ) {
	    el.disabled = false;
	} else {
	    el.disabled = true;
	    // check if a checkbox or a button or a text input
	    if ( values[i] == id+"_Visitor") {
		// remove visitors from page if any by clearing innerHTML
		// and set visitor count to 0
		document.getElementById("Visitor_" + id).innerHTML = "";
		document.getElementById(id+"_Visitors").value = 0;
	    } else if ( values[i] == id+"_ExtraPoints" ) {
		el.value = "0";
	    } else {
		el.checked = "";
	    }
	}
    }
}

/*****************************************************************
 * Function changeURL
 *
 * Description:  Changes the value in Picture URL textbox based on
 *     a javascript prompt input.
 *
 * Input variables:  n/a
 *
 * Return Type: n/a
 *****************************************************************/
function changeURL () {
	var url = prompt("Please enter the url of the picture.");

	// default location -- needs to be dynamic....need to change to a picker
	var defaultLocation = "YAT_Photos/";

	if ( url != "" ) { // not an empty string (i.e. nothing input)
	    url = defaultLocation + url;
	    document.getElementById('PictureURL').value = url;
	    document.getElementById('pictureUrlValue').innerHTML = url;
	}
}


/****************************************************
 * POPUP & CONTENT SECTION
 ****************************************************/

/***
 * open content
 ***/
function showContent () {
	document.getElementById('load-background').style.display = "block";
	document.getElementById('load-content').style.display = "block";
}

/***
 * close content
 ***/
function closeContent () {
	// set display to none and then clear the innerHTML of the information element
	document.getElementById('load-background').style.display = "none";
	document.getElementById('load-content').style.display = "none";
	document.getElementById('loaded-content').innerHTML = "";
}

/***
 * open popup
 ***/
function showPopup () {
	/*
	document.getElementById('popup-background').style.display = "block";
	document.getElementById('popup-content').style.display = "block";
	*REPLACE WITH FADE IN JQUERY*/
	$("#popup-background").fadeIn();
	$("#popup-content").fadeIn("slow");
}

/***
 * close popup
 ***/
function closePopup () {
	// set display to none and then clear the innerHTML of the information element
	$('#popup-background').fadeOut("slow");
	$('#popup-content').fadeOut();
	$('#popup-information').html("");
}


/***
 * set popup content
 ***/
function setPopupContent ( header, information, buttons ) {
	// set the content inside of the popup sections
	document.getElementById('popup-header').innerHTML = header;
	document.getElementById('popup-information').innerHTML = information;
	document.getElementById('popup-buttons').innerHTML = buttons;
}

/***
 * To resize the popup content
 ***/
function resizePopup ( height, width, id ) {
	// double check input value and set defaults
	if ( height === undefined )
		height = "400";
	if ( width === undefined )
		width = "400";
	if ( id === undefined )
		id = "popup-content";

	// resposition the popup so that it is still centered
	mLeft = "-200";
	mTop = "-200";
	if ( height != "400" ) {
		// convert height to int, divide by two and make a string with negative sign
		tempNumber = +height / 2;
		mTop = "-" + tempNumber;
	}
	if ( width != "400" ) {
		// convert height to int, divide by two and make a string with negative sign
		tempNumber = +width / 2;
		mLeft = "-" + tempNumber;
	}

	// set the style of the popup-content element
	document.getElementById(id).style.height = height + "px";
	document.getElementById(id).style.width = width + "px";
	document.getElementById(id).style.marginLeft = mLeft + "px";
	document.getElementById(id).style.marginTop = mTop + "px";
}

/***
 * Custom popup css
 *
 * uses jquery's css
 ***/
function setPopupStyle ( style, id ) {
	if ( id === undefined )
		id = "popup-content";

	$('#' + id ).css(style);
}
