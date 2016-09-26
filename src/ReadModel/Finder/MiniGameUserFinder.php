<?php

namespace MiniGameMessageApp\ReadModel\Finder;

use MessageApp\User\ApplicationUser;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

interface MiniGameUserFinder
{
    /**
     * Gets a user by a player id
     *
     * @param PlayerId $playerId
     *
     * @return ApplicationUser
     */
    public function getByPlayerId(PlayerId $playerId);

    /**
     * Gets users by a game id
     *
     * @param MiniGameId $gameId
     *
     * @return ApplicationUser[]
     */
    public function getByGameId(MiniGameId $gameId);
}
