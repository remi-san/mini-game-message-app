<?php

namespace MiniGameMessageApp\Message;

use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MiniGame\GameResult;

class MiniGameMessageTextExtractor implements MessageTextExtractor
{
    /**
     * Extract the message from the game result.
     *
     * @param  object $object
     * @param  string $languageIso
     * @return string
     */
    public function extractMessage($object, $languageIso)
    {
        if (!$object instanceof GameResult) {
            return null;
        }

        return $object->getAsMessage();
    }
}
