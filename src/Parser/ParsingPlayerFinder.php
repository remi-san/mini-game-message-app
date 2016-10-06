<?php

namespace MiniGameMessageApp\Parser;

use MessageApp\User\ApplicationUserId;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

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

    /**
     * Delete the parsing player.
     *
     * @param PlayerId $playerId
     */
    public function delete(PlayerId $playerId);

    /**
     * Delete parsing players by game id.
     *
     * @param MiniGameId $gameId
     */
    public function deleteByGame(MiniGameId $gameId);
}
