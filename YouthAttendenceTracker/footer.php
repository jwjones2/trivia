<?php
	/************************************************************
	 * Footer.php
	 *
	 * Description:  The footer for the page.  Insert popup code
	 * 	and finish the body and html.  Add additional site-wide
	 * 	code.
	 ************************************************************/
?>
        
        <?php
		// insert the popup code
		require_once('Popup.php');
		insert_popup_code();
        ?>
        
        <script>
            // to show the tooltip for any "title" tag in the page
            $( function () {
                $( document ).tooltip();
            });
        </script>
    
    </body>
</html>