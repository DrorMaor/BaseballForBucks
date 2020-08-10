<?php
    //include("DisplayErrors.php");

    $final->W = 0;
    $final->L = 0;
    $teams = explode(",", $_GET["teams"]);
    $years = explode(",", $_GET["years"]);
    $lineups = explode(",", $_GET["lineups"]);

    require("DBconn.php");
    require("schedule.php");
    for ($i=0; $i<5; $i++) {
        $season = SaveUserLineup($conn, $teams[$i], $years[$i], $lineups[$i]);
        $schedule = new schedule($teams[$i], $years[$i], $season);
        $schedule->start();
        $final->W += $schedule->W;
        $final->L += $schedule->L;
    }
    $conn = null;
    echo json_encode($final);

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
