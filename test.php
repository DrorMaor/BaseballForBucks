<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once ("DBconn.php");
require_once ("classes/pitcher.php");
require_once ("classes/team.php");
GetPitchers($conn, 40, 1971, 117);
$conn=null;

function GetPitchers($conn, $team, $year, $gameNum) {
    $id = 0;
    $Team = new Team();
    $sql = $conn->prepare("select * from ActualPitchers where team = $team and year = $year;");
    $sql->execute();
    foreach ($sql as $row => $cols) {
        $p = new pitcher();
        $p->id = $id;  // used to determine who's this game's starter
        $p->name = $cols["name"];
        $p->ERA = $cols["ERA"];
        $p->AvgInnPerGame = $cols["AvgInnPerGame"];
        $p->type = $cols['type'];  // (R/S) for Reliever or Starter
        array_push($Team->pitchers, $p);
        $id++;
    }
    print_r($Team->pitchers);

    // select the starter, based on the rotation
    $starters = array();
    foreach ($Team->pitchers as $pitcher) {
        if ($pitcher->type == "S")
            array_push($starters, $pitcher->id);
    }
    echo "<br>";
    print_r($starters);
    echo "<br>";
    $starterID = $starters[$gameNum % count($starters)];
    echo "---> $starterID <--- <br>";
    $counter = 0;
    foreach ($Team->pitchers as $pitcher) {
        if ($pitcher->type == "S" && $pitcher->id != $starterID)
            unset($Team->pitchers[$counter]);
        $counter++;
    }
    print_r($Team->pitchers);
}

?>
