<?php
    include ("DBconn.php");
    $sql = $conn->prepare("delete from files where id = " . $_GET["FileID"]);
    $sql->execute();
    $conn = null;
?>
