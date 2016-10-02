<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

interface ParsingPlayerFactory
{
    /**
     * @param PlayerId          $playerId
     * @param MiniGameId        $gameId
     * @param ApplicationUserId $getId
     *
     * @return ParsingPlayer
     */
    public function createParsingPlayer(PlayerId $playerId, MiniGameId $gameId, ApplicationUserId $getId);
}
