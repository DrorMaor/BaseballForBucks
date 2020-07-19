<?php

class GetGameLineup {
    public $teams = array();
    private $team;
    private $year;
    private $season;
    private $AwayTeam;
    private $HomeTeam;
    private $gameNum;

    public function __construct($team, $year, $season, $AwayTeam, $HomeTeam, $gameNum) {
        $this->team = $team;
        $this->year = $year;
        $this->season = $season;
        $this->AwayTeam = $AwayTeam;
        $this->HomeTeam = $HomeTeam;
        $this->gameNum = $gameNum;

        require_once ("classes/team.php");
        require_once ("classes/batter.php");
        require_once ("classes/pitcher.php");
    }

    function start() {
        require("DBconn.php");
        $this->GetLineup($conn);
        $conn = null;
    }

    function GetLineup($conn) {
        // for the batters, if the home or away team is the one that the member is playing,
        // so we get his lineup instead of the actual one
        $this->DoEachTeam($this->AwayTeam, $conn);
        $this->DoEachTeam($this->HomeTeam, $conn);
    }

    function DoEachTeam($AwayHomeTeam, $conn) {
        $team = new team();
        $team = $this->GetTeamData($conn, $AwayHomeTeam, $this->year);
        if ($this->team == $AwayHomeTeam)
            $team->batters = $this->GetBatters($conn, -1, -1, $this->season);
        else
            $team->batters = $this->GetBatters($conn, $AwayHomeTeam, $this->year, -1);
        $team->pitchers = $this->GetPitchers($conn, $AwayHomeTeam, $this->year);
        array_push($this->teams, $team);
    }

    function GetTeamData($conn, $teamID, $year) {
        $team = new team();
        $sql = $conn->prepare("select * from ActualTeams t inner join ActualSeasons s on s.team = t.id where t.id = $teamID and s.year = $year; ");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $team->city = $cols["city"];
            $team->name = $cols["name"];
            $team->W = $cols["W"];
            $team->L = $cols["L"];
        }
        return $team;
    }

    function GetBatters($conn, $team, $year, $season) {
        $Team = new Team();
        if ($season != -1)
            $sql = $conn->prepare("select * from ActualBatters where id in (select batter from SeasonsLineup where season = $season);");
        else
            $sql = $conn->prepare("select * from ActualBatters where team = $team and year = $year;");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $b = new batter();
            $b->name = $cols["name"];
            $b->AVG = $cols["AVG"];
            $b->H = $cols["H"];
            $b->B2 = $cols["B2"];
            $b->B3 = $cols["B3"];
            $b->HR = $cols["HR"];
            array_push($Team->batters, $b);
        }
        return $Team->batters;
    }

    function GetPitchers($conn, $team, $year) {
        $id = 0;
        $Team = new Team();
        $sql = $conn->prepare("select * from ActualPitchers where team = $team and year = $year;");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $p = new pitcher();
            $p->id = $id;  // used to determine who's this game's starter
            $p->name = $cols["name"];
            $p->ERA = $cols["ERA"];
            $p->AvgInnPerGame = $cols["AvgInnPerGame"];
            $p->type = $cols['type'];  // (R/S) for Reliever or Starter
            array_push($Team->pitchers, $p);
            $id++;
        }

        // -----------------------------------------///
        // select the starter, based on the rotation //
        // -----------------------------------------///

        // put all starters in temp array
        $starters = array();
        foreach ($Team->pitchers as $pitcher) {
            if ($pitcher->type == "S")
                array_push($starters, $pitcher->id);
        }

        // determine today's starter
        $starterID = $starters[$this->gameNum % count($starters)];

        // here we remove all other starters besides this game's starter
        $counter = 0;
        foreach ($Team->pitchers as $pitcher) {
            if ($pitcher->type == "S" && $pitcher->id != $starterID)
                unset($Team->pitchers[$counter]);
            $counter++;
        }

        return $Team->pitchers;
    }
}
?>
