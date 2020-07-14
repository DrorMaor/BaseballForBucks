<?php

class game {
    public $teams = array();
    private $bti;  // Batting Team Index (too long to write full name each time)
    private $inning;
    private $count;

    // we allow up to 5 errors per game (both teams combined)
    // randomly, an error will occur, until this # is reached
    private $MaxErrors;
    private $ErrorCount;

    // this will increment by 1 with each top/bottom of inning, and will be a running total, but we can determine (for printing reasons) the actual inning # and top/bottom
    public $InningFrame;

    private $GameOver;

    private $year;
    private $AwayTeam;
    private $HomeTeam;
    private $gameNum;

    public function __construct($year, $AwayTeam, $HomeTeam, $gameNum) {
        $this->year = $year;
        $this->AwayTeam = $AwayTeam;
        $this->HomeTeam = $HomeTeam;
        $this->gameNum = $gameNum;

        require_once ("classes/inning.php");
        require_once ("classes/count.php");
        require_once ("classes/team.php");
        require_once ("classes/batter.php");
        require_once ("classes/pitcher.php");

        $this->bti = 0;
        $this->inning = new inning();
        $this->count = new count();
        $this->MaxErrors = floor($this->GetRand()*6);
        $this->ErrorCount = 0;
        $this->InningFrame = -1;
        $this->GameOver = -1;
    }

    function GetRand() {
        return rand (0, 999) / 1000;
    }

    function start() {
        $this->GetLineup();
        $this->PlayBall();
    }


    /////////////////
    // LINEUP code //
    /////////////////
    function GetLineup() {
        require ("DBconn.php");

        $team = new team();
        $team->batters = $this->GetBatters($conn, $this->AwayTeam, $this->year);
        $team->pitchers = $this->GetPitchers($conn, $this->AwayTeam, $this->year);
        $this->GetTeamData($conn, $team, $this->year, $this->AwayTeam);
        array_push($this->teams, $team);
        $team = new team();
        $team->batters = $this->GetBatters($conn, $this->HomeTeam, $this->year);
        $team->pitchers = $this->GetPitchers($conn, $this->HomeTeam, $this->year);
        $this->GetTeamData($conn, $team, $this->year, $this->HomeTeam);
        array_push($this->teams, $team);

        $conn = null;
    }

