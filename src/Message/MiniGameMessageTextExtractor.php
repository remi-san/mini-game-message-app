<?php

namespace MiniGameMessageApp\Message;

use MessageApp\Event\UnableToCreateUserEvent;
use MessageApp\Event\UserEvent;
use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MessageApp\Parser\Exception\MessageParserException;
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
        // TODO retrieve and translate the message / remove getAsMessage

        if ($object instanceof GameResult) {
            return $object->getAsMessage();
        }

        if ($object instanceof UnableToCreateUserEvent) {
            return $object->getReason();
        }

        if ($object instanceof UserEvent) {
            return $object->getAsMessage();
        }

        if ($object instanceof MessageParserException) {
            return $object->getMessage();
        }

        return null;
    }
}
