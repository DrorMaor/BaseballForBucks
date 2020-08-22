<?php

class game
{
    public $teams = array();
    // we store text $highlights to display after the season
    public $highlights = [];

    private $bti;  // Batting Team Index (too long to write full name each time)
    private $inning;

    // we allow up to 5 errors per game (both teams combined)
    // randomly, an error will occur, until this # is reached
    private $MaxErrors;
    private $ErrorCount;
    private $InningFrame;

    private $team;  // to know if it's the home or away team
    private $year;
    private $AwayTeam;
    private $HomeTeam;

    private $ThisTeamIndex = 0;
    private $ThatTeamIndex = 0;

    private $homeruns = 0;  // used with highlights, if >=3 a game

    public function __construct($teams, $team, $year, $AwayTeam, $HomeTeam) {
        $this->teams = $teams;   // this will be both teams' lineups
        $this->team = $team;
        $this->year = $year;
        $this->AwayTeam = $AwayTeam;
        $this->HomeTeam = $HomeTeam;

        require_once ("DataClasses/inning.php");
        $this->inning = new inning();
        $this->bti = 0;
        $this->MaxErrors = floor($this->GetRand()*6);
        $this->ErrorCount = 0;
        $this->InningFrame = -0.5;
        $this->homeruns = 0;

        // reset incrementing values
        for ($i = 0; $i < 2; $i++) {
            $this->teams[$i]->score = 0;
            $this->teams[$i]->batter = 0;
        }
    }

    public function __destruct() {
        $this->teams = null;
        $this->team = -1;
        $this->year = -1;
        $this->AwayTeam = -1;
        $this->HomeTeam = -1;
        $this->bti = -1;
        $this->inning = null;
        $this->MaxErrors = -1;
        $this->ErrorCount = -1;
        $this->InningFrame = -1;
        $this->homeruns = -1;
        $this->ThisTeamIndex = -1;
        $this->ThatTeamIndex = -1;
    }

    function GetRand() {
        return rand (0, 999) / 1000;
    }

    function ThatTeamName() {
        // used with highlights
        $vs = ($this->GetRand() < 0.5) ? "vs" : "against";
        $text = " " . $vs . " the " . $this->teams[$this->ThatTeamIndex]->city . " " . $this->teams[$this->ThatTeamIndex]->name;
        return $text;
    }

    function start() {
        // used with highlights
        $this->ThisTeamIndex = ($this->team == $this->AwayTeam) ? 0 : 1;
        $this->ThatTeamIndex = ($this->team == $this->AwayTeam) ? 1 : 0;
        $this->StartInning();
    }

    function HomeAwayGame() {
        $text = "a home";
        if ($this->ThisTeamIndex == 0)
            $text = ($this->GetRand() < 0.5) ? "an away" : "a road";
        return $text . " game ";
    }

    function GameOver () {
        /*
        if ($this->teams[$this->ThisTeamIndex]->score > 10)
        {
            $hl = $this->teams[$this->ThisTeamIndex]->score . " runs in " . $this->HomeAwayGame() . $this->ThatTeamName();
            array_push($this->highlights, $hl);
        }
        if ($this->homeruns > 4) // homeruns only gets incremented if this team is the batting team
        {
            $hl = $this->homeruns . " homeruns in " . $this->HomeAwayGame() . $this->ThatTeamName();
            array_push($this->highlights, $hl);
        }
        foreach ($this->teams[$this->ThisTeamIndex]->batters as $batter)
        {
            if ($batter->Sim_HR > 4)
            {
                $hl = $batter->name . " hits " . $batter->Sim_HR . " homeruns in " . $this->HomeAwayGame() . $this->ThatTeamName();
                array_push($this->highlights, $hl);
            }
        }

        shuffle($this->highlights);
        */
        return;
    }

    function StartInning() {
        $this->InningFrame += 0.5;
        // which team is batting
        $this->bti = (floor($this->InningFrame) == $this->InningFrame) ? 0 : 1;

        $this->inning->runners = "000";
        $this->inning->outs = 0;
        $this->inning->runs = 0;

        while (true) {
            $this->DoAtBat();
            if ($this->inning->outs == 3) {
                $this->EndInning();
                break;
            }
        }
    }

    function CurrentBatter () {
        $BattingTeam = $this->teams[$this->bti];
        return $BattingTeam->batters[$BattingTeam->batter];
    }

    function DoAtBat() {
        $BattingTeam = $this->teams[$this->bti];
        $CurrBtr = $this->CurrentBatter();

        // Home/Away W/L %age
        // (we adjust the hitters chance of getting on base, based on the team's general home/away winning percentage)
        $HAWL = 0.000;
        if ($this->team == $this->HomeTeam)
            $HAWL = $BattingTeam->HomeW / ($BattingTeam->HomeW + $BattingTeam->HomeL);
        else
            $HAWL = $BattingTeam->AwayW / ($BattingTeam->AwayW + $BattingTeam->AwayL);
        $AdjustedFactor = $CurrBtr->AVG + (($HAWL - .500) / 10);

        if ($this->GetRand() < $AdjustedFactor)
        {
            // he's on base

            // calculate type of hit %age
            $B2 = $CurrBtr->B2 / $CurrBtr->H;
            $B3 = $CurrBtr->B3 / $CurrBtr->H;
            $HR = $CurrBtr->HR / $CurrBtr->H;

            // determine which hit type (DoHit param is # of bases in hit)
            $r = $this->GetRand();
            if ($r < $HR) {
                $this->DoHit(4);
                if ($this->bti == $this->ThisTeamIndex) {
                    $this->homeruns ++;
                    $BattingTeam->batters[$BattingTeam->batter]->Sim_HR++;
                }
            }
            elseif ($r >= $HR && $r < ($HR + $B3) )
                $this->DoHit(3);
            elseif ($r >= ($HR + $B3) && $r < ($HR + $B3 + $B2) )
                $this->DoHit(2);
            else
                $this->DoHit(1);
        } elseif (!$this->TryError())
            $this->DoOut();
    }

    function EndInning() {
        // all this occurs at the end of an inning, BEFORE we increment the inning # and frame
        // (which we do at the beginning of StartInning)

        // determine whether to end the game, or start another inning
        if ($this->InningFrame == 8.5 && $this->teams[1]->score > $this->teams[0]->score)
           // bottom of the 9th, home team ahead
           $this->GameOver();
        elseif ($this->InningFrame >= 9.0 && $this->bti == 1 && $this->teams[1]->score != $this->teams[0]->score)
           // extra innings, bottom of frame, any team ahead
           $this->GameOver();
        else
        {
            /*
            if ($this->inning->runs >= 6 && $this->bti == $this->ThisTeamIndex)
            {
                $hl = $this->inning->runs . " runs in one inning " . $this->ThatTeamName();
                array_push($this->highlights, $hl);
            }
            */
            // 1-8 innings, or any other extra inning
            $this->StartInning();
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
        $this->teams[$this->bti]->batter++;
        if ($this->teams[$this->bti]->batter % 9 == 0)
            $this->teams[$this->bti]->batter = 0;
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
        if ($this->InningFrame >= 8.5 && $this->bti == 1 && ($this->teams[1]->score + $runs) > $this->teams[0]->score) {
            // in the bottom of the 9+ inning, test for a walkoff, and if so, only count the # of runs needed to win
            // (unless it's a HR, then all runs count)
            $walkoff = true;
            if (!$HR)
                $runs = $this->teams[0]->score - $this->teams[1]->score + 1;
        }
        $this->teams[$this->bti]->score += $runs;
        $this->inning->runs += $runs;
        if ($walkoff)
            $this->GameOver();
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
                        /*
                        if ($this->bti == $this->ThisTeamIndex)
                        {
                            $CurrBtr = $this->CurrentBatter();
                            $hl = $CurrBtr->name . " hits a bases clearing triple " . $this->ThatTeamName();
                            array_push($this->highlights, $hl);
                        }
                        */
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
                        /*
                        if ($this->bti == $this->ThisTeamIndex)
                        {
                            $CurrBtr = $this->CurrentBatter();
                            $hl = $CurrBtr->name . " hits a Grand Slam " . $this->ThatTeamName();
                            array_push($this->highlights, $hl);
                        }
                        */
                        break;
                }
                $this->inning->runners = "000"; // will always clear the bases
                break;
        }
    }


    function DoOut() {
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
        else {
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
    }
}
?>
