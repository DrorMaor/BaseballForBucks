var team = 0;
var year = 0;
var _city = "";
var _name = "";
var TeamSeasonSummary = "";
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
}

function TeamYearSplit(teamID) {
    var TeamYear = $("#TeamYear_" + teamID).val().split('_');
    team = TeamYear[0];
    year = TeamYear[1];
}

$(function() {
    $(document).tooltip();
    $("#ulLineup").sortable();
    $("#divAbout").draggable();
    $("#divContactUs").draggable();
    $("#ulLineup").disableSelection();
    ToggleRowBGcolor();
});

$(window).load(function() {
    FeaturedFranchise();
});

function FeaturedFranchise () {
    // show the default (random) team
    $.ajax({
        type: "GET",
        url: "php/FeaturedFranchise.php?team=" + team + "&year=" + year,
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

function SendComments() {
    $.ajax({
        type: "GET",
        url: "php/SendComments.php?contact=" + $("#txtContact").val() + "&comments=" + $("#txtComments").val(),
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $('#ContactThanks').show();
            $('#divContactUs').fadeOut(5000).promise().done(function() {
                $('#ContactThanks').hide();
                $("#txtContact").val("");
                $("#txtComments").val("");
            });
        }
    });
}

function ToggleRowBGcolor () {
    $(".tr:even").css("background-color", "#e1e6fc");
    $(".tr:odd").css("background-color", "#cfd8ff");
    $(".tr").css("font-weight", "normal");
}

function CreateLineup(computer) {
    // show actual season's summary
    $.ajax({
        type: "GET",
        url: "php/TeamSeasonSummary.php?team=" + team + "&year=" + year,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            TeamSeasonSummary = JSON.parse(response);
            var msg = "The " + TeamSeasonSummary.city + " " + TeamSeasonSummary.name + " went " + TeamSeasonSummary.W + "-" + TeamSeasonSummary.L + " in " + TeamSeasonSummary.year;
            $("#ActualSeasonSummary").html(msg).show();
            $("#btnPlayBall").show();
        }
    });

    // show the actual lineup
    $.ajax({
        type: "GET",
        url: "php/GetLineup.php?team=" + team + "&year=" + year + "&computer=" + computer,
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
    $("#btnPlayBall").hide();
    $("#SimulatedSeasonResults").hide();

    var lineup = "";
    $("#ulLineup li").each(function(idx, li) {
        lineup += $(li).attr('id') + "|";
    });
    $.ajax({
        type: "GET",
        url: "php/RunSchedule.php?team=" + team + "&year=" + year + "&lineup=" + lineup,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#SpinBall").hide();
            DisplayScheduleResults(response);
            $("#btnPlayBall").show();
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

function DisplayScheduleResults(response) {
    try {
        var json = JSON.parse(response);
        var msg = "With your lineup, the " + _name + " would have gone " + json.W + "-" + json.L + " in " + year;

        var AllHighlights = "";
        for (var season in json.highlights) {
            if (json.highlights[season] != null) {
                for (var game in season) {
                    if (json.highlights[season][game] != null) {
                        AllHighlights += "<li>" + json.highlights[season][game] + "</li>";
                    }
                }
            }
        }
        if (AllHighlights != "") {
            msg += "<br><br>Here are some season highlights:";
            msg += "<div style='text-align:left;'><ul>" + AllHighlights + "</ul></div>";
        }
        $("#SimulatedSeasonResults").css("background-color", "#000c2b").html(msg).show();
    }
    catch(err) {
        $("#SimulatedSeasonResults").css("background-color", "red").html("An error occurred. Please try again.").show();
    }
}
