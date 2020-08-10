<html>
    <head>
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="scripts.js"></script>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
        <link rel="icon" type="image/x-icon" href="favicon.ico?v=2"/>
        <title>Baseball For Bucks</title>
    </head>
    <body>
	<div id="beta">BETA</div>

	<div id="leftMenu" class="steps">
        <div class="heading" style="height:0px;">&nbsp;</div>
        <img src="images/logo.png" id="logo">
        <br> <br>
        <?php //include ("menu/about.php") ?>
        <div id="FeaturedFranchise"></div>
	</div>

	<div class="steps" id="Step1">
        <div class="heading">
            Step 1<br>
            Choose Team and Year
        </div>
        <div id="divShowTeams">
            <?php include("php/ShowTeams.php"); ?>
        </div>
	</div>

	<div class="steps" id="Step2">
		<div class="heading">
			Step 2<br>
			Arrange the Lineup
		</div>
		<div id="Step2_instructions" class="instructions">
            After selecting the team and year in Step 1, you can arrange the lineup here by dragging the players into position (or having the computer generate a great lineup for you).
            <br>
            You can research the team or any player by clicking the Google icon(s).
            <br>
            Then, click the blue right arrow to add this lineup to your seasons.
		</div>
		<div id="ActualSeasonSummary" class="SeasonMessage"></div>
		<div id="divTools" class="tools">
			<a id="GoogleTeam" target="_blank" class="tools" title="Research this team on Google"><img src="images/google.png"></a>
			&nbsp;
			<a class="tools" onclick="CreateLineup(1);" title="Let the computer generate a great lineup"><img src="images/computer.png"></a>
            &nbsp;
            <a class="tools" id="RightArrow" onclick="AddLineup();" title="Add this lineup to your seasons"><img src="images/RightArrow.png"></a>
		</div>
		<ul id='ulLineup'></ul>
	</div>

	<div class="steps" id="Step3">
		<div class="heading">
			Step 3</br>
			Simulate the Seasons
		</div>
		<div id="Step3_instructions" class="instructions">
            Once you have chosen five seasons, click "Play Ball", and the computer will simulate those seasons, against their actual opponents from that year.
            <br>
            At the end of each week, the highest winning percentage will split the earnings!
        </div>
		<div id="RunSchedule">
			<br>
            <ul id='ulSeasons'></ul>
            <br>
			<button id="btnPlayBall">Play Ball !</button>
			<img id="SpinBall" src="images/ball.png">
		</div>
		<br> <br>
		<div id="SimulatedSeasonResults" class="SeasonMessage"></div>
	</div>
    </body>
</html>
