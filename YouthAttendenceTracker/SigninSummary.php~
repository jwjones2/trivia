<?php
	/********************************************************
	 * SigninSummary.php
	 *
	 * Description:  Page redirected to after SignUp.php completes.
	 *	Displays the logged results of the signin to user.
	 ********************************************************/
	// includes
	require_once('HTML_helper.php');
?>
<html>

<head>
	<title>Signin Summary Page</title>
	
	<!--favicon icon-->
        <link rel="shortcut icon" href="./favicon.ico" />
   
        <!-- Metadata Section:  Description of Page and Keywords for Site. -->
        <meta name="description" content="" />
        <meta name="keywords" content="" />
   
        <!-- Link to Stylesheet -->
        <link rel="stylesheet" href="./main.css" type="text/css" />
        
        <!-- Link to Javascript code for page -->
        <script type="text/javascript" language="javascript" src="./main.js"> </script>
	
	<style type="text/css">
		body
		{
			background-color: Gray;
		}
	</style>
</head>

<?php
	// write the site menu
	write_menu();
?>

<body>
	<div class="results">
		<?php
			// get results and parse from GET
			$results = $_GET['results'];
			
			// results strings delimited by __ so explode
			$parse_results = explode('__', $results);
			
			// print successful student queries number stored in first element of parse_results
			printf('<h1>%s</h1><br /><br />', $parse_results[0]);
			
			// print failed student queries number stored in second element of parse_results
			printf('<h1>%s</h1><br /><br />', $parse_results[1]);
		?>
	</div>
</body>

</html>
