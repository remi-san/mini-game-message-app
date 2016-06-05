<?php

namespace MiniGameMessageApp\Message;

use MiniGame\GameResult;

class MiniGameMessageExtractor
{
    /**
     * Extract the message from the game result.
     *
     * @param GameResult $result
     * @param string     $languageIso
     * @return string
     */
    public function extractMessage(GameResult $result, $languageIso) // TODO move to message app and accept events
    {
        return $result->getAsMessage(); // TODO retrieve and translate the message / remove getAsMessage
    }
}
