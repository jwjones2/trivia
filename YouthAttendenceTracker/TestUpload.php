<?php
/***********************************************
 * Code taken from w3schools
 * http://www.w3schools.com/php/php_file_upload.asp
 *
 * This code takes a file from a form and uploads
 * it to a temp folder and then moves it to a folder
 * on the server if the picture file does not alreader
 * exist.  The picture must be gif or jpeg and smaller
 * than 50kb.
 ***********************************************/
if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg")) && ($_FILES["file"]["size"] < 50000))
{
	if ($_FILES["file"]["error"] > 0)
        {
        	echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        }
        else
        {
        	echo "Upload: " . $_FILES["file"]["name"] . "<br />";
        	echo "Type: " . $_FILES["file"]["type"] . "<br />";
        	echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
        	echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

        	if (file_exists("upload/" . $_FILES["file"]["name"]))
        	{
        		echo $_FILES["file"]["name"] . " already exists. ";
        	}
        	else
        	{
        		move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $_FILES["file"]["name"]);
        		echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
        	}
        }
}
else
{
	echo "Invalid file";
}

?>
