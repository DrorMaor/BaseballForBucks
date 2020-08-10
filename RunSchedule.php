<?php
    include("DisplayErrors.php");

    $team = $_GET["team"];
    $year = $_GET["year"];
    require("DBconn.php");
    $season = SaveUserLineup($conn, $team, $year, $_GET["lineup"]);
    $conn = null;

    require_once("schedule.php");
    $schedule = new schedule($team, $year, $season);
    $schedule->start();
    echo json_encode($schedule);

    function SaveUserLineup($conn, $team, $year, $lineup) {
        // first, insert new season
        $lineup = substr($lineup, 0, -1);
        $sql = $conn->prepare("insert into SeasonsPlayed (team, year) values ($team, $year) ");
        $sql->execute();
        $season = $conn->lastInsertId();
        // now, instert the lineup
        $insert = "insert into SeasonsLineup (season, batter) values ";
        $players = explode("|", $lineup);
        foreach ($players as $player)
            $insert .= "($season, $player), ";
        $insert = substr($insert, 0, -2) . ";";  // remove final comman
        $sql = $conn->prepare($insert);
        $sql->execute();
        return $season;
    }
?>
