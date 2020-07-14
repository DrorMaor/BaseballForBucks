<?php
	if (true) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
?>

<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="scripts.js"></script>
		<link rel="stylesheet" href="styles.css">
	</head>
<body>
<div id='divTeams'>
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
</div>

<div>
	<br>
	<input type="radio" id="actual" name="lineup" checked>
	<label for="actual">Use actual lineup</label>
	<br>
	<input type="radio" id="create" name="lineup" onclick="CreateLineup();">
	<label for="create">Create your own lineup</label><br>
	<br>
	<button id='btnRunSchedule' onclick='RunSchedule();'>Run</button>
</div>

<div id="divLineup">

</div>

<div id="divResults"></div>
<img id="imgSpinBall" src="ball.png" alt="">
</body>
</html>
