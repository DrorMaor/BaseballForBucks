var team = 0;
var year = 0;

function SelectTeamYear(teamID) {
    TeamYearSplit(teamID);
    $(".tr").css("background-color","white");
    $("#tr_" + team).css("background-color","yellow");
    CreateLineup();
}

function TeamYearSplit(teamID) {
    var TeamYear = $("#TeamYear_" + teamID).val().split('_');
    team = TeamYear[0];
    year = TeamYear[1];
}

$( function() {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
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
    $("#Step2 ul").empty();
    for (i = 0; i < json.length; i++)
        $("#Step2 ul").append("<li class='lineup' id='" + json[i].id + "'>" + json[i].name + "</li> ");
}

function RunSchedule() {
    $("#divResults").text("It'll take about 10-15 seconds to simulate all the games");
    $("#imgSpinBall").show();
    $.ajax({
        type: "GET",
        url: "RunSchedule.php?team=" + team + "&year=" + year + "&lineup=" + UserLineup(),
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#imgSpinBall").hide();
            DisplayScheduleResults(response);
        }
    });
}

function UserLineup() {
    var lineup = "";
    $("#sortable li").each(function(idx, li) {
        lineup += $(li).attr('id') + "|";
    });
    return lineup;
}

function DisplayScheduleResults(response) {
    //var json = JSON.parse(response);
    //var s = "the " + json.city + " " + json.name + " went " + json.W + "-" + json.L + " in " + json.year;
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