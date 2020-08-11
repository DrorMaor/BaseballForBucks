<?php
    require_once("DBconn.php");
    $sql = $conn->prepare("insert into traffic (referer, ip) values ('" . $_SERVER['HTTP_REFERER'] . "', '" . $_SERVER['REMOTE_ADDR'] . "') ");
    $sql->execute();
    $conn = null;
?>
