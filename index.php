<?php

	echo time()."<br>";

	$type = "s";
	$err = true;
	if ($err) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}


	if ($type == "s") {
		require_once("schedule.php");
		$schedule = new schedule(38, 1982);
		$schedule->GetScheduleAndPlayGames();
		echo $schedule->W . "-" . $schedule->L . "<br>";
	}
	else {
		require_once("game.php");
		$game = new game(1982, 38, 39);
		$game->start();
		print_r($game->teams);
	}

	echo time();
?>
