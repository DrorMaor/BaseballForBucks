<?php

    class schedule {
        private $team;
        private $year;
        private $season;
        public $W = 0;
        public $L = 0;
        public $WinningStreak = 0;
        public $highlights = [];

        public function __construct($team, $year, $season) {
            $this->team = $team;
            $this->year = $year;
            $this->season = $season;

            require_once("GetGameLineup.php");
            require_once("game.php");
        }

        function start() {
            $this->GetLineupAndPlayGames();

            // record season W/L
            require("DBconn.php");
            $update = "update SeasonsPlayed set W = " . $this->W . ", L = " . $this->L . " where id = " . $this->season ;
            $sql = $conn->prepare($update);
            $sql->execute();
            $conn = null;

            return;
        }

        function GetLineupAndPlayGames() {
            // get the schedule
            require("DBconn.php");
            $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $this->team or HomeTeam = $this->team) and year = $this->year");
            $sql->execute();
            foreach ($sql as $row => $cols) {
                $GetGameLineup = new GetGameLineup($this->team, $this->year, $this->season, $cols["AwayTeam"], $cols["HomeTeam"]);
                $GetGameLineup->start();
                for ($i = 0; $i < $cols["games"]; $i++)
                    // simulate each game
                    $this->PlayEachGame($GetGameLineup->teams, $cols["AwayTeam"], $cols["HomeTeam"]);
            }
            $conn = null;
        }

        function PlayEachGame($teams, $AwayTeam, $HomeTeam) {
            $game = new game($teams, $this->team, $this->year, $AwayTeam, $HomeTeam);
            $game->start();
            if ($this->team == $AwayTeam)
                if ($game->teams[0]->score > $game->teams[1]->score)
                    $this->W++;
                else
                    $this->L++;
            else
                if ($game->teams[1]->score > $game->teams[0]->score)
                    $this->W++;
                else
                    $this->L++;
        }
    }
?>