    function GetTeamData($conn, &$team, $year, $teamID) {
        $sql = $conn->prepare("select * from ActualTeams t inner join ActualSeasons s on s.team = t.id where t.id = $teamID and s.year = $year; ");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $team->city = $cols["city"];
            $team->name = $cols["name"];
            $team->W = $cols["W"];
            $team->L = $cols["L"];
        }
        $conn = null;
    }

    function GetBatters($conn, $team, $year) {
        $Team = new Team();
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
        $starterID = $starters[$gameNum % count($starters)];

        // here we remove all other starters besides this game's starter
        $counter = 0;
        foreach ($Team->pitchers as $pitcher) {
            if ($pitcher->type == "S" && $pitcher->id != $starterID)
                unset($Team->pitchers[$counter]);
            $counter++;
        }

        return $Team->pitchers;
    }
    /////////////////////
    // end LINEUP code //
    /////////////////////


    function PlayBall() {
        $this->teams[0]->AtBatNum = 0;
        $this->teams[1]->AtBatNum = 0;
        $this->StartInning();
    }

    function StartInning() {
        // reset the inning numbers
        $this->inning->runners = "000";
        $this->CheckPitchingChange();
        $this->inning->outs = 0;
        $this->InningFrame++;
        $this->bti = $this->InningFrame % 2;
        while ($this->inning->outs < 3)
            $this->DoAtBat();
        $this->EndInning();
    }

    function CheckPitchingChange() {
        $team = $this->teams[$this->bti];
        if ($team->pitchers[$team->pitcher]->AvgInnPerGame == $team->CurPitcherInns) {
            if (count($team->pitchers) > $team->pitcher + 1) {
                $this->teams[$this->bti]->pitcher++;
                $this->teams[$this->bti]->CurPitcherInns = 0;
            }
        }
    }

    function DoAtBat() {
        $this->count->balls = 0;
        $this->count->strikes = 0;

        // determine if it's a hit or out

        $team = $this->teams[$this->bti];
        $CurrBtr = $team->batters[$team->AtBatNum];

        // ERA3 is ERA adjusted to 3.0
        // (3.00 is a decent ERA, and anothing higher would make the batter stronger,
        //  and anything lower would make the batter weaker)
        $ERA3 = $team->pitchers[$team->pitcher]->ERA - 3.33;
        // GBOP = Getting Batter Out Percentage, we adjust the ERA3 based on the AVG
        // so we have a fair chance at a hit/out, based on both pitcher & batter
        $GBOP = $CurrBtr->AVG + ($ERA3 / 50);
        if ($this->GetRand() < $GBOP) {
            // he's on base

            // calculate type of hit %age
            $B2 = $CurrBtr->B2 / $CurrBtr->H;
            $B3 = $CurrBtr->B3 / $CurrBtr->H;
            $HR = $CurrBtr->HR / $CurrBtr->H;

            // determine which hit type (DoHit param is # of bases in hit)
            $r = $this->GetRand();
            if ($r < $HR)
                $this->DoHit(4);
            elseif ($r >= $HR && $r < ($HR + $B3) )
                $this->DoHit(3);
            elseif ($r >= ($HR + $B3) && $r < ($HR + $B3 + $B2) )
                $this->DoHit(2);
            else
                $this->DoHit(1);
        } elseif (!$this->TryError())
            $this->DoOut(false);  // he's out

        if ($this->inning->outs == 3)
            $this->EndInning();
    }

    function EndInning() {
        // all this occurs at the end of an inning, BEFORE we increment the inning # and frame
        // (which we do at the beginning of StartInning)

        $this->teams[$this->bti]->CurPitcherInns++;  // needed to determine if pitching change is due

        // determine whether to end the game, or start another inning
        if ($this->InningFrame == 16 && $this->teams[1]->score > $this->teams[0]->score)
            // bottom of the 9th, home team ahead
            $this->GameOver = 1;
        elseif ($this->InningFrame >= 17 && $this->bti == 1 && $this->teams[1]->score != $this->teams[0]->score)
            // extra innings, bottom of frame, any team ahead
            $this->GameOver = 1;
        else
            // 1-8 innings, or any other extra inning
            $this->StartInning();
    }

    function TryError() {
        $error = false;
        if ($this->ErrorCount < $this->MaxErrors) {
            // try throwing an error
            // (this is based on 80 atbats per game: 27 min per team, plus average 3 walks and 10 hits)
            if ($this->GetRand() < ($this->MaxErrors / 80)) {
                $error = true;
                $this->ErrorCount++;
                $this->AdvanceRunners(-2, -1);
                $this->AdvanceLineup();
                $this->DoAtBat();
            }
        }
        return $error;
    }

    function DoHit($bases) {
        // most base hits are out of the infield, so we assume them here
        $outfield = floor($this->GetRand()*3) + 7;  // left, center, or right field (nfk"m for runner scoring from second)
        $this->AdvanceRunners($bases, $outfield);
        $this->AdvanceLineup();
        $this->DoAtBat();
    }

    function AdvanceLineup() {
        $this->teams[$this->bti]->AtBatNum++;
        if ($this->teams[$this->bti]->AtBatNum % 9 == 0)
            $this->teams[$this->bti]->AtBatNum = 0;
    }

    function TryDoublePlay ($pos) {
        $dbTurned = true; // this will be the result (will be the default value here, unless it's set to false)

        switch ($this->inning->runners) {
            case "000":
                $dbTurned = false;
                break;
            case "100":
                $this->inning->runners = "000";
                break;
            case "010":
            case "001":
                $dbTurned = false;
                break;
            case "001":
            case "101":
                $this->inning->runners = "001";
                break;
            case "011":
                $dbTurned = false;
                break;
            case "111":
                $this->inning->runners = "110";
                break;
        }
        return $dbTurned;
    }

    function IncrementScore($runs, $HR) {
        $walkoff = false;
        if ($this->InningFrame >= 17 && $this->bti == 1 && ($this->teams[1]->score + $runs) > $this->teams[0]->score) {
            // in the bottom of the 9+ inning, test for a walkoff, and if so, only count the # of runs needed to win
            // (unless it's a HR, then all runs count)
            $walkoff = true;
            if (!$HR) {
                $runs = $this->teams[0]->score - $this->teams[1]->score + 1;
            }
        }
        $this->teams[$this->bti]->score += $runs;

        if ($walkoff)
            $this->GameOver = 1;
    }

    function AdvanceRunners($bases, $pos) {
        // bases: # of bases of hit
        // pos: defensive position where ball was hit (1 based)

        switch ($bases) {
            case -2: // error (assumed one base advance per runner, plus batter safe at first)
                switch ($this->inning->runners) {
                    case "000":
                        $this->inning->runners = "100";
                        break;
                    case "100":
                        $this->inning->runners = "110";
                        break;
                    case "010":
                        $this->inning->runners = "101";
                        break;
                    case "001":
                        $this->inning->runners = "100";
                        $this->IncrementScore(1, false);
                        break;
                    case "001":
                        $this->inning->runners = "111";
                        break;
                    case "101":
                        $this->inning->runners = "110";
                        break;
                        $this->IncrementScore(1, false);
                    case "011":
                        $this->inning->runners = "101";
                        $this->IncrementScore(1, false);
                        break;
                    case "111":
                        $this->IncrementScore(1, false);
                        break;
                }
                break;
            case -1: // out (sac fly)
                if ($pos >= 7 && substr($this->inning->runners, 2, 1) == "1" && $this->inning->outs < 2) {
                    // the other 2 baserunners stay the same
                    $this->inning->runners = substr_replace($this->inning->runners, "0", 2, 1);
                    $this->IncrementScore(1, false);
                }
                break;
            case 0: // walk
                switch ($this->inning->runners) {
                    case "000":
                        $this->inning->runners = "100";
                        break;
                    case "100":
                    case "010":
                        $this->inning->runners = "110";
                        break;
                    case "001":
                        $this->inning->runners = "101";
                        break;
                    case "001":
                    case "101":
                    case "011":
                        $this->inning->runners = "111";
                        break;
                    case "111":
                        $this->IncrementScore(1, false);
                        break;
                }
                break;
            // from now on these are # of bases in the hit
            case 1:
                switch ($this->inning->runners) {
                    case "000":
                        $this->inning->runners = "100";
                        break;
                    case "100":
                        if ($pos == 9)
                            // runner will advance from 1st to 3rd on a single to right
                            $this->inning->runners = "101";
                        else
                            $this->inning->runners = "110";
                        break;
                    case "010":
                        // runner will score from second
                    case "001":
                        $this->inning->runners = "100";
                        $this->IncrementScore(1, false);
                        break;
                    case "001":
                        if ($pos >= 8)
                            $this->inning->runners = "101";
                        else
                            $this->inning->runners = "110";

                        $this->IncrementScore(1, false);
                        break;
                    case "101":
                        if ($pos >= 8)
                            $this->inning->runners = "101";
                        else
                            $this->inning->runners = "110";

                        $this->IncrementScore(1, false);
                        break;
                    case "011":
                        if ($pos >= 8) {
                            $this->inning->runners = "100";
                            $this->IncrementScore(2, false);
                        } else {
                            $this->inning->runners = "101";
                            $this->IncrementScore(1, false);
                        }
                        break;
                    case "111":
                        if ($pos >= 8) {
                            $this->inning->runners = "101";
                            $this->IncrementScore(2, false);
                        } else {
                            $this->inning->runners = "111";
                            $this->IncrementScore(1, false);
                        }
                        break;
                }
                break;
            case 2:
                switch ($this->inning->runners) {
                    case "000":
                        break;
                    case "100":
                    case "010":
                    case "001":
                        $this->IncrementScore(1, false);
                        break;
                    case "001":
                    case "101":
                    case "011":
                        $this->IncrementScore(2, false);
                        break;
                    case "111":
                        $this->IncrementScore(3, false);
                        break;
                }
                $this->inning->runners = "010"; // will always clear the bases (besides for batter himself)
                break;
            case 3:
                switch ($this->inning->runners) {
                    case "000":
                        break;
                    case "100":
                    case "010":
                    case "001":
                        $this->IncrementScore(1, false);
                        break;
                    case "001":
                    case "101":
                    case "011":
                        $this->IncrementScore(2, false);
                        break;
                    case "111":
                        $this->IncrementScore(3, false);
                        break;
                }
                $this->inning->runners = "001"; // will always clear the bases
                break;
            case 4:
                switch ($this->inning->runners) {
                    case "000":
                        $this->IncrementScore(1, true);
                        break;
                    case "100":
                    case "010":
                    case "001":
                        $this->IncrementScore(2, true);
                        break;
                    case "001":
                    case "101":
                    case "011":
                        $this->IncrementScore(3, true);
                        break;
                    case "111":
                        $this->IncrementScore(4, true);
                        break;
                }
                $this->inning->runners = "000"; // will always clear the bases
                break;
        }
    }

    function DoOut($strikeout) {
        if (!$strikeout) {
            $r = $this->GetRand();
            $pos = 0;
            // much less likelihood that the pitcher or catcher will do the putout, so we give them a smaller probabililty
            if ($r < 0.0625)
                $pos = 1;
            elseif ($r >= 0.0625 && $r < 0.125)
                $pos = 2;
            elseif ($r >= 0.125 && $r < 0.25)
                $pos = 3;
            elseif ($r >= 0.25 && $r < 0.375)
                $pos = 4;
            elseif ($r >= 0.375 && $r < 0.5)
                $pos = 5;
            elseif ($r >= 0.5 && $r < 0.625)
                $pos = 6;
            elseif ($r >= 0.625 && $r < 0.75)
                $pos = 7;
            elseif ($r >= 0.75 && $r < 0.875)
                $pos = 8;
            else
                $pos = 9;

            if ($pos >= 7)
                $this->AdvanceRunners(-1, $pos);
            else
                // this following conditions have to be met for a double play to happen:
                // 1) there has to be less than 2 outs
                // 2) not a catcher (rare to have a catcher start a DP)
                // 3) when function TryDoublePlay returns true it only means that it's possible for a DP,
                //    but still there's a 10% chance it won't be turned
                if ($this->inning->outs < 2 && $pos != 2 && $this->TryDoublePlay($pos) && $this->GetRand() < 0.9)
                    $this->inning->outs++; // this will only be the EXTRA out
        }

        $this->inning->outs++;  // always increment the regular out
        $this->AdvanceLineup();
        if ($this->inning->outs == 3)
            $this->EndInning();
        else
            $this->DoAtBat();
    }
}
?>
