<?php
    class GameScript {

        function GameScript($id, $text) {
            $script = "";
            switch $id {
                case 2:
                    // end of inning
                    $script = "End of " + InningScript()
                    $script += ". " + ScoreScript()
                    $script += "\n" + strings.Repeat("-", RepeatChars) + "\n" + strings.Repeat("-", RepeatChars) + "\n\n"
                case 3:
                    // each at bat
                    script = strings.Repeat("-", RepeatChars) + "\n"
                    if !Inning.LeadOff {
                        script += "Batting"
                    } else {
                        script += "Leading off in the " + InningScript()
                    }
                    script += fmt.Sprintf(" for %s, %s %s %s", Teams[bti].short, Teams[bti].batters[Teams[bti].AtBatNum].pos, Teams[bti].batters[Teams[bti].AtBatNum].FirstName, Teams[bti].batters[Teams[bti].AtBatNum].LastName)

                    Inning.LeadOff = false
                case 6:
                    // after runners advancing
                    switch BasesStatus() {
                        case "000":
                            //script = "Bases empty"
                        case "100":
                            script = "A runner at first base"
                        case "010":
                            script = "A runner at second base"
                        case "001":
                            script = "A runner at third base"
                        case "110":
                            script = "Runners at first and second"
                        case "101":
                            if GetRand() < 0.5 {
                                script = "Runners at first and third"
                            } else {
                                script = "Runners at the corners"
                            }
                        case "011":
                            script = "Runners at second and third"
                        case "111":
                            script = "Bases loaded"
                    }
                case 7:
                    // game over
                    script = "Game over. Final score: " + ScoreScript()
                case 8:
                    // hit
                    script = text
                case 12:
                    // ball
                    b := " Ball "
                    r := GetRand()
                    if r < 0.125 {
                        b += "high and inside"
                    } else if r >= 0.125 && r < 0.25 {
                        b += "high"
                    } else if r >= 0.25 && r < 0.375 {
                        b += "high and outside"
                    } else if r >= 0.375 && r < 0.5 {
                        b += "inside"
                    } else if r >= 0.5 && r < 0.625 {
                        b += "outside"
                    } else if r >= 0.625 && r < 0.75 {
                        b += "low and inside"
                    } else if r >= 0.75 && r < 0.825 {
                        b += "low"
                    } else {
                        b += "low and outside"
                    }
                    script = b + ". " + CountScript()
                case 13:
                    // strike
                    script = " " + text + ". " + CountScript()
                case 14:
                    // out
                    script = text
                case 15:
                    // out #
                    script = fmt.Sprintf("%d out", Inning.outs)
                    if Inning.outs == 2 {
                        script += "s"
                    }
                case 16:
                    // double play (whether successful or failed attempt)
                    script = text
                case 17:
                    // run(s) score(s)
                    script = text + ". " + ScoreScript()
                case 18:
                    // error (the text is in no way a reflection on which player caused the error, it's just a random position)
                    tPos := Teams[0].batters[int(math.Floor(GetRand()*9))].pos
                    if tPos == "DH" {
                        tPos = "P"
                    }
                    script = "Error on " + tPos
            }
            PlayNum ++
            FullGameScript += script + "\n"   // strconv.Itoa(PlayNum) + ") " +
        }

        function ScoreScript() string {
            return fmt.Sprintf("%s %d, %s %d", Teams[0].short, Teams[0].score, Teams[1].short, Teams[1].score)
        }

        function CountScript() string {
            if Count.balls == 4 {
                return "Walk"
            } else if Count.strikes == 3 {
                return "Strikeout"
            } else {
                return fmt.Sprintf("Count %d-%d", Count.balls, Count.strikes)
            }
        }

        function RandomField () string {
            var field string = ""
            r := GetRand()
            if r < 0.2 {
                field = "left field"
            } else if r >= 0.2 && r < 0.4 {
                field = "left center"
            } else if r >= 0.4 && r < 0.6 {
                field = "center field"
            } else if r >= 0.6 && r < 0.8 {
                field = "right center"
            } else {
                field = "right field"
            }
            return field
        }

        function InningScript () string {
            var is string = ""
            if bti == 0 {
                is += "top"
            } else {
                is += "bottom"
            }
            is += fmt.Sprintf(" of the %d", InningNum)
            switch InningFrame {
                case 0:
                    fallthrough
                case 1:
                    is += "st"
                case 2:
                    fallthrough
                case 3:
                    is += "nd"
                case 4:
                    fallthrough
                case 5:
                    is += "rd"
                default:
                    is += "th"
            }
            return is
        }
    }
?>