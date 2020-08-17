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
            shuffle($this->highlights);
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
                    $this->AddWinningStreak();
                else
                    $this->EndWinningStreak();
            else
                if ($game->teams[1]->score > $game->teams[0]->score)
                    $this->AddWinningStreak();
                else
                    $this->EndWinningStreak();
            array_push($this->highlights, $game->highlights);
        }

        function AddWinningStreak() {
            $this->W++;
            $this->WinningStreak++;
        }

        function EndWinningStreak() {
            $this->L++;
            if ($this->WinningStreak >= 10) {
                $WS_arr = array($this->WinningStreak . " game winning streak");
                array_push($this->highlights, $WS_arr);
            }
            $this->WinningStreak = 0;
        }
    }
?>
