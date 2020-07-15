<table>
<?php
	require("DBconn.php");
	$sql = $conn->prepare("select t.id teamID, t.city, t.name, min(s.year) min, max(s.year) max
		from ActualTeams t
		inner join ActualSchedules s on s.AwayTeam = t.id
		group by t.id
		order by t.city, t.name; ");
	$sql->execute();
	foreach($sql as $row => $cols) {
		$TeamID = $cols['teamID'];
		echo "<tr class='tr' id='tr_$TeamID'>";
		echo "<td>".$cols['city']." ".$cols['name']."</td>";
		echo "<td> <select id='TeamYear_$TeamID' onclick='SelectTeamYear($TeamID);'>";
		for ($y = $cols['min']; $y <= $cols['max']; $y++)
			echo "<option value='".$TeamID."_".$y."'>".$y;
		echo "</select> </td>";
		echo "</tr>";
	}
	$conn = null;
	?>
</table>
