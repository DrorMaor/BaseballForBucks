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
            require_once("game.php");
        }

        function start() {
            $this->GetLineup();
        }

        function GetLineup() {
            $gameNum = 0;
            // get the schedule
            require("DBconn.php");

            $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $this->team or HomeTeam = $this->team) and year = $this->year;");
            $sql->execute();
            foreach ($sql as $row => $cols) {
                $GetGameLineup = new GetGameLineup($this->team, $this->year, $this->season, $cols["AwayTeam"], $cols["HomeTeam"], $gameNum);
                $GetGameLineup->start();
                for ($i = 0; $i < $cols["games"]; $i++) {
                    $this->PlayEachGame($GetGameLineup->teams, $gameNum);
                    $gameNum++;
                }
            }
            $conn = null;
        }

        function PlayEachGame($teams, $gameNum) {
            // simulate each game
            $game = new game($teams, $this->team, $this->year, $this->season, $cols["AwayTeam"], $cols["HomeTeam"], $gameNum);
            $game->start();
            if ($this->team == $cols["AwayTeam"]) {
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
        }
    }
?>
