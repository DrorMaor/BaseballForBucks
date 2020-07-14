<?php
    $team = $_GET["team"];
    $year = $_GET["year"];

    require_once("schedule.php");
    $schedule = new schedule($team, $year);
    $schedule->GetScheduleAndPlayGames();

    require("DBconn.php");
    $sql = $conn->prepare("select t.city, t.name, s.W, s.L from ActualTeams t
            inner join ActualSeasons s on t.id = s.team
            where t.id = $team and s.year = $year ;");
    $sql->execute();
    foreach($sql as $row => $cols)
        echo json_encode($cols);
    $conn = null;
?>
