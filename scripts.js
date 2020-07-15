var team = 0;
var year = 0;

function SelectTeamYear(teamID) {
    TeamYearSplit(teamID);
    $(".tr").css("background-color","white");
    $("#tr_" + team).css("background-color","yellow");
}

function TeamYearSplit(teamID) {
    var TeamYear = $("#TeamYear_" + teamID).val().split('_');
    team = TeamYear[0];
    year = TeamYear[1];
}

/////////////////////////
// drag/drop functions //
/////////////////////////

$(document).ready(function() {
    team = 38;
    year = 1982;
    CreateLineup();
    $( ".draggable" ).draggable();


    $( ".droppable" ).droppable({
      drop: function( event, ui ) {
        $( this )
          .css( "background-color", "yellow" );
      }
});
} );


function CreateLineup() {
    $.ajax({
        type: "GET",
        url: "GetLineup.php?team=" + team + "&year=" + year,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            DisplayLineupResults(response);
        }
    });
}

function DisplayLineupResults(response) {
    var json = JSON.parse(response);
    lineup = "<table >";
    lineup += "<tr><td>Actual Lineup</td> <td>Your Lineup</td> </tr>";
    for (i = 0; i < json.length; i++) {
        lineup += "<tr> <td class='ActualLineup lineup draggable' draggable='true' id='al_" + json[i].id + "'>" + json[i].name + "</td> ";
        lineup += "<td class='LineupPosition lineup droppable' id='pos_" + i + "'>&nbsp;</td> </tr>";
    }
    lineup += "</table>";
    $("#divLineup").html(lineup);
}

function RunSchedule() {
    $("#divResults").text("It'll take about 10-15 seconds to simulate all the games");
    $("#imgSpinBall").show();
    $.ajax({
        type: "GET",
        url: "RunSchedule.php?team=" + team + "&year=" + year,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#imgSpinBall").hide();
            DisplayScheduleResults(response);
        }
    });
}

function DisplayScheduleResults(response) {
    var json = JSON.parse(response);
    var s = "the " + json.city + " " + json.name + " went " + json.W + "-" + json.L + " in " + json.year;
    $("#divResults").text(response);


    /*
    $totalActualGames = $cols["W"] + $cols["L"];
    $totalSimulatedGames = $schedule->W + $schedule->L;
    if ($totalActualGames != $totalSimulatedGames)
        $schedule->L = $totalActualGames - $cols["W"];
    $results = "The " . $cols["city"] . " " . $cols["name"] . " went " . $cols["W"] . "-" . $cols["L"] . " in the actual " . $year . " season. ";
    $results .= "With your lineup, the computer simulated their season, and they went " . $schedule->W . "-" . $schedule->L . ".";
    if ($schedule->W / $totalSimulatedGames > $cols["W"] / $totalActualGames) {
        $pct = ($schedule->W / $totalSimulatedGames) - ($cols["W"] / $totalActualGames);
        $results .= " Your lineup was " . number_format($pct * 100, 0) . "% better than the actual lineup !";
    }
    */
}
