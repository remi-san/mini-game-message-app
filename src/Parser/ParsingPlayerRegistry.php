<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;
use MiniGame\Entity\PlayerId;

interface ParsingPlayerRegistry
{
    /**
     * Gets the active player for the user
     *
     * @param  ApplicationUserId $userId
     *
     * @return ParsingPlayer
     */
    public function getActivePlayerForUser(ApplicationUserId $userId);

    /**
     * Register the parsing player.
     *
     * @param ParsingPlayer $player
     *
     * @return void
     */
    public function register(ParsingPlayer $player);

    /**
     * Unregister the parsing player.
     *
     * @param PlayerId $playerId
     */
    public function unregister(PlayerId $playerId);
}
