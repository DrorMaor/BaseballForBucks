<?php
    class Team {
        public $city = "";
        public $name = "";
        public $pct = 0.000;    // winning percentage: W/(W+L)
        public $batters = array();
        public $pitchers = array();
        public $pitcher = 0;  // # in the pitchers list
        public $score = 0;
        public $AtBatNum = 0;
        public $Boxscore = "";
        public $CurPitcherInns = 0;  // this will help us determine when a pitching change is needed
    }
?>
