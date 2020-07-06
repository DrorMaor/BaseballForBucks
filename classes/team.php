<?php
    class Team {
        public $city;
        public $name;
        public $pct;    // winning percentage: W/(W+L)
        public $batters = array();
        public $pitchers = array();
        public $pitcher;  // # in the pitchers list
        public $score;
        public $AtBatNum;
        public $Boxscore;
        public $CurPitcherInns ;  // this will help us determine when a pitching change is needed
    }
?>
