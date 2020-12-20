<div id="files">
    <h3>My Files</h3>
    <?php
        include ("DisplayErrors.php");
        include ("DBconn.php");
        $sql = $conn->prepare("select * from files where user = 1 order by UploadTime desc;");
        $sql->execute();
        $rows = array();
        ?>
        <a class="button greenBG" title="Upload a new file to sell" onclick="ShowUploadForm();">Sell</a>
        <br>
        <table id="tblFiles">
            <tr>
                <th class="left">File Name</th>
                <th class="center">Price</th>
                <th class="left">Description</th>
                <th class="center">Views</th>
                <th class="center">Downloads</th>
                <th class="center">Earnings</th>
            </tr>
        <?php
        foreach($sql as $row => $cols) {
            $id = $cols["id"];
            echo "<tr>";
            echo "<td><span onmouseout='$(\"#Image$id\").hide();' onmouseover='$(\"#Image$id\").show();'>" . $cols["FileName"] . "</span></td>";
            echo "<span class='FileImage' id='Image".$id."'><img style='width:100px;' src='data:image;base64," . $cols['image'] . "'/></span>";
            echo "<td class='TablePadding'>$" . number_format($cols["price"], 2) . "</td>";
            echo "<td class='TablePadding left'>";
            $description = $cols["description"];
            if (strlen($description) <= 40)
                echo $description;
            else
                echo "<span title='$description'>" . substr($description, 0, 40) . " ...</span>";
            echo "</td>";
            echo "<td class='TablePadding center'>" . $cols["views"] . "</td>";
            echo "<td class='TablePadding center'>" . $cols["downloads"] . "</td>";
            echo "<td class='TablePadding center'>$" . number_format($cols["earnings"], 2) . "</td>";
            echo "<td class='TablePadding'>";
            echo "<a class='RepeatButton orange' onclick='EditFile(" . $cols["id"] . ");'>Edit</a> &nbsp;";
            echo "<a class='RepeatButton red' onclick='DeleteFile(" . $cols["id"] . ");'>Delete</a> </td>";
            echo "</tr>";
        }
        echo "</table>";
        $conn = null;
    ?>
</div>
