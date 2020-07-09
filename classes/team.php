<?php
    class Team {
        public $city = "";
        public $name = "";
        public $W = 0;
        public $L = 0;
        public $batters = array();
        public $pitchers = array();
        public $pitcher = 0;  // # in the pitchers list
        public $score = 0;
        public $AtBatNum = 0;
        public $Boxscore = "";
        public $CurPitcherInns = 0;  // this will help us determine when a pitching change is needed
    }
?>
