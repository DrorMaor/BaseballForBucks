<?php

    class schedule {
        private $team;
        private $year;
        private $season;
        public $W = 0;
        public $L = 0;

        public function __construct($team, $year, $season) {
            $this->team = $team;
            $this->year = $year;
            $this->season = $season;
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
                    $this->PlayEachGame($GetGameLineup->teams, $cols["AwayTeam"], $cols["HomeTeam"]);
                    $GameNum++;
                }
            }
            $conn = null;
        }

        function PlayEachGame($teams, $AwayTeam, $HomeTeam) {
            // simulate each game
            
            
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
            
            /*
            $QuickGame = new QuickGame($teams, $team, $HomeTeam);
            $QuickGame->start();
            if ($QuickGame->outcome[1] > $QuickGame->outcome[0])
                $this->W++;
            else
                $this->L++;
             * 
             */
        }
    }
?>
