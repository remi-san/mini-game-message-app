<?php

namespace MiniGameMessageApp;

use MessageApp\User\PersistableUser;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

interface PersistableMiniGameUser extends PersistableUser
{
    /**
     * @param MiniGameId $miniGameId
     * @param PlayerId   $playerId
     */
    public function linkToPlayer(MiniGameId $miniGameId, PlayerId $playerId);
}
