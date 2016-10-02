<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;

interface ParsingPlayerFinder
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
}
