<?php

class game {
    private $teams = array();
    private $bti;  // Batting Team Index (too long to write full name each time)
    private $inning;
    private $count;

    // we allow up to 5 errors per game (both teams combined)
    // randomly, an error will occur, until this # is reached
    private $MaxErrors;
    private $ErrorCount;

    // this will increment by 1 with each top/bottom of inning, and will be a running total, but we can determine (for printing reasons) the actual inning # and top/bottom
    private $InningFrame;

    public function __construct() {
        require_once ("classes/inning.php");
        require_once ("classes/count.php");
        require_once ("classes/team.php");
        require_once ("classes/batter.php");
        require_once ("classes/BatterHitsPct.php");
        require_once ("classes/pitcher.php");

        $this->bti = 0;
        $this->inning = new inning();
        $this->count = new count();
        $this->MaxErrors = floor($this->GetRand()*6);
        $this->ErrorCount = 0;
        $this->InningFrame = -1;
    }

    function GetRand() {
        return rand (0, 999) / 1000;
    }

    function main() {
        $this->GetLineup();
        $this->PlayBall();
    }


    /////////////////
    // LINEUP code //
    /////////////////
    function GetLineup() {
        $AwayTeam = $_GET["AwayTeam"];
        $AwayYear = $_GET["AwayYear"];
        $HomeTeam = $_GET["HomeTeam"];
        $HomeYear = $_GET["HomeYear"];
        require ("DBconn.php");

        $team = new team();
        $team->batters = $this->GetBatters($conn, $AwayTeam, $AwayYear);
        $team->pitchers = $this->GetPitchers($conn, $AwayTeam, $AwayYear);
        array_push($this->teams, $team);
        $team = new team();
        $team->batters = $this->GetBatters($conn, $HomeTeam, $HomeYear);
        $team->pitchers = $this->GetPitchers($conn, $HomeTeam, $HomeYear);
        array_push($this->teams, $team);

        $conn = null;
    }

