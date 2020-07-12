<?php
	echo time()."<br>";

	$_schedule = true;
	$_game = false;
	$_err = false;

	if ($_err) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
	if ($_schedule) {
		require_once("schedule.php");
		$schedule = new schedule(4, 1962);
		$schedule->GetScheduleAndPlayGames();
		echo $schedule->W . "-" . $schedule->L . "<br>";
	}
	if ($_game) {
		require_once("game.php");
		for ($i=0; $i<3; $i++) {
			$game = new game(1982, 38, 39);
			$game->start();
			echo $game->teams[0]->score . "-" . $game->teams[1]->score . " ($game->InningFrame)<br>";
			$game = null;
		}
	}
	echo time()."<br>";
?>
