<?php
    include ("DBconn.php");
    $sql = $conn->prepare("select * from files where id = " . $_GET["FileID"]);
    $sql->execute();
    $rows = array();
    foreach($sql as $row => $cols)
        array_push($rows, $cols);
    echo json_encode($rows);
    $conn = null;
?>
