<?php

class GetGameLineup {
    public $teams = array();
    private $team;
    private $year;
    private $season;
    private $AwayTeam;
    private $HomeTeam;
    private $GameNum;

    public function __construct($team, $year, $season, $AwayTeam, $HomeTeam, $GameNum) {
        $this->team = $team;
        $this->year = $year;
        $this->season = $season;
        $this->AwayTeam = $AwayTeam;
        $this->HomeTeam = $HomeTeam;
        $this->GameNum = $GameNum;

        require_once ("DataClasses/team.php");
        require_once ("DataClasses/batter.php");
    }

    function start() {
        require("DBconn.php");
        $this->GetLineup($conn);
        $conn = null;
    }

    function GetLineup($conn) {
        // for the batters, if the home or away team is the one that the member is playing,
        // so we get his lineup instead of the actual one
        array_push($this->teams, $this->DoEachTeam($this->AwayTeam, $conn));
        array_push($this->teams, $this->DoEachTeam($this->HomeTeam, $conn));
    }

    function DoEachTeam($AwayHomeTeam, $conn) {
        $team = new team();
        $team = $this->GetTeamData($conn, $AwayHomeTeam, $this->year);
        $team->batters = $this->GetBatters($conn, $AwayHomeTeam, $this->year, $this->season);
        return $team;
    }

    function GetTeamData($conn, $teamID, $year) {
        $team = new team();
        $sql = $conn->prepare("select * from ActualTeams t inner join ActualSeasons s on s.team = t.id where t.id = $teamID and s.year = $year");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $team->city = $cols["city"];
            $team->name = $cols["name"];
            $team->W = $cols["W"];
            $team->L = $cols["L"];
            $team->HomeW = $cols["HomeW"];
            $team->HomeL = $cols["HomeL"];
            $team->AwayW = $cols["AwayW"];
            $team->AwayL = $cols["AwayL"];
        }
        return $team;
    }

    function GetBatters($conn, $AwayHomeTeam, $year, $season) {
        $team = new team();
        if ($season != -1 && $team == $AwayHomeTeam)
            $sql = $conn->prepare("select * from ActualBatters where id in (select batter from SeasonsLineup where season = $season)");
        else
            $sql = $conn->prepare("select * from ActualBatters where team = $AwayHomeTeam and year = $year");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $b = new batter();
            $b->name = $cols["name"];
            $b->AVG = $cols["AVG"];
            $b->H = $cols["H"];
            $b->B2 = $cols["B2"];
            $b->B3 = $cols["B3"];
            $b->HR = $cols["HR"];
            array_push($team->batters, $b);
        }
        return $team->batters;
    }
}
?>
