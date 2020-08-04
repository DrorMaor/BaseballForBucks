<?php

class QuickGame {
    private $teams = array();   // this will be both teams' lineups
    // to know if it's the home team (for HAWL stat below)
    private $team;  
    private $HomeTeam;
    private $GameNum;
    public $outcome;
    
    public function __construct($teams, $team, $HomeTeam, $GameNum) {
        $this->teams = $teams;
        $this->team = $team;
        $this->HomeTeam = $HomeTeam;
        $this->GameNum = $GameNum;
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
            $bti = $t;
            $pti = abs($t - 1);
            $BattingTeam = $this->teams[$bti];
            $PitchingTeam = $this->teams[$pti];
            $PitcherIndex = $this->GameNum % count($PitchingTeam->pitchers);
            $CurrPitcher = $PitchingTeam->pitchers[$PitcherIndex];
            
            // ERA3 is ERA adjusted to 3.0
            // (3.00 is a decent ERA, and anothing higher would make the batter stronger,
            // and anything lower would make the batter weaker)
            $ERA3 = $CurrPitcher->ERA - 3.33;
            
            $index = 0;
            foreach ($BattingTeam->batters as $batter)
            {
                $index ++;
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
                
                // lineup adjustment
                $GBOP -= ($index - 9) * ($batter->AVG - 0.300);
                // improve offensive chance based on extra base hits
                $GBOP += ($batter->B2 + $batter->B3 + $batter->HR) / 1000;
                
                for ($ab = 0; $ab < 4; $ab++) 
                {
                    if ($this->GetRand() < $GBOP) 
                        $this->outcome[$bti] ++;
                    else
                        $this->outcome[$pti] ++;
                }
            }      
        }
    }
}
?>