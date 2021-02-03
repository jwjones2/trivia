
// checks for empty string, alerts user, and returns boolean
function checkForBlank(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateBlank(val)) {  // use validateBlank to test for empty string
        message.show("Error:  " + name + " cannot be blank.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
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
        message.show("Error:  " + name + " must be a string.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
        return false;   // return valid 
    }
    else {
        return true;
    }
}

// checks if value of user name is a letter, number, or underscore, alerts user, and returns boolean
function checkForUserName(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateUserName( trim(val) )) {  // use function to test value; use trim to get rid of leading or trailing whitespace
        message.show("Error:  " + name + " must be a letter, number, or _ from 6 to 20 characters long.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
        return false;   // return valid 
    }
    else {
        return true;
    }
}

// checks if value a password is a letter, number, or underscore from 8-20 chars, alerts user, and returns boolean
function checkForPassword(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validatePassword( trim(val) )) {  // use function to test value; use trim to get rid of leading or trailing whitespace
        message.show("Error:  " + name + " must be a letter, number, or _ from 8 to 20 characters long.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
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
        message.show("Error:  " + name + " must be a date in format MM-DD-YYYY.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
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
        message.show("Error:  " + name + " must be in format ###-###-####.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
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
        message.show("Error:  " + name + " is not valid.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
        return false;   // return valid 
    }
    else {
        return true;
    }
}

// checks if value of id is a valid zip, alerts user, and returns boolean
function checkForZip(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateZip(val)) {  // use function to test value
        message.show("Error: The zipcode is not in the proper format. Must be 5 numbers.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
        return false;   // return valid 
    }
    else {
        return true;
    }
}

// checks if value of id is a valid State code, alerts user, and returns boolean
function checkForState(id, name) {
    var val = document.getElementById(id).value;  // get value from DOM
    if (!validateState(val)) {  // use function to test value
        message.show("Error: The State is not properly formatted.  Must be in format 'MO'.", "", "error", 5000);  // alert user to error
        // set the background color of the offending field
        setBackgroundColor(id);
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

function setBackgroundColor ( id ) {
    // set background color of offending field
    $("#" + id).css("background-color", "#ffff99");
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

// validate that value is a letter, number, or _
function validateUserName(value) {
    var stringRegEx = /^[a-z0-9_]{6,20}$/i;  // match a-z or A-Z (all letters lower or upper case) one or more times
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

// validate zip
function validateZip(value) {
    var zipRegEx = /^[0-9]{5}$/;
    return zipRegEx.test(value);
}

// validate State -- 2 letters
function validateState(value) {
    var stringRegEx = /^[a-zA-Z]{2}$/;  // match a-z or A-Z (all letters lower or upper case) one or more times
    return stringRegEx.test(value);
}

// validate Password -- alphanumberic from 8 - 20 chars
function validatePassword (value ) {
    var stringRegEx = /^\w{8,20}$/;
    return stringRegEx.test(value);
}

// from  http://stackoverflow.com/questions/18082/validate-numbers-in-javascript-isnumeric
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
