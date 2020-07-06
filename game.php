<?php

    class game {
        private $Teams = array();
        private $bti;  // Batting Team Index (too long to write full name each time)
        private $Inning;
        private $Count;

        // we allow up to 5 errors per game (both teams combined)
        // randomly, an error will occur, until this # is reached
        private $MaxErrors;

        private $ErrorCount;

        // this will increment by 1 with each top/bottom of inning, and will be a running total, but we can determine (for printing reasons) the actual inning # and top/bottom
        private $InningFrame = -1;

        public function __construct() {
            require_once ("classes/inning.php");
            require_once ("classes/count.php");
            require_once ("classes/team.php");
            require_once ("classes/batter.php");
            require_once ("classes/batterHitsPct.php");
            require_once ("classes/pitcher.php");
            //require_once ("classes/boxscore.php");

            $MaxErrors = floor($this->GetRand()*6);
        }

        function GetRand() {
            return rand (0, 1000) / 1000;
        }

        function main() {
            $AwayTeam = $_GET["AwayTeam"];
            $AwayYear = $_GET["AwayYear"];
            $HomeTeam = $_GET["HomeTeam"];
            $HomeYear = $_GET["HomeYear"];
            require ("DBconn.php");

            $team = new Team();
            $team->batters = $this->GetBatters($conn, $AwayTeam, $AwayYear);
            $team->pitchers = $this->GetPitchers($conn, $AwayTeam, $AwayYear);
            array_push($this->Teams, $team);
            $team->batters = $this->GetBatters($conn, $HomeTeam, $HomeYear);
            $team->pitchers = $this->GetPitchers($conn, $HomeTeam, $HomeYear);
            array_push($this->Teams, $team);

            $conn = null;
            $this->PlayBall();
        }

        function GetBatters($conn, $team, $year) {
            $Team = new Team();
            $sql = $conn->prepare("SELECT * from ActualBatters where team = $team and year = $year");
            $sql->execute();
            foreach($sql as $row => $cols) {
                $b = new batter();
                $b->name = $cols["name"];
                $b->AVG = $cols["AVG"];
                $bhp = new batterHitsPct();
                $bhp->DBL = $cols["B2"];
                $bhp->TPL = $cols["B3"];
                $bhp->HR = $cols["HR"];
                $b->batterHitsPct = $bhp;
                array_push($Team->batters, $b);
        	}
            return $Team->batters;
        }

        function GetPitchers($conn, $team, $year) {
            $Team = new Team();
            $sql = $conn->prepare("SELECT * from ActualPitchers where team = $team and year = $year");
            $sql->execute();
            foreach($sql as $row => $cols) {
                $p = new batter();
                $p->name = $cols["name"];
                $p->ERA = $cols["ERA"];
                $p->AvgInnPerGame = $cols["AvgInnPerGame"];
                $p->RS = $cols['type'];
                array_push($Team->pitchers, $p);
        	}
            return $Team->pitchers;
        }

        function PlayBall() {
            $this->Teams[0]->AtBatNum = 0;
            $this->Teams[1]->AtBatNum = 0;
            $this->StartInning();
        }

        function GameOver() {
            //DrawBoxscore();
        }

        /*
        function DrawBoxscore() {
            box = "\n"
            // this is the heading of the boxscore
            box += "    "
            inns = count(Teams[0]->Boxscore->inn)
            for i = 1; i <= inns; i++ {
                box += fmt.Sprintf("%d ", i)
            }
            box += " R H E \n"
            // now both teams' #s
            for _, team = range Teams {
                box += team.short + " "
                if count(team.Boxscore.inn) < inns {
                    team.Boxscore.inn = append(team.Boxscore.inn, -1)
                }
                for i = 0; i < inns; i++ {
                    if team.Boxscore.inn[i] == -1 {
                        box += "- "
                    } else {
                        box += fmt.Sprintf("%d ", team.Boxscore.inn[i])
                    }
                }
                box += fmt.Sprintf(" %d %d %d\n", team.score, team.Boxscore.H, team.Boxscore.E)
            }


            fmt.Println (box)

            f, _ = os.OpenFile("boxscore", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
            f.WriteString(box)
            f.Close()
        }
        */

        function StartInning() {
            // reset the inning numbers
            $Inning->outs = 0;
            $Inning->LeadOff = true;
            $this->SetRunnersStatus(array (false, false, false));
            $this->CheckPitchingChange();

            $InningFrame++;
            $InningNum = floor($InningFrame / 2) + 1;
            $bti = InningFrame % 2;
            array_push ($Teams[$bti]->Boxscore->inn, 0);
            while (true) {
                DoAtBat();
                if ($Inning->outs == 3) {
                    EndInning();
                    break;
                }
            }
        }

        function CheckPitchingChange() {
            if ($Teams[$bti]->pitchers[$Teams[$bti]->pitcher]->AvgInnPerGame == $Teams[$bti]->CurPitcherInns) {
                if (count($Teams[$bti]->pitchers) > $Teams[$bti]->pitcher + 1) {
                    $Teams[$bti]->pitcher++;
                    $Teams[$bti]->CurPitcherInns = 0;
                }
            }
        }

        function DoAtBat() {
            $Count->balls = 0;
            $Count->strikes = 0;
            while (true) {
                if ($Count->balls < 4 && $Count->strikes < 3)
                    DoPitch();
                elseif ($Inning->outs == 3)
                    EndInning();
            }
        }

        function EndInning() {
            // all this occurs at the end of an inning, BEFORE we increment the inning # and frame
            // (which we do at the beginning of StartInning)

            $Teams[$bti]->CurPitcherInns++;  // needed to determine if pitching change is due

            // determine whether to end the game, or start another inning
            if ($InningFrame == 16 && $Teams[1]->score > $Teams[0]->score)
                // bottom of the 9th, home team ahead
                GameOver();
            elseif ($InningFrame >= 17 && $bti == 1 && $Teams[1]->score != $Teams[0]->score)
                // extra innings, bottom of frame, any team ahead
                GameOver();
            else
                // 1-8 innings, or any other extra inning
                StartInning();
        }

        function DoPitch() {
            // assuming that an average count of an at-bat is 2-1 and then he hits it, so that's a .25 chance of him hitting it
            if (GetRand() < 0.75) {
                // ball or strike (2/3 chance of a ball)
                if (GetRand() < 0.667) {
                    // ball
                    $Count->balls++;
                    if ($Count->balls == 4) {
                        // walk
                        AdvanceRunners(0, -1);
                        AdvanceLineup();
                        DoAtBat();
                    }
                } else {
                    // strike
                    $s = GetRand();
                    if (!($Count->strikes == 2 && s >= 0.667)) {
                        // only add strike if it's not Strike 2 now and it's not a foul ball
                        $Count->strikes++;
                        if ($Count->strikes == 3)
                            DoOut(true);
                    }
                }
            } else {
                // hit in play

                // determine if it's a hit or out
                $CurrBtr = $Teams[$bti]->batters[$Teams[$bti]->AtBatNum];
                // ERA3 is ERA adjusted to 3.0
                // (3.00 is a decent ERA, and anothing higher would make the batter stronger,
                //  and anything lower would make the batter weaker)
                $ERA3 = $Teams[$bti]->pitchers[$Teams[$bti]->pitcher]->ERA - 3.33;
                // GBOP = Getting Batter Out Percentage, we adjust the ERA3 based on the AVG
                // so we have a fair chance at a hit/out, based on both pitcher & batter
                $GBOP = $CurrBtr->AVG + ($ERA3 / 50);
                if (GetRand() < GBOP) {
                    // he's on base
                    // determine which hit type (param is # of bases in hit)
                    $r = GetRand();
                    if ($r < $CurrBtr->BatterHitsPct->HR)
                        DoHit(4);
                    elseif ($r >= $CurrBtr->BatterHitsPct->HR && $r < ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL) )
                        DoHit(3);
                    elseif ($r >= ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL) && r < ($CurrBtr->BatterHitsPct->HR + $CurrBtr->BatterHitsPct->TPL + $CurrBtr->BatterHitsPct->DBL) )
                        DoHit(2);
                    else
                        DoHit(1);
                } elseif (!TryError())
                    DoOut(false);  // he's out
            }
        }

        function TryError() {
            $error = false;
            if ($ErrorCount < $MaxErrors) {
                // try throwing an error
                // (this is based on 80 atbats per game: 27 min per team, plus average 3 walks and 10 hits)
                if (GetRand() < ($MaxErrors / 80)) {
                    $error = true;
                    // it's the NON batting team that gets charged with the error
                    if ($bti == 0)
                        $Teams[1]->Boxscore->E++;
                    else
                        $Teams[0]->Boxscore->E++;

                    $ErrorCount++;
                    AdvanceRunners(-2, -1);
                    AdvanceLineup();
                    DoAtBat();
                }
            }
            return error;
        }

        function DoHit($bases) {
            // most base hits are out of the infield, so we assume them here
            $outfield = floor(GetRand()*3) + 7;  // left, center, or right field (nfk"m for runner scoring from second)
            $this->AdvanceRunners($bases, $outfield);
            $this->AdvanceLineup();
            //$Teams[$bti]->Boxscore.H++;
            DoAtBat();
        }

        function AdvanceLineup() {
            $Teams[$bti]->AtBatNum++;
            if ($Teams[$bti]->AtBatNum % 9 == 0)
                $Teams[$bti]->AtBatNum = 0;
        }

        function SetRunnersStatus (array $runners) {
            for ($i = 0; $i <= 2; $i++)
                $Inning->runners[i] = $runners[i];
        }

        function TryDoublePlay ($pos) {
            $dbTurned = true; // this will be the result (will be the default value here, unless it's set to false)

            // who's the middle man for the double play
            $players = array ($pos, 4, 3);
            $players[1] = 4;
            if ($pos == 5)
                $players[1] = 6;

            switch (BasesStatus()) {
                case "000":
                    $dbTurned = false;
                    break;
                case "100":
                    $Inning->runners[0] = false;
                    break;
                case "010":
                case "001":
                    $dbTurned = false;
                    break;
                case "110":
                case "101":
                    SetRunnersStatus(array (false, false, true));
                    break;
                case "011":
                    $dbTurned = false;
                    break;
                case "111":
                    SetRunnersStatus(array (true, true, false));
                    $players = array (pos, 2, 5);
                    break;
            }
        }

        function IncrementScore($runs, $HR) {
            $walkoff = false;
            if ($InningFrame >= 17 && $bti == 1 && $Teams[1]->score + runs > $Teams[0]->score) {
                // in the bottom of the 9+ inning, test for a walkoff, and if so, only count the # of runs needed to win
                // (unless it's a HR, then all runs count)
                $walkoff = true;
                if (!HR) {
                    $runs = $Teams[0]->score - $Teams[1]->score + 1;
                }
            }
            $Teams[$bti]->score += $runs;
            //$Teams[$bti]->Boxscore.inn[count($Teams[$bti]->Boxscore.inn)-1] += runs
            if ($walkoff) {
                GameOver();
            }
        }

        function AdvanceRunners($bases, $pos) {
            // bases: # of bases of hit
            // pos: defensive position where ball was hit (1 based)

            switch ($bases) {
                case -2: // error (assumed one base advance per runner, plus batter safe at first)
                    switch (BasesStatus()) {
                        case "000":
                            SetRunnersStatus(array (true, false, false));
                        case "100":
                            SetRunnersStatus(array (true, true, false));
                        case "010":
                            SetRunnersStatus(array (true, false, true));
                        case "001":
                            SetRunnersStatus(array (true, false, false));
                            IncrementScore(1, false);
                        case "110":
                            SetRunnersStatus(array (true, true, true));
                        case "101":
                            SetRunnersStatus(array (true, true, false));
                            IncrementScore(1, false);
                        case "011":
                            SetRunnersStatus(array (true, false, true));
                            IncrementScore(1, false);
                        case "111":
                            IncrementScore(1, false);
                    }
                case -1: // out (sac fly)
                    if ($pos >= 7 && $Inning->runners[2] && $Inning->outs < 2) {
                        $Inning->runners[2] = false;  // the other 2 baserunners stay the same
                        IncrementScore(1, false);
                    }
                case 0: // walk
                    switch (BasesStatus()) {
                        case "000":
                            SetRunnersStatus(array (true, false, false));
                            break;
                        case "100":
                        case "010":
                            SetRunnersStatus(array (true, true, false));
                            break;
                        case "001":
                            SetRunnersStatus(array (true, false, true));
                            break;
                        case "110":
                        case "101":
                        case "011":
                            SetRunnersStatus(array (true, true, true));
                            break;
                        case "111":
                            IncrementScore(1, false);
                            break;
                    }
                // from now on these are # of bases in the hit
                case 1:
                    switch (BasesStatus()) {
                        case "000":
                            SetRunnersStatus(array (true, false, false));
                            break;
                        case "100":
                            if (pos == 9 )
                                // runner will advance from 1st to 3rd on a single to right
                                SetRunnersStatus(array (true, false, true));
                            else
                                SetRunnersStatus(array (true, true, false));
                            break;
                        case "010":
                            // runner will score from second
                        case "001":
                            SetRunnersStatus(array (true, false, false));
                            IncrementScore(1, false);
                            break;
                        case "110":
                            if (pos >= 8)
                                SetRunnersStatus(array (true, false, true));
                            else
                                SetRunnersStatus(array (true, true, false));

                            IncrementScore(1, false);
                            break;
                        case "101":
                            if (pos >= 8)
                                SetRunnersStatus(array (true, false, true));
                            else
                                SetRunnersStatus(array (true, true, false));

                            IncrementScore(1, false);
                            break;
                        case "011":
                            if (pos >= 8) {
                                SetRunnersStatus(array (true, false, false));
                                IncrementScore(2, false);
                            } else {
                                SetRunnersStatus(array (true, false, true));
                                IncrementScore(1, false);
                            }
                            break;
                        case "111":
                            if (pos >= 8) {
                                SetRunnersStatus(array (true, false, true));
                                IncrementScore(2, false);
                            } else {
                                SetRunnersStatus(array (true, true, true));
                                IncrementScore(1, false);
                            }
                            break;
                    }
                case 2:
                    switch (BasesStatus()) {
                        case "000":
                            break;
                        case "100":
                        case "010":
                        case "001":
                            IncrementScore(1, false);
                        case "110":
                        case "101":
                        case "011":
                            IncrementScore(2, false);
                            break;
                        case "111":
                            IncrementScore(3, false);
                            break;
                    }
                    SetRunnersStatus(array (false, true, false));  // will always clear the bases (besides for batter himself)
                case 3:
                    switch (BasesStatus()) {
                        case "000":
                            break;
                        case "100":
                        case "010":
                        case "001":
                            IncrementScore(1, false);
                            break;
                        case "110":
                        case "101":
                        case "011":
                            IncrementScore(2, false);
                            break;
                        case "111":
                            IncrementScore(3, false);
                            break;
                    }
                    SetRunnersStatus(array (false, false, true)); // will always clear the bases
                case 4:
                    switch (BasesStatus()) {
                        case "000":
                            IncrementScore(1, true);
                            break;
                        case "100":
                        case "010":
                        case "001":
                            IncrementScore(2, true);
                            break;
                        case "110":
                        case "101":
                        case "011":
                            IncrementScore(3, true);
                            break;
                        case "111":
                            IncrementScore(4, true);
                            break;
                    }
                    SetRunnersStatus(array (false, false, false)); // will always clear the bases
            }
        }

        function BasesStatus() {
            $retVal = "";
            for ($i = 0; $i <= 2; $i++) {
                if ($$Inning->runners[i])
                    $retVal += "1";
                else
                    $retVal += "0";
            }
            return $retVal;
        }

        function DoOut($strikeout) {
            if (!$strikeout) {
                $r = GetRand();
                $pos = 0;
                // much less likelihood that the pitcher or catcher will do the putout, so we give them a smaller probabililty
                if ($r < 0.0625)
                    $pos = 1;
                elseif ($r >= 0.0625 && r < 0.125)
                    $pos = 2;
                elseif ($r >= 0.125 && r < 0.25)
                    $pos = 3;
                elseif ($r >= 0.25 && r < 0.375)
                    $pos = 4;
                elseif ($r >= 0.375 && r < 0.5)
                    $pos = 5;
                elseif ($r >= 0.5 && r < 0.625)
                    $pos = 6;
                elseif ($r >= 0.625 && r < 0.75)
                    $pos = 7;
                elseif ($r >= 0.75 && r < 0.875)
                    $pos = 8;
                else
                    $pos = 9;

                if ($pos >= 7)
                    AdvanceRunners(-1, $pos);
                else {
                    if ($Inning->outs < 2) {
                        if ($pos != 2) {  // rare to have a catcher start a double play
                            if (count($dbText) > 0) {
                                // dbText returns true only means that it's possible for a DP,
                                // but still there's a 10% chance it won't be turned
                                if (GetRand() < 0.9) {
                                    $Inning->outs++ ; // this will only be the EXTRA out
                                }
                            }
                        }
                    }
                }
            }

            $Count->strikes = 0;
            $Count->balls = 0;
            $Inning->outs++;  // always increment the regular out

            AdvanceLineup();
            if ($Inning->outs == 3)
                EndInning();
            else
                DoAtBat();
        }
    }
?>
