<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $team = $_GET["team"];
    $year = $_GET["year"];
    require("DBconn.php");
    $season = SaveUserLineup($conn, $team, $year, $_GET["lineup"]);

    require_once("schedule.php");
    $schedule = new schedule($team, $year, $season);
    $schedule->start();

    $sql = $conn->prepare("select t.city, t.name, s.W, s.L
        from ActualTeams t
        inner join ActualSeasons s on t.id = s.team
        where t.id = $team and s.year = $year ;");
    $sql->execute();
    foreach($sql as $row => $cols)
        echo json_encode($cols);
    $conn = null;

    function SaveUserLineup($conn, $team, $year, $lineup) {
        // first, insert new season
        $lineup = substr($lineup, 0, -1);
        $sql = $conn->prepare("insert into SeasonsPlayed (team, year) values ($team, $year); ");
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
