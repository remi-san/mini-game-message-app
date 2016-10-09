<?php

namespace MiniGameMessageApp\Message;

use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MiniGameApp\Event\MiniGameAppErrorEvent;
use RemiSan\Intl\TranslatableResource;

class GameErrorTextExtractor implements MessageTextExtractor
{
    /**
     * Extract the message from the game result.
     *
     * @param  object $object
     * @return TranslatableResource
     */
    public function extractMessage($object)
    {
        if (!$object instanceof MiniGameAppErrorEvent) {
            return null;
        }

        return new TranslatableResource(
            $object->getAsMessage()
        );
    }
}
