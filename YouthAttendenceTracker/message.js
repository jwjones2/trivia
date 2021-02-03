/***
 * Used to dynamically display messages or loaders that are
 *   closed upon another event happening.
 ***/

// GLOBAL VARIABLES FOR REFERENCE WHEN REMOVING MESSAGES
var message_id;     // the id of an active message div

/**********************************************************************
 * message
 *
 * Description:
 *********************************************************************/
function Message ( name, height, width, showCloseButton ) {
    this.name = name;          // to hold the name of the message (the variable name javascript references in code);
    this.id = null;                    // to hold id of div that displays message for use in hiding
    this.h = "25";                    // default height
    this.w = "125";                 // default width
    this.s = new Array();        // the array that holds style attributes for message div
    this.closeButton = false;  // bool -- whether to show close button or not; defaults to no
    this.closeButtonStyle = "position: absolute; top: 2px; right: 2px; color: Red; font-size: 10px; cursor: hand; cursor: pointer; text-align: center;"
    
    // check optional parameters and set defaults if not supplied
    if ( height !== undefined )
        this.h = height;
    if ( width !== undefined ) 
        this.w = width;
    if ( showCloseButton !== undefined )  {// check for show close button boolean parameter
        if ( showCloseButton )
            this.closeButton = true;
        else
            this.closeButton = false;
    }

    this.show = function ( mssg ) {
        /***
         * The first time show runs it generates
         * a div and appends it to the body element.
         * Subsequent calls to show only set the display to block.
         * -->On subsequent calls, need to write the new message
         * still since a new message is possibly being called.
         ***/
        if ( this.id ) {
            var node = document.getElementById(this.id);  // get element
            // before displaying, remove text node and append new message as a text node
            node.firstChild.removeChild(node.firstChild.firstChild);
            node.firstChild.appendChild(document.createTextNode(mssg));
            
            // set node visible
            //UPDATE USE JQUERY TO FADE IN -- node.style.display = "block";
            $("#" + this.id).fadeIn();
        } else {
            this.id = "div_" + parseInt(Math.random() * 100);  // id = div_randomnumber
            var div = document.createElement('div');
            div.setAttribute("id", this.id);  // set the id to random generated id
            div.setAttribute("style", this.buildStyle());
            var message = document.createTextNode(mssg);
            var span = document.createElement('span');
            span.appendChild(message);
            div.appendChild(span);
            
            // check if need to append close button
            if ( this.closeButton ) {
                // create button and set attributes
                var b = document.createElement('input');
                b.setAttribute("type", "button");
                b.setAttribute("value", "x");
                b.setAttribute("style", this.closeButtonStyle);
                // set onclick using reference to this object passed in s parameter
                b.setAttribute("onclick", s + ".hide()");
                
                // append to div
                div.appendChild(b);
            }
        
            document.body.appendChild(div);
        }
        
        // call setWidth if auto width that will set width based on mssg size
        if ( this.w == "auto" )
            this.setWidth(mssg);
    }
    
    this.setStyle = function ( style ) {
        this.s.push(style);
    }
    
    // this is a combination of show and set timer in
    // one function.  Call with mssg and time, message
    // will show for time input.
    this.popup = function ( message, time ) {
        this.show(message);
        this.setTimer(time);
    }
    
    this.buildStyle = function () {
        // set the elements for default div style
        this.s.push("position: fixed;");
        // check for auto width
        if ( this.w != "auto" )
            this.s.push("width: " + this.w + "px;");
        this.s.push("height: " + this.h + "px;");
        this.s.push("-moz-box-shadow: 5px 3px 3px #444; -webkit-box-shadow: 5px 3px 3px #444; box-shadow: 5px 3px 3px #444;");
        this.s.push("-moz-border-radius: 5px; border-radius: 5px;");
        this.s.push("background-color: #eeeeee;");
        this.s.push("background-color: rgba(240, 240, 240, 0.7);");
        this.s.push("border: 1px solid #00b6de;")
        this.s.push("font-size: 13px;");
        this.s.push("line-height: " + this.h + "px;");
        this.s.push("verticle-align: middle;");
        this.s.push("text-align: center;")
        this.s.push("font-weight: bold;");
        this.s.push("font-family: Sans Serif;")
        this.s.push("color: #231f20;");
        
        return this.s.join(' ');
    }
    
    // function to hide the message -- sets the display of div to none
    this.hide = function () {
        //document.getElementById(this.id).style.display = "none";
        // UPDATE -- USE JQUERY TO FADEOUT
        $("#" + this.id).fadeOut();
    }
    
    // function to set to show close button
    this.showClose = function ( b ) {
        // use b as a boolean instead of just setting closeButton to
        // value, this will ensure bad input is caught -- if will treat
        // any non-false value as true
        if ( b )
            this.closeButton = true;
        else
            this.closeButton = false;
    }
    
    // function to set a timeout on the message
    this.setTimer = function ( time ) {
        setTimeout(this.name + '.hide()', time);
    }
    
    // function to set the width dynamically if width is auto
    this.setWidth = function ( mssg ) {
        var len = mssg.length * this.h / 2;
    
        // set the message node's width
        document.getElementById(this.id).style.width = len + "px";
        // now center the message with new width
        document.getElementById(this.id).style.left = "50%";
        document.getElementById(this.id).style.marginLeft = "-" + (len / 2);
    }
}

/************************************************************************
 * Toastr helper class ToastrHelper
 *
 * Description:  A simple wrapper for calling toastr to be able to keep
 *  a generic form for toastr calls across a web application and keep
 *  code minimal in other javascript files.  Also saves the last toast
 *  reference for clearing the last toast
 ************************************************************************/
function ToastrHelper () {
    this.last = null; // reference to the last toast made
    this.defaultTimeout = 2000;  // the default timeout
    
    // the basic call, takes a title (optional), message, type, timeout(optional), and onclick (optional)
    // and makes the toastr call
    this.show = function (mssg, title, type, timeout, onclick) {
        // can set timeout first if input, else set it to default timeout
        if ( timeout !== undefined )
            toastr.options.timeOut = timeout;
        else
            toastr.options.timeOut = this.defaultTimeout;
    
        // can check and set onclick also
        if ( onclick !== undefined )
            toastr.options.onclick = onclick;
        
        // first check for type and make calls accordingly
        if ( type == "info" ) {
            // for info, show top right
            toastr.options.positionClass = 'toast-top-right';
            
            // check that title was input
            if ( title !== undefined ) 
                this.last = toastr.info(title, mssg);
            else
                this.last = toastr.info(mssg);
        } else if ( type == "warning" ) {
            // for warning, show top full width
            toastr.options.positionClass = 'toast-top-full-width';
          
            // check that title was input
            if ( title !== undefined ) 
                this.last = toastr.warning(title, mssg);
            else
                this.last = toastr.warning(mssg);
        } else if ( type == "error" ) {
            // for errors, show full width across the top
            toastr.options.positionClass = 'toast-top-full-width';
            
            // check that title was input
            if ( title !== undefined ) 
                this.last = toastr.error(title, mssg);
            else
                this.last = toastr.error(mssg);
        }  else {
            // this is the default so that type, if not entered correct, or not entered will display as info
            // for info, show top right
            toastr.options.positionClass = 'toast-top-right';
            
            // check that title was input
            if ( title !== undefined ) 
                this.last = toastr.info(title, mssg);
            else
                this.last = toastr.info(mssg);
        }
    }
    
    // to clear the last toast shown
    this.clearLast = function () {
        toastr.clear(this.last);
    }
    
    // to clear all toasts using toastr built in function
    this.clear = function () {
        toastr.clear();
    }
}