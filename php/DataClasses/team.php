<?php
    class Team {
        public $city = "";
        public $name = "";
        public $W = 0;
        public $L = 0;

        // the team's W/L record at Home and Away
        // (their offensive strength depends on that somewhat)
        public $HomeW = 0;
        public $HomeL = 0;
        public $AwayW = 0;
        public $AwayL = 0;
        //////////////////////////////////////////////////////

        public $batters = array();
        public $score = 0;
        public $batter = 0;
    }
?>
