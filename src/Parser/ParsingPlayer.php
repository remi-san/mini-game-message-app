<?php

namespace MiniGameMessageApp\Parser;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

interface ParsingPlayer
{
    /**
     * @return PlayerId
     */
    public function getPlayerId();

    /**
     * @return MiniGameId
     */
    public function getGameId();
}
