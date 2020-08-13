<?php
    //include ("DisplayErrors.php");
    $msg = $_GET["contact"] . "\r---------\r" . $_GET["comments"] . "\r";
    $msg .= "---------------------------\r\r";
    file_put_contents("../ContactUs", $msg, FILE_APPEND);
    mail("dror.m.maor@gmail.com", "BFB msg", $msg);
?>
