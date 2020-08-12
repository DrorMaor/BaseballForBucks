<?php
    require("DBconn.php");
    $sql = $conn->prepare("select * from traffic order by id desc");
    $sql->execute();
    echo "<table border=1>";
    foreach ($sql as $row => $cols) {
        echo "<tr>";
        echo "<td>" . $cols["id"] . "</td>";
        echo "<td>" . $cols["referer"] . "</td>";
        echo "<td>" . $cols["ip"] . "</td>";
        echo "<td>" . $cols["theTime"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    $conn = null;
?>
