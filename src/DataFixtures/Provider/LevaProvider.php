<?php

namespace App\DataFixtures\Provider;

use App\Entity\Screenshots;

class LevaProvider
{
  
    // Tableau des plateformes pour les Fixtures
    private $platformList = [
        'Twitter',
        'Facebook',
        'Vinted',
        'Instagram',
        'TikTok',
        'Twitch',
        'Youtube',
        'slack'
    ];

    // Tableau des status pour les fixtures
    private $status = [
        'AWAITING',
        'ONGOING',
        'PROCESSED',
    ];

    // Tableau des screenshots pour les fixtures
    


    /**
     * Retourne une platform au hasard
     */
    public function platform()
    {
        return $this->platformList[array_rand($this->platformList)];
    }


    /**
     * Get the value of platformList
     */ 
    public function getPlatformList()
    {
        return $this->platformList;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }
}