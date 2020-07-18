<html>
	<head>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="scripts.js"></script>
		<link rel="stylesheet" href="styles.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	</head>
<body>
<div class="steps" id="Step1">
	<div class="heading">
		Step 1<br>
		Choose Team & Year
	</div>
	<div id="divShowTeams">
		<?php include("ShowTeams.php"); ?>
	</div>
</div>

<div class="steps" id="Step2">
	<div class="heading">
		Step 2<br>
		Choose Your Lineup
	</div>
	<ul id='sortable'></ul>
</div>

<div class="steps" id="Step3">
	<div class="heading">
		Step 3</br>
		Play Ball !
	</div>
	<button id='btnRunSchedule' onclick='RunSchedule();'>Play Ball !</button>
	<br>
	<div id="divResults"></div>
	<img id="imgSpinBall" src="ball.png" alt="">
</div>

</body>
</html>
