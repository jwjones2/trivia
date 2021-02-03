<?php
	// start session for service id
	session_start();

	// Turn off all error reporting
	error_reporting(0);

	/********************************************************
	 * Database Connection section.
	 ********************************************************/
	// Connect to the db; use MYSQL_helper.php for mysql login
	require_once('MYSQL_helper.php');
	$db = db_connect( );

	// set the error variable to hold db errors; set to empty string
	$error = "";

	// Check that connection was successful and set $error if not
	if ( $db->connect_error ) {
		$error = "There was a problem connecting to the database.";
	}

	// Turn off all error reporting to suppress warning and such appearing on html page
	error_reporting(0);

	// set the default timezone for date functions
	date_default_timezone_set("US/Central");

	// check if body_class was defined, if not set to default homeBody
	if ( !isset($body_class) )
		$body_class = "homeBody";
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo $title; ?></title>

		<!--favicon icon-->
		<link rel="shortcut icon" href="./favicon.ico" />

		<!-- Metadata Section:  Description of Page and Keywords for Site. -->
		<meta name="description" content="" />
		<meta name="keywords" content="" />

		<!-- External scripts and css -->
		<link rel="stylesheet" href="./jquery/css/smoothness/jquery-ui-1.10.1.custom.css" />
		<link rel="stylesheet" href="./toastr-master/toastr.css" />
		<link href='http://fonts.googleapis.com/css?family=Parisienne' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Quintessential|Fanwood+Text:400,400italic|Alice' rel='stylesheet' type='text/css'>


		<!-- jquery from google source -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>

		<!-- Link to Stylesheet -->
		<link rel="stylesheet" href="./main.css" type="text/css" />
<?php
	// check if this is AttendeeGridView.php, if so output the extra css
	if ( $_SERVER['PHP_SELF'] == "/YouthAttendenceTracker/AttendeeGridView.php" ):
?>
		<link rel="stylesheet" href="./reset.css" type="text/css"
	<?php endif; ?>

		<!-- Link to Javascript code for page -->
		<script type="text/javascript" language="Javascript" src="./toastr-master/toastr.js"> </script>
		<script type="text/javascript" src="Sha.js"> </script>
		<script type="text/javascript" language="Javascript" src="./validator.js"> </script>
		<script type="text/javascript" language="Javascript" src="./baseurl.js"> </script>
		<script type="text/javascript" language="Javascript" src="./message.js"> </script>
		<script type="text/javascript" language="Javascript" src="./main.js"> </script>
		<script type="text/javascript" language="Javascript" src="./backup.js"> </script>
		<style type="text/css">
<?php
	// test for homepage to outut special style for highlighting current day on Calendar
	if ( $_SERVER['PHP_SELF'] == "/YouthAttendenceTracker/home.php" )
		printf('.%s-%d { background-color: #A7D2F9; }', date('F'), date('j'));
?>
			.extra-background {
<?php
	// get the background file names to use in setting a random background for the page
	$bgs = scandir($_SERVER['DOCUMENT_ROOT'] . '/YouthAttendenceTracker/site_images/backgrounds/');
	$random_number = rand(2, (count($bgs)-1));  // get the random number to use to get random image, first two spots in array are . and ..

	// set the background image
	echo 'background-image: url("./site_images/backgrounds/' . $bgs[$random_number] . '");';

?>
			}
		</style>

	</head>

	<body class="<?= $body_class; ?>">
		<div class="extra-background"> </div>
		<!-- MENU SECTION -->
		<?php
			require_once('HTML_helper.php');
			write_menu();
		?>

		<div id="homeLogo">
			<a href="./home.php">
				<img src="./site_images/YAT_logo.png" class="homeLogo" />
			</a>
		</div>
