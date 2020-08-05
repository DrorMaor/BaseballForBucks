<?php

    class schedule {
        private $team;
        public $year;
        private $season;
        public $W = 0;
        public $L = 0;
        public $TeamName;  // for response back to JS msg

        public function __construct($team, $year, $season, $TeamName) {
            $this->team = $team;
            $this->year = $year;
            $this->season = $season;
            $this->$TeamName = $TeamName;

            require_once("GetGameLineup.php");
            require_once("QuickGame.php");
        }

        function start() {
            $this->GetLineup();
        }

        function GetLineup() {
            $GameNum = 0;
            // get the schedule
            require("DBconn.php");
            $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $this->team or HomeTeam = $this->team) and year = $this->year;");
            $sql->execute();
            foreach ($sql as $row => $cols) {
                $GetGameLineup = new GetGameLineup($this->team, $this->year, $this->season, $cols["AwayTeam"], $cols["HomeTeam"], $GameNum);
                $GetGameLineup->start();
                for ($i = 0; $i < $cols["games"]; $i++) {
                    // simulate each game
                    $this->PlayEachGame($GetGameLineup->teams, $cols["AwayTeam"], $cols["HomeTeam"], $GameNum);
                    $GameNum++;
                }
            }
            $conn = null;
        }

        function PlayEachGame($teams, $AwayTeam, $HomeTeam, $GameNum) {
           // print_r($teams);
            //echo "<br>----------<br>";
            /*
            $game = new game($teams, $this->team, $this->year, $this->season, $AwayTeam, $HomeTeam);
            $game->start();
            if ($this->team == $AwayTeam) {
                if ($game->teams[0]->score > $game->teams[1]->score)
                    $this->W++;
                else
                    $this->L++;
            }
            else {
                if ($game->teams[1]->score > $game->teams[0]->score)
                    $this->W++;
                else
                    $this->L++;
            }
            */
            $QuickGame = new QuickGame($teams, $this->team, $HomeTeam, $GameNum);
            $QuickGame->start();
            if ($QuickGame->outcome[0] > $QuickGame->outcome[1])
                $this->W++;
            else
                $this->L++;
        }
    }
?>
