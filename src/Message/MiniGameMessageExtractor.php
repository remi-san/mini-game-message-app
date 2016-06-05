<?php

namespace MiniGameMessageApp\Message;

use MiniGame\GameResult;

class MiniGameMessageExtractor
{
    /**
     * Extract the message from the game result.
     *
     * @param GameResult $result
     * @return string
     */
    public function extractMessage(GameResult $result)
    {
        return $result->getAsMessage(); // TODO deal with other languages / remove getAsMessage
    }
}
