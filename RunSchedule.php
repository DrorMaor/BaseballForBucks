<?php
    require("DisplayErrors.php");

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

    foreach($sql as $row => $cols) {
        $results = "The " . $cols["city"] . " " . $cols["name"] . " went " . $cols["W"] . "-" . $cols["L"] . " in " . $year . "<br>";
        $results .= "In the simulated season with your lineup, they went " . $schedule->W . "-" . $schedule->L . ".";
        //echo json_encode($cols);
    }
    echo $results;
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
