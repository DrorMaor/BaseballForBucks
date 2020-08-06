var team = 0;
var year = 0;
var _city = "";
var _name = "";
var LineupPos = 0;

function SelectTeamYear(teamID, city, name) {
    _city = city;
    _name = name;
    TeamYearSplit(teamID);
    ToggleRowBGcolor();
    $("#tr_" + team).css("background-color","#4885e8");
    $("#tr_" + team).css("font-weight","bold");
    CreateLineup(0);
    //$("#divTools").empty();
    $("#GoogleTeam").attr("href", "https://www.google.com/search?q=" + city + "+" + name + "+" + year);
    $("#divTools").show();
    $("#PlayBall").show();
}

function TeamYearSplit(teamID) {
    var TeamYear = $("#TeamYear_" + teamID).val().split('_');
    team = TeamYear[0];
    year = TeamYear[1];
}

$( function() {
    $(document).tooltip();
    $("#ulLineup").sortable();
    $("#ulLineup").disableSelection();
    ToggleRowBGcolor();

    for (i = 0; i < 5; i++)
        $("#RunSchedule ul").append("<li class='AddedLineup_before'>&nbsp;</li>");
} );

$(window).load(function() {
    FeaturedFranchise();
});

function FeaturedFranchise () {
    // show the default (random) team
    $.ajax({
        type: "GET",
        url: "FeaturedFranchise.php?team=" + team + "&year=" + year,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            var json = JSON.parse(response);
            var ffText = "<div id='headFF'>Featured Franchise</div>";
            ffText +=  json.city + " " + json.name + " " + json.year;
            $("#FeaturedFranchise").html(ffText);
            // now, auto-click that  year to bring up the lineup
            $("#TeamYear_" + json.id).val(json.id + "_" + json.year);
            SelectTeamYear(json.id, json.city, json.name);
        }
    });
}

function ToggleRowBGcolor () {
    $(".tr:even").css("background-color", "#e1e6fc");
    $(".tr:odd").css("background-color", "#cfd8ff");
    $(".tr").css("font-weight", "normal");
}

function AddLineup() {
    LineupPos++;
    var table = "<table>";
    table += "<tr>";
    table += "<td style='width:220px;'>";
    table += "<strong>" + _city + " " + _name + " " + year + "</strong>";
    table += "</td>";
    table += "<td style='width:20px;'>";
    table += "<img class='tools' title='Remove this season' src='images/close.png' onclick='RemoveLineup(" + LineupPos + ");'>";
    table += "</td>";
    table += "</tr>";
    table += "</table>";
    $("#RunSchedule ul").prepend("<li class='AddedLineup_after' id='LineupPos_" + LineupPos + "'>"  + table + "</li> ");

    $('#RunSchedule li:last-child').remove();
    if ($('.AddedLineup_after').length == 5)
        $("#RightArrow").attr("onClick", "").css('cursor', 'not-allowed');
}

function RemoveLineup(LineupPos) {
    $("#LineupPos_" + LineupPos).remove();
    $("#RunSchedule ul").append("<li class='AddedLineup_before'>&nbsp;</li>");
    $("#RightArrow").attr("onClick", "AddLineup()").css('cursor', 'pointer');
}

function CreateLineup(computer) {
    // show actual season's summary
    $.ajax({
        type: "GET",
        url: "TeamSeasonSummary.php?team=" + team + "&year=" + year,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#ActualSeasonSummary").html(response).show();
        }
    });

    // show the actual lineup
    $.ajax({
        type: "GET",
        url: "GetLineup.php?team=" + team + "&year=" + year + "&computer=" + computer,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            DisplayLineupResults(response);
            $("#SimulatedSeasonResults").hide().html("");
        }
    });
}

function RunSchedule() {
    $("#SpinBall").show();
    $("#PlayBall").hide();
    $("#SimulatedSeasonResults").hide();
    $.ajax({
        type: "GET",
        url: "RunSchedule.php?team=" + team + "&year=" + year + "&lineup=" + UserLineup(),
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#SpinBall").hide();
            $("#PlayBall").show();
            DisplayScheduleResults(response);
        }
    });
}

function DisplayLineupResults(response) {
    var json = JSON.parse(response);
    $("#Step2 ul").empty();
    for (i = 0; i < json.length; i++) {
        var table = "<table>";
        table += "<tr>";
        table += "<td style='width:200px;'>";
        table += "<strong>" + json[i].name + "</strong><br>AVG: " + json[i].AVG.replace('0.', '.') + ", " + json[i].HR + " HR";
        table += "</td>";
        table += "<td style='width:50px;'>";
        table += "<a target='_blank' href='https://www.google.com/search?q=" + json[i].name +"'><img title='Research this player' src='images/g.png'></a>"
        table += "</td>";
        table += "</tr>";
        table += "</table>";
        $("#Step2 ul").append("<li class='lineup' id='" + json[i].id + "'>" + table + "</li> ");
    }

}

function UserLineup() {
    var lineup = "";
    $("#ulLineup li").each(function(idx, li) {
        lineup += $(li).attr('id') + "|";
    });
    return lineup;
}

function DisplayScheduleResults(response) {
    var json = JSON.parse(response);
    var msg = "With your lineup, the " + _city + " " + _name + " would have gone " + json.W + "-" + json.L + " in " + json.year ;
    $("#SimulatedSeasonResults").text(msg).show();


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
