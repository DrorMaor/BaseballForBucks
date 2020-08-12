<?php
    require("DBconn.php");
    $sql = $conn->prepare("
        select t.id, s.year, t.city, t.name
        from ActualTeams t inner join ActualSeasons s on s.team = t.id
        where s.team = (select id from ActualTeams order by rand() limit 1)
            and (s.year < (select BegYear from ActualSeasonsExceptions where team = s.team) or s.year > (select EndYear from ActualSeasonsExceptions where team = s.team) )
        order by rand() limit 1;
    ");
    $sql->execute();
    foreach($sql as $row => $cols)
        echo json_encode($cols);
    $conn = null;
?>
