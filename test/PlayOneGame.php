<?php
    //require_once ("../DisplayErrors.php");

    $team = 38;
    $year = 1982;
    $season = -1;
    $W = 0;
    $L = 0;
    // this will save all the W/L of each game (W:1, L:0), so we can plot them later for the results
    $AllGames = array();

    require_once("../GetGameLineup.php");
    require_once("../game.php");

    GetLineup($team, $year, $season);

    function GetLineup($team, $year, $season) {
        $gameNum = 0;
        // get the schedule
        require("../DBconn.php");

        $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $team or HomeTeam = $team) and year = $year limit 1");
        $sql->execute();
        foreach ($sql as $row => $cols) {
            $GetGameLineup = new GetGameLineup($team, $year, $season, $cols["AwayTeam"], $cols["HomeTeam"], $gameNum);
            $GetGameLineup->start();
            //for ($i = 0; $i < $cols["games"]; $i++) {
            for ($i = 0; $i<1; $i++) {
                PlayEachGame($GetGameLineup->teams, $cols["AwayTeam"], $cols["HomeTeam"], $gameNum);
                $gameNum++;
            }
        }
        $conn = null;
    }

    function PlayEachGame($teams, $AwayTeam, $HomeTeam, $gameNum) {
        $game = new game($teams, $team, $year, $season, $AwayTeam, $HomeTeam, $gameNum);
        $game->start();
        if ($team == $AwayTeam) {
            if ($game->teams[0]->score > $game->teams[1]->score) {
                $W++;
                array_push($AllGames, 1);
            }
            else {
                $L++;
                array_push($AllGames, 0);
            }
        }
        else {
            if ($game->teams[1]->score > $game->teams[0]->score) {
                $W++;
                array_push($AllGames, 1);
            }
            else {
                $L++;
                array_push($AllGames, 0);
            }
        }
    }
?>
