<?php
    require_once ("../DisplayErrors.php");

    $team = 16;
    $year = 2016;
    $season = -1;
    $W = 0;
    $L = 0;

    require_once("../GetGameLineup.php");
    require_once("../QuickGame.php");

    GetLineup($team, $year, $season);

    echo $W . "-" . $L. "<br>";

    function GetLineup($team, $year, $season) {
        $GameNum = 0;
        // get the schedule
        require("../DBconn.php");

        $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $team or HomeTeam = $team) and year = $year");
        $sql->execute();
        foreach ($sql as $row => $cols) {
            $GetGameLineup = new GetGameLineup($team, $year, $season, $cols["AwayTeam"], $cols["HomeTeam"], $GameNum);
            $GetGameLineup->start();
            for ($i = 0; $i < $cols["games"]; $i++) {
                PlayQuickGame($GetGameLineup->teams, $team, $cols["HomeTeam"], $GameNum);
                $GameNum++;
            }
        }
        $conn = null;
    }

    function PlayQuickGame($teams, $team, $HomeTeam, $GameNum) {
        $QuickGame = new QuickGame($teams, $team, $HomeTeam, $GameNum);
        $QuickGame->start();
        global $W, $L;
        if ($QuickGame->outcome[0] > $QuickGame->outcome[1])
            $W++;
        else
            $L++;
    }
?>
