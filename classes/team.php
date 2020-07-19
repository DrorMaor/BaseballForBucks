<?php
    class Team {
        public $city = "";
        public $name = "";
        public $W = 0;
        public $L = 0;
        public $batters = array();
        public $pitchers = array();
        public $score = 0;
        public $AtBatNum = 0;
        public $CurPitcherInns = 0;  // this will help us determine when a pitching change is needed
        public $pitcher = 0;  // # in the pitchers list
    }
?>
