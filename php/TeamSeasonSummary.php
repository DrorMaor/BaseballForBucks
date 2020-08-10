<?php
    $team = $_GET["team"];
    $year = $_GET["year"];

    require("DBconn.php");
    $sql = $conn->prepare("select * from ActualTeams t inner join ActualSeasons s on s.team = t.id where t.id = $team and s.year = $year;");
    $sql->execute();
    $rows = array();

    foreach($sql as $row => $cols)
        echo json_encode($cols);
    $conn = null;
?>
