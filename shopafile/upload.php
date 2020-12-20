<?php
    include ("DBconn.php");
    include ("DisplayErrors.php");
    $description = $_POST["description"];
    $price = $_POST["price"];
    $image = $_FILES["image"]["tmp_name"];
    $fileName = basename($_FILES["file"]["name"]);
    $fileFile = $_FILES["file"]["tmp_name"];
    $insert = "INSERT INTO files (user, FileName, price, description, image, file)
        values (1, '$fileName', $price, '$description',
        '" . base64_encode(file_get_contents($image)) . "', '" . base64_encode(file_get_contents($fileFile)) . "')";
    //echo $insert;
    $sql = $conn->prepare($insert);
    $sql->execute();
    $conn = null;
?>
