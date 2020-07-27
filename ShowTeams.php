<table id="tblShowTeams">
<?php
	require("DBconn.php");
	$sql = $conn->prepare("select t.id teamID, t.city, t.name, min(s.year) min, max(s.year) max
		from ActualTeams t
		inner join ActualSchedules s on s.AwayTeam = t.id
		group by t.id
		order by t.city, t.name; ");
	$sql->execute();
	foreach($sql as $row => $cols) {
		$teamID = $cols['teamID'];
		$city = $cols['city'];
		$name = $cols['name'];
		echo "<tr class='tr' id='tr_$teamID'>";
		echo "<td class='td'>" . $city . " " . $name . "</td>";
		echo "<td class='td'> <select class='TeamYear' id='TeamYear_" . $teamID . "' ";
		echo "onclick='SelectTeamYear($teamID, \"$city\", \"$name\");'>";
		for ($y = $cols['min']; $y <= $cols['max']; $y++)
			echo "<option value='" . $teamID . "_" . $y . "'>" . $y;
		echo "</select> </td>";
		echo "</tr>";
	}
	$conn = null;
	?>
</table>