    function GetBatters($conn, $team, $year) {
        $Team = new Team();
        $sql = $conn->prepare("SELECT * from ActualBatters where team = $team and year = $year");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $b = new batter();
            $b->name = $cols["name"];
            $b->AVG = $cols["AVG"];
            $bhp = new BatterHitsPct();
            $bhp->DBL = $cols["B2"];
            $bhp->TPL = $cols["B3"];
            $bhp->HR = $cols["HR"];
            $b->BatterHitsPct = $bhp;
            array_push($Team->batters, $b);
    	}
        return $Team->batters;
    }

    function GetPitchers($conn, $team, $year) {
        $Team = new Team();
        $sql = $conn->prepare("SELECT * from ActualPitchers where team = $team and year = $year");
        $sql->execute();
        foreach($sql as $row => $cols) {
            $p = new pitcher();
            $p->name = $cols["name"];
            $p->ERA = $cols["ERA"];
            $p->AvgInnPerGame = $cols["AvgInnPerGame"];
            $p->RS = $cols['type'];
            array_push($Team->pitchers, $p);
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

    function GameOver() {
        echo "final score: " . $this->teams[0]->score . "-" . $this->teams[1]->score;
        exit();
    }

    function StartInning() {
        // reset the inning numbers
        $this->SetRunnersStatus(array("!", "!", "!"));
        $this->CheckPitchingChange();
        $this->inning->outs = 0;
        $this->InningFrame++;
        $this->bti = $this->InningFrame % 2;
        while (true) {
            $this->DoAtBat();
            if ($this->inning->outs == 3) {
                $this->EndInning();
                break;
            }
        }
    }

    function CheckPitchingChange() {
        $team = $this->teams[$this->bti];
        if ($team->pitchers[$team->pitcher]->AvgInnPerGame == $team->CurPitcherInns)
            if (count($team->pitchers) > $team->pitcher + 1) {
                $this->teams[$this->bti]->pitcher++;
                $this->teams[$this->bti]->CurPitcherInns = 0;
            }
    }

    function DoAtBat() {
        $this->count->balls = 0;
        $this->count->strikes = 0;
        while (true) {
            if ($this->count->balls < 4 && $this->count->strikes < 3)
                $this->DoPitch();
            elseif ($this->inning->outs == 3)
                $this->EndInning();
        }
    }

    function EndInning() {
        // all this occurs at the end of an inning, BEFORE we increment the inning # and frame
        // (which we do at the beginning of StartInning)

        $this->teams[$this->bti]->CurPitcherInns++;  // needed to determine if pitching change is due

        // determine whether to end the game, or start another inning
        if ($this->InningFrame == 16 && $this->teams[1]->score > $this->teams[0]->score)
            // bottom of the 9th, home team ahead
            $this->GameOver();
        elseif ($this->InningFrame >= 17 && $this->bti == 1 && $this->teams[1]->score != $this->teams[0]->score)
            // extra innings, bottom of frame, any team ahead
            $this->GameOver();
        else
            // 1-8 innings, or any other extra inning
            $this->StartInning();
    }

    function DoPitch() {
        // assuming that an average count of an at-bat is 2-1 and then he hits it, so that's a .25 chance of him hitting it
        if ($this->GetRand() < 0.75) {
            // ball or strike (2/3 chance of a ball)
            if ($this->GetRand() < 0.667) {
                // ball
                $this->count->balls++;
                if ($this->count->balls == 4) {
                    // walk
                    $this->AdvanceRunners(0, -1);
                    $this->AdvanceLineup();
                    $this->DoAtBat();
                }
            } else {
                // strike
                $s = $this->GetRand();
                if (!($this->count->strikes == 2 && $s >= 0.667)) {
                    // only add strike if it's not Strike 2 now and it's not a foul ball
                    $this->count->strikes++;
                    if ($this->count->strikes == 3)
                        $this->DoOut(true);
                }
            }
        } else {
            // hit in play

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
                // determine which hit type (param is # of bases in hit)
                $r = $this->GetRand();
                if ($r < $CurrBtr->BatterHitsPct->HR)
                    $this->DoHit(4);
                elseif ($r >= $CurrBtr->BatterHitsPct->HR && $r < ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL) )
                    $this->DoHit(3);
                elseif ($r >= ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL) && $r < ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL + $CurrBtr->BatterHitsPct->DBL) )
                    $this->DoHit(2);
                else
                    $this->DoHit(1);
            } elseif (!$this->TryError())
                $this->DoOut(false);  // he's out
        }
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

    function SetRunnersStatus ($runners) {
        for ($i = 0; $i <= 2; $i++)
            $this->inning->runners[$i] = $runners[$i];
    }

    function TryDoublePlay ($pos) {
        $dbTurned = true; // this will be the result (will be the default value here, unless it's set to false)

        switch ($this->BasesStatus()) {
            case "!!!":
                $dbTurned = false;
                break;
            case "*!!":
                $this->inning->runners[0] = false;
                break;
            case "!*!":
            case "!!*":
                $dbTurned = false;
                break;
            case "**!":
            case "*!*":
                $this->SetRunnersStatus(array("!", "!", "*"));
                break;
            case "!**":
                $dbTurned = false;
                break;
            case "***":
                $this->SetRunnersStatus(array ("*", "*", "!"));
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
            $this->GameOver();
    }

    function AdvanceRunners($bases, $pos) {
        // bases: # of bases of hit
        // pos: defensive position where ball was hit (1 based)

        switch ($bases) {
            case -2: // error (assumed one base advance per runner, plus batter safe at first)
                switch ($this->BasesStatus()) {
                    case "!!!":
                        $this->SetRunnersStatus(array ("*", "!", "!"));
                    case "*!!":
                        $this->SetRunnersStatus(array ("*", "*", "!"));
                    case "!*!":
                        $this->SetRunnersStatus(array ("*", "!", "*"));
                    case "!!*":
                        $this->SetRunnersStatus(array ("*", "!", "!"));
                        $this->IncrementScore(1, false);
                    case "**!":
                        $this->SetRunnersStatus(array ("*", "*", "*"));
                    case "*!*":
                        $this->SetRunnersStatus(array ("*", "*", "!"));
                        $this->IncrementScore(1, false);
                    case "!**":
                        $this->SetRunnersStatus(array ("*", "!", "*"));
                        $this->IncrementScore(1, false);
                    case "***":
                        $this->IncrementScore(1, false);
                }
            case -1: // out (sac fly)
                if ($pos >= 7 && $this->inning->runners[2] && $this->inning->outs < 2) {
                    $this->inning->runners[2] = false;  // the other 2 baserunners stay the same
                    $this->IncrementScore(1, false);
                }
            case 0: // walk
                switch ($this->BasesStatus()) {
                    case "!!!":
                        $this->SetRunnersStatus(array ("*", "!", "!"));
                        break;
                    case "*!!":
                    case "!*!":
                        $this->SetRunnersStatus(array ("*", "*", "!"));
                        break;
                    case "!!*":
                        $this->SetRunnersStatus(array ("*", "!", "*"));
                        break;
                    case "**!":
                    case "*!*":
                    case "!**":
                        $this->SetRunnersStatus(array ("*", "*", "*"));
                        break;
                    case "***":
                        $this->IncrementScore(1, false);
                        break;
                }
            // from now on these are # of bases in the hit
            case 1:
                switch ($this->BasesStatus()) {
                    case "!!!":
                        $this->SetRunnersStatus(array ("*", "!", "!"));
                        break;
                    case "*!!":
                        if ($pos == 9)
                            // runner will advance from 1st to 3rd on a single to right
                            $this->SetRunnersStatus(array ("*", "!", "*"));
                        else
                            $this->SetRunnersStatus(array ("*", "*", "!"));
                        break;
                    case "!*!":
                        // runner will score from second
                    case "!!*":
                        $this->SetRunnersStatus(array ("*", "!", "!"));
                        $this->IncrementScore(1, false);
                        break;
                    case "**!":
                        if ($pos >= 8)
                            $this->SetRunnersStatus(array ("*", "!", "*"));
                        else
                            $this->SetRunnersStatus(array ("*", "*", "!"));

                        $this->IncrementScore(1, false);
                        break;
                    case "*!*":
                        if ($pos >= 8)
                            $this->SetRunnersStatus(array ("*", "!", "*"));
                        else
                            $this->SetRunnersStatus(array ("*", "*", "!"));

                        $this->IncrementScore(1, false);
                        break;
                    case "!**":
                        if ($pos >= 8) {
                            $this->SetRunnersStatus(array ("*", "!", "!"));
                            $this->IncrementScore(2, false);
                        } else {
                            $this->SetRunnersStatus(array ("*", "!", "*"));
                            $this->IncrementScore(1, false);
                        }
                        break;
                    case "***":
                        if ($pos >= 8) {
                            $this->SetRunnersStatus(array ("*", "!", "*"));
                            $this->IncrementScore(2, false);
                        } else {
                            $this->SetRunnersStatus(array ("*", "*", "*"));
                            $this->IncrementScore(1, false);
                        }
                        break;
                }
            case 2:
                switch ($this->BasesStatus()) {
                    case "!!!":
                        break;
                    case "*!!":
                    case "!*!":
                    case "!!*":
                        $this->IncrementScore(1, false);
                    case "**!":
                    case "*!*":
                    case "!**":
                        $this->IncrementScore(2, false);
                        break;
                    case "***":
                        $this->IncrementScore(3, false);
                        break;
                }
                $this->SetRunnersStatus(array ("!", "*", "!")); // will always clear the bases (besides for batter himself)
            case 3:
                switch ($this->BasesStatus()) {
                    case "!!!":
                        break;
                    case "*!!":
                    case "!*!":
                    case "!!*":
                        $this->IncrementScore(1, false);
                        break;
                    case "**!":
                    case "*!*":
                    case "!**":
                        $this->IncrementScore(2, false);
                        break;
                    case "***":
                        $this->IncrementScore(3, false);
                        break;
                }
                $this->SetRunnersStatus(array("!", "!", "*")); // will always clear the bases
            case 4:
                switch ($this->BasesStatus()) {
                    case "!!!":
                        $this->IncrementScore(1, true);
                        break;
                    case "*!!":
                    case "!*!":
                    case "!!*":
                        $this->IncrementScore(2, true);
                        break;
                    case "**!":
                    case "*!*":
                    case "!**":
                        $this->IncrementScore(3, true);
                        break;
                    case "***":
                        $this->IncrementScore(4, true);
                        break;
                }
                $this->SetRunnersStatus(array("!", "!", "!")); // will always clear the bases
        }
    }

    function BasesStatus() {
        $status = "";
        foreach ($this->inning->runners as $runner)
            $status += $runner;
        return $status;
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
