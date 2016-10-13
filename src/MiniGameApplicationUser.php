<?php

namespace MiniGameMessageApp;

use MessageApp\User\ApplicationUser;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

interface MiniGameApplicationUser extends ApplicationUser
{
    /**
     * @param MiniGameId $miniGameId
     * @param PlayerId   $playerId
     */
    public function linkToPlayer(MiniGameId $miniGameId, PlayerId $playerId);
}
