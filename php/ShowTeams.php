<table id="tblShowTeams">
<?php
	require("DBconn.php");
	$sql = $conn->prepare("
            select t.id teamID, t.city, t.name, min(s.year) min, max(s.year) max, 
                ifnull(se.BegYear, 0) BegYearEx, ifnull(se.EndYear, 0) EndYearEx
            from ActualTeams t
                inner join ActualSchedules s on s.AwayTeam = t.id
                left join ActualSeasonsExceptions se on se.team = t.id
            group by t.id
            order by t.city, t.name");
	$sql->execute();
	foreach($sql as $row => $cols) {
            $teamID = $cols['teamID'];
            $city = $cols['city'];
            $name = $cols['name'];
            echo "<tr class='tr' id='tr_$teamID'>";
            echo "<td class='td'>" . $city . " " . $name;
            if ($name == "Angels")
            {
                $angels = "&nbsp; ";
                $angels .= "<a href='https://www.google.com/search?q=History of the Los Angeles Angels' target='_blank' class='tools td'>";
                $angels .= "<img title='History of the Los Angeles Angels' src='images/google.png' style='height:50%;'></a>";
                echo $angels;
            }
            echo "</td>";
            echo "<td class='td'> <select class='TeamYear' id='TeamYear_" . $teamID . "' ";
            echo "onclick='SelectTeamYear($teamID, \"$city\", \"$name\");'>";
            for ($y = $cols['min']; $y <= $cols['max']; $y++)
                if ( !($y >= $cols['BegYearEx'] && $y <= $cols["EndYearEx"]) )
                    echo "<option value='" . $teamID . "_" . $y . "'>" . $y;
            echo "</select> </td>";
            echo "</tr>";
	}
	$conn = null;
	?>
</table>
