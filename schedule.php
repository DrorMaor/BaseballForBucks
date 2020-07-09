<?php
    class schedule {
        private $team;
        private $year;
        public $W = 0;
        public $L = 0;

        public function __construct($team, $year) {
            $this->team = $team;
            $this->year = $year;
        }

        function GetScheduleAndPlayGames() {
            require_once("game.php");
            require_once("DBconn.php");
            $sql = $conn->prepare("select * from ActualSchedule where (AwayTeam = $this->team or HomeTeam = $this->team) and year = $this->year limit 2;") ;
            $sql->execute();
            foreach($sql as $row => $cols) {
                for ($i = 0; $i < $cols["games"]; $i++) {
                    $game = new game($this->year, $cols["AwayTeam"], $cols["HomeTeam"]);
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
            $conn = null;
        }
    }
?>
