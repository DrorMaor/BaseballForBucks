<?php
	if (true) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
?>

<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="scripts.js"></script>
		<link rel="stylesheet" href="styles.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	</head>
<body>
<div id='divTeams'><?php include("ShowTeams.php"); ?></div>

<div id="divCreateLineup">
	<br>
	<input type="radio" id="actual" name="lineup" checked>
	<label for="actual">Use actual lineup</label>
	<br>
	<input type="radio" id="create" name="lineup" onclick="CreateLineup();">
	<label for="create">Create your own lineup</label><br>
	<br>
	<button id='btnRunSchedule' onclick='RunSchedule();'>Run</button>
</div>

<div id="divLineup"></div>
<div id="divResults"></div>
<img id="imgSpinBall" src="ball.png" alt="">
</body>
</html>
