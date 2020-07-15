<?php
	if (true) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
?>

<html>
	<head>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="scripts.js"></script>
		<link rel="stylesheet" href="styles.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	</head>
<body>
<div id='divTeams'>
	<div class="heading">Step 1 - Choose Team & Year</div>
	<?php include("ShowTeams.php"); ?>
</div>

<div id="divLineup">
	<div class="heading">Step 2 - Drag Players to Choose Your Lineup</div>
	<ul id='sortable'></ul>
</div>

<div id="divPlayBall">
	<div class="heading">Step 3 - Play Ball !</div>
	<button id='btnRunSchedule' onclick='RunSchedule();'>Run</button>
	<br>
	<div id="divResults"></div>
	<img id="imgSpinBall" src="ball.png" alt="">
</div>

</body>
</html>
