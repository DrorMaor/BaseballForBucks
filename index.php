<?php
    if (parse_url($_SERVER['REQUEST_URI'])["query"] != "")
        die();
    else
        include_once("php/traffic.php");
?>

<html>
    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-20157082-9"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-20157082-9');
        </script>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <?php include ("menu.html") ?>
        <br> <br>
        <div id="FeaturedFranchise"></div>
	</div>

	<div class="steps" id="Step1">
        <div class="heading">
            <div class="HeadingGray">Step 1</div>
            Choose Team and Year
        </div>
        <div id="divShowTeams">
            <?php include("php/ShowTeams.php"); ?>
        </div>
	</div>

	<div class="steps" id="Step2">
		<div class="heading">
			<div class="HeadingGray">Step 2</div>
			<div class="help" title = "After selecting the team and year in Step 1, you can arrange the lineup here by dragging the players into position (or having the computer generate a great lineup for you).
                You can research the team or any player by clicking the Google icon(s).">
                Arrange the Lineup
            </div>
		</div>
		<div id="ActualSeasonSummary" class="SeasonMessage"></div>
		<div id="divTools" class="tools">
			<a id="GoogleTeam" target="_blank" class="tools" title="Research this team on Google"><img src="images/google.png"></a>
			&nbsp;
			<a class="tools" onclick="CreateLineup(1);" title="Let the computer generate a great lineup"><img src="images/computer.png"></a>
		</div>
		<ul id='ulLineup'></ul>
	</div>

	<div class="steps" id="Step3">
        <div class="heading">
            <div class="HeadingGray">Step 3</div>
		    <div class="help" title = "Once you have chosen your lineup, click 'Play Ball', and the computer will simulate the season against their actual opponents from that year.
                At the end of each week, the highest winning percentage will split the pot!">
                Simulate the Season
            </div>
            </span>
        </div>
		<div id="RunSchedule">
            <br>
			<button id="btnPlayBall" onclick="RunSchedule();">Play Ball !</button>
            <br>
			<img id="SpinBall" src="images/ball.png">
		</div>
		<br> <br>
		<div id="SimulatedSeasonResults" class="SeasonMessage"></div>
	</div>

    <div id="AdSense">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- BaseballForBucks -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-9172347417963561"
             data-ad-slot="6598809232"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    </body>
</html>
