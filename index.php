<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script>
		var team = 0;
		var year = 0;
		function SelectTeamYear(teamID)
		{
			var TeamYear = $("#TeamYear_" + teamID).val().split('_');
			team = TeamYear[0];
			year = TeamYear[1];
			$(".tr").css("background-color","white");
			$("#tr_" + team).css("background-color","yellow");
		}

		function RunSchedule(){
			$(".loader").show();
			$.ajax({
				type: "GET",
				url: "RunSchedule.php?team=" + team + "&year=" + year,
				data: $(this).serialize(),
				dataType: 'text',
				success: function(response){
					$(".loader").hide();
					DisplayResults(response);
				}
			});
		}

		function DisplayResults(response) {
			alert(response);
			var json = JSON.parse(response);
			alert (json);
			/*
			var s = "the " + json.team + " went " + json.W + "-" + json.L + " in " + json.year;
			$("#divResults").text(s);
			alert(s);
			*/
		}
	</script>

	<style>
		.loader {
			display:none;
		  border: 16px solid #f3f3f3; /* Light grey */
		  border-top: 16px solid #3498db; /* Blue */
		  border-radius: 50%;
		  width: 120px;
		  height: 120px;
		  animation: spin 2s linear infinite;
		}
		@keyframes spin {
		  0% { transform: rotate(0deg); }
		  100% { transform: rotate(360deg); }
		}
	</style>
</head>
<body>
<?php
	if (true) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

	require_once("DBconn.php");
	echo "<div style='height:350px; width:350px; overflow-y: scroll;'>";
	echo "<table>";
	$sql = $conn->prepare("
		select t.id teamID, t.city, t.name, min(s.year) min, max(s.year) max
		from ActualTeams t
		inner join ActualSeasons s on s.team = t.id
		group by t.id
		order by t.city, t.name; ");
	$sql->execute();
	foreach($sql as $row => $cols) {
		$TeamID = $cols['teamID'];
		echo "<tr class='tr' id='tr_$TeamID'>";
		echo "<td>".$cols['city']." ".$cols['name']."</td>";
		echo "<td> <select id='TeamYear_$TeamID'>";
		for ($y = $cols['min']; $y <= $cols['max']; $y++)
			echo "<option value='".$TeamID."_".$y."'>".$y;
		echo "</select> </td>";
		echo "<td> <a style='cursor:pointer;' onclick='SelectTeamYear($TeamID);'>choose</a> </td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "<button id='RunSchedule' onclick='RunSchedule();'>Run</button>";
	$conn = null;
?>
<div id="divResults"></div>
<div class="loader"></div>
</body>
</html>
