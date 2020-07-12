<?php
    require_once("schedule.php");
    $schedule = new schedule($_GET["team"], $_GET["year"]);
    $schedule->GetScheduleAndPlayGames();
    print_r($schedule);
    // echo $schedule->W . "-" . $schedule->L . "<br>";
?>
