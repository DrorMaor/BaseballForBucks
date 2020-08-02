<?php
    //require_once ("../DisplayErrors.php");

    $team = 38;
    $year = 1982;
    $season = -1;
    $W = 0;
    $L = 0;

    require_once("../GetGameLineup.php");
    require_once("../QuickGame.php");

    GetLineup($team, $year, $season);
    
    function GetLineup($team, $year, $season) {
        $gameNum = 0;
        // get the schedule
        require("../DBconn.php");

        $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $team or HomeTeam = $team) and year = $year");
        $sql->execute();
        foreach ($sql as $row => $cols) {
            $GetGameLineup = new GetGameLineup($team, $year, $season, $cols["AwayTeam"], $cols["HomeTeam"], $gameNum);          
            $GetGameLineup->start();
            for ($i = 0; $i < $cols["games"]; $i++) {
                PlayQuickGame($GetGameLineup->teams, $team, $cols["HomeTeam"]);
                $gameNum++;
            }
        }
        $conn = null;
    }

    function PlayQuickGame($teams, $team, $HomeTeam) {
        $QuickGame = new QuickGame($teams, $team, $HomeTeam);
        $QuickGame->start();
    }
?>
