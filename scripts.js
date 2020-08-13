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
    $("#divTools").show();
    $("#btnPlayBall").show();
}

function TeamYearSplit(teamID) {
    var TeamYear = $("#TeamYear_" + teamID).val().split('_');
    team = TeamYear[0];
    year = TeamYear[1];
}

$( function() {
    $(document).tooltip();
    $("#ulLineup").sortable();
    $("#divAbout").draggable();
    $("#divContactUs").draggable();
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

    // add the lineup to this season
    var lineup = "";
    $(".lineup").each(function() {
        lineup += $(this).attr('id') + "|";
    });
    $("#RunSchedule ul").prepend("<li class='AddedLineup_after' id='LineupPos_" + LineupPos + "' team='" + team + "' year='" + year + "' W='" + TeamSeasonSummary.W + "' L='" + TeamSeasonSummary.L + "' lineup='" + lineup + "'>"  + table + "</li> ");

    $('#RunSchedule li:last-child').remove();
    if ($('.AddedLineup_after').length == 5) {
        $("#RightArrow").attr("onClick", "").css('cursor', 'not-allowed');
        $("#btnPlayBall").attr("onClick", "RunSchedule()").css('cursor', 'pointer').css("opacity", "1");
    }
}

function RemoveLineup(LineupPos) {
    $("#LineupPos_" + LineupPos).remove();
    $("#RunSchedule ul").append("<li class='AddedLineup_before'>&nbsp;</li>");
    $("#RightArrow").attr("onClick", "AddLineup()").css('cursor', 'pointer');
    $("#btnPlayBall").attr("onClick", "").css('cursor', 'not-allowed').css("opacity", ".5");
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
    // get all data to PHP file
    var teams = [];
    var years = [];
    var lineups = [];
    $(".AddedLineup_after").each(function() {
        teams.push($(this).attr('team'));
        years.push($(this).attr('year'));
        lineups.push($(this).attr('lineup'));
    });

    $.ajax({
        type: "GET",
        url: "php/RunSchedule.php?teams=" + teams + "&years=" + years + "&lineups=" + lineups,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            $("#SpinBall").hide();
            $("#btnPlayBall").show();
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

function DisplayScheduleResults(response) {
    var json = JSON.parse(response);
    var W = 0;
    var L = 0;
    $(".AddedLineup_after").each(function() {
        W += parseInt($(this).attr('W'));
        L += parseInt($(this).attr('L'));
    });
    var AvgW = (W / 5).toFixed(0);
    var AvgL = (L / 5).toFixed(0);
    var msg = "The teams you selected had an average record of " + AvgW + "-" + AvgL + "<br>";
    msg += "With your lineups, their simulated average record was " + (json.W / 5).toFixed(0) + "-" + ( (parseInt(AvgW) + parseInt(AvgL)) - (json.W / 5).toFixed(0) ) + ".";
    $("#SimulatedSeasonResults").html(msg).show();
}
