<html>
	<head>
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="scripts.js"></script>
		<link rel="stylesheet" href="styles.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
	</head>
<body>
<div id="leftMenu" class="steps">
	<div class="heading">
		Baseball For Bucks
		<br>
		&nbsp;
	</div>
	About <br>
	FAQ <br>
	Contact </br>
	Sign up
</div>

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
		Arrange the Lineup
	</div>
	<div id="Step2_instructions" class="instructions">
		After you select the team and year in Step 1, you will arrange the lineup here by dragging the players into position.
		<br>
		You can easily research the team or any player by clicking the Google icon(s).
	</div>
	<div id="ActualSeasonSummary" class="SeasonMessage"></div>
	<div id="GoogleTeam"></div>
	<ul id='ulLineup'></ul>
</div>

<div class="steps" id="Step3">
	<div class="heading">
		Step 3</br>
		Simulate the Season
	</div>
	<div id="Step3_instructions" class="instructions">
		Once you finalized your lineup, click "Play Ball", and the computer will simulate the entire season of the team you managed, against their actual opponents from that year.
	</div>
	<button id='PlayBall' onclick='RunSchedule();'>Play Ball !</button>
	<br>
	<img id="imgSpinBall" src="ball.png" alt="">
	<br>
	<div id="SimulatedSeasonResults" class="SeasonMessage"></div>
</div>

</body>
</html>
