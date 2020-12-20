<?php
    if (isset($_GET["id"])) {
        include("DBconn.php");
        $sql = $conn->prepare("select * from files where id = '" . $_GET["id"] . "'");
        $sql->execute();
        $rows = array();
        foreach($sql as $row => $cols) {
            //header("Content-length: $size");
            //header("Content-type: $type");
            header("Content-Disposition: attachment; filename=" . $cols["FileName"]);
            ob_clean();
            flush();
            echo $cols["file"];
            exit;
        }
    }
?>
