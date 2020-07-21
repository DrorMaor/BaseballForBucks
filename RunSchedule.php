<?php
    //include("DisplayErrors.php");

    $team = $_GET["team"];
    $year = $_GET["year"];
    require("DBconn.php");
    $season = SaveUserLineup($conn, $team, $year, $_GET["lineup"]);
    $conn = null;

    require_once("schedule.php");
    $schedule = new schedule($team, $year, $season);
    $schedule->start();
    $results .= "In the simulated season with your lineup, they went " . $schedule->W . "-" . $schedule->L . ".";
    //echo json_encode($cols);
    echo $results;
    echo "<br> <br>";
    //for ($i = 0; $i < count($schedule->AllGames); $i++) {
    //    echo $schedule->AllGames[$i];
    //}
    //print_r($schedule->AllGames);

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
