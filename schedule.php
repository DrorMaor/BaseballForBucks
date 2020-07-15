<?php
    class schedule {
        private $season;
        public $team;
        public $year;
        public $W = 0;
        public $L = 0;
        private $gameNum = 0;

        public function __construct($team, $year, $season) {
            $this->team = $team;
            $this->year = $year;
            $this->season = $season;
        }

        function start() {
            // get the schedule
            require_once("game.php");
            require("DBconn.php");
            $sql = $conn->prepare("select * from ActualSchedules where (AwayTeam = $this->team or HomeTeam = $this->team) and year = $this->year;") ;
            $sql->execute();
            foreach ($sql as $row => $cols) {
                for ($i = 0; $i < $cols["games"]; $i++) {
                    // simulate each game
                    $game = new game($this->team, $this->year, $this->season, $cols["AwayTeam"], $cols["HomeTeam"], $this->gameNum);
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
                    $this->gameNum++;
                }
            }
            $conn = null;
        }
    }
?>
