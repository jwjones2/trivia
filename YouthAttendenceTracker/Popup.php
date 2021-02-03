<?php
    /************************************************************************************
     * Popup.php
     *
     * Description:  contains a function for spitting out the standard used popup div
     *      elements.  This code is often resued for creating, populating, and closing popups.
     ************************************************************************************/
    
    function insert_popup_code ( $id = "popup", $close_function = "closePopup" ) {
        // just prints the html with the inserted ids
        echo '<!-- popup section -->';
        printf('<div id="%s-background" class="%s-background" onclick="%s()">', $id, $id, $close_function);
	printf('<div id="closeButton" class="transparent-close-button" onclick="%s()">X</div></div>', $close_function);
        printf('<div id="%s-content" class="%s-content"><div id="%s-header" class="%s-header"> </div>', $id, $id, $id, $id);
        printf('<div id="%s-information" class="%s-information"> </div>', $id, $id);
        printf('<div id="%s-buttons" class="%s-buttons"> </div> <!-- optional div to set buttons when using popup for various functions --> </div>', $id, $id);
    }
    
    function insert_loading_code ( $id ="load", $close_function = "closeContent" ) {
        // just prints the html with the inserted ids
        echo '<!-- LOAD SONG SECTION:  Used to dynamically display a song when clicked. -->';
        printf('<div id="%s-background" onclick="%s()"> </div>', $id, $close_function);
        printf('<div id="%s-content"><div id="closeButton" onclick="%s()">X</div>', $id, $close_function);
        echo '<div id="loaded-content"> <!-- Loaded data is generated here by javascript. --></div></div>';
    }
?>