<?php

    $team = $_GET["team"];
    $year = $_GET["year"];
    $computer = $_GET["computer"];

    $rows = array();
    require("DBconn.php");
    if ($computer == 0) {
        $sql = $conn->prepare("select * from ActualBatters where team = $team and year = $year order by id");
        $sql->execute();
        foreach($sql as $row => $cols)
            array_push($rows, $cols);
    }
    else {
        // start lineup with top 5 highest AVG batters
        $sql = $conn->prepare("select * from ActualBatters where team = $team and year = $year order by AVG desc, HR desc limit 5");
        $sql->execute();
        foreach($sql as $row => $cols)
            array_push($rows, $cols);
        // get rest of lineup
        $inIDs = "";
        foreach($rows as $row)
            $inIDs .= $row["id"] .= ",";
        $inIDs = substr($inIDs, 0, -1);
        $sql = $conn->prepare("select * from ActualBatters where team = $team and year = $year and id not in ($inIDs) order by HR desc");
        $sql->execute();
        foreach($sql as $row => $cols)
            array_push($rows, $cols);
    }
    $conn = null;
    echo json_encode($rows);
?>
