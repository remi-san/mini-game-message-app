<?php

namespace MiniGameMessageApp\Message;

use MessageApp\Message\TextExtractor\MessageTextExtractor;
use MiniGame\GameResult;
use RemiSan\Intl\TranslatableResource;

class GameResultTextExtractor implements MessageTextExtractor
{
    /**
     * @var MessageTextExtractor[]
     */
    private $gameResultExtractors;

    /**
     * Constructor.
     *
     * @param $gameResultExtractors
     */
    public function __construct(array $gameResultExtractors = [])
    {
        $this->gameResultExtractors = $gameResultExtractors;
    }

    /**
     * Extract the message from the game result.
     *
     * @param  object $object
     * @param  string $languageIso
     * @return TranslatableResource
     */
    public function extractMessage($object, $languageIso)
    {
        if (!$object instanceof GameResult) {
            return null;
        }

        foreach ($this->gameResultExtractors as $extractor) {
            if ($message = $extractor->extractMessage($object, $languageIso)) {
                return $message;
            }
        }

        throw new \InvalidArgumentException('Unsupported Game Result');
    }
}
