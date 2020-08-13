<?php
    include("DisplayErrors.php");

    $team = $_GET["team"];
    $year = $_GET["year"];
    $lineup = $_GET["lineup"];

    require("DBconn.php");
    require("schedule.php");

    $season = SaveUserLineup($conn, $team, $year, $lineup);
    $schedule = new schedule($team, $year, $season);
    $schedule->start();

    $conn = null;
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
        $insert = substr($insert, 0, -2) . ";";  // remove final comma
        $sql = $conn->prepare($insert);
        $sql->execute();
        return $season;
    }
?>
