<?php

class QuickGame {
    private $teams = array();   // this will be both teams' lineups
    // to know if it's the home team (for HAWL stat below)
    private $team;  
    private $HomeTeam;
    public $outcome;
    
    public function __construct($teams, $team, $HomeTeam) {
        $this->teams = $teams;
        $this->team = $team;
        $this->HomeTeam = $HomeTeam;
        $this->outcome = array(0, 0);
    }

    function GetRand() {
        return rand (0, 999) / 1000;
    }
    
    function start() {
        $bti = 0;
        $pti = 0;
        for ($t = 0; $t < 2; $t++) 
        {
            if ($t == 0)  {
                $bti = 0;
                $pti = 1;
            }
            else {
                $bti = 1;
                $pti = 0;
            }
            $BattingTeam = $this->teams[$bti];
            $PitchingTeam = $this->teams[$pti];
            $PitcherIndex = floor( $this->GetRand() * 9);
            $CurrPitcher = $PitchingTeam->pitchers[$PitcherIndex];
            
            // ERA3 is ERA adjusted to 3.0
            // (3.00 is a decent ERA, and anothing higher would make the batter stronger,
            // and anything lower would make the batter weaker)
            $ERA3 = $CurrPitcher->ERA - 3.33;
            
            foreach ($BattingTeam->batters as $batter)
            {
                // GBOP = Getting Batter Out Percentage, we adjust the ERA3 based on the AVG
                // so we have a fair chance at a hit/out, based on both pitcher & batter
                $GBOP = $batter->AVG + ($ERA3 / 50);
                
                // Home/Away W/L %age
                // (we adjust the hitters chance of getting on base, based on the team's general home/away winning percentage)
                $HAWL = 0.000;
                if ($this->team == $this->HomeTeam)
                    $HAWL = $BattingTeam->HomeW / ($BattingTeam->HomeW + $BattingTeam->HomeL);
                else
                    $HAWL = $BattingTeam->AwayW / ($BattingTeam->AwayW + $BattingTeam->AwayL);
                $GBOP += $HAWL - .500; 

                for ($ab = 0; $ab < 4; $ab++) 
                {
                    if ( $this->GetRand() < $GBOP) 
                        $this->outcome[$bti] ++;
                    else
                        $this->outcome[$pti] ++;
                }
            }      
        }
    }
}
?>