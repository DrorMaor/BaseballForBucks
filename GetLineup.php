<?php
    $team = $_GET["team"];
    $year = $_GET["year"];

    require("DBconn.php");
    $sql = $conn->prepare("select * from ActualBatters where team = $team and year = $year;");
    $sql->execute();
    $rows = array();
    foreach($sql as $row => $cols)
        array_push($rows, $cols);
    $conn = null;
    echo json_encode($rows);
?>
