<?php

namespace MiniGameMessageApp\Store;

use MessageApp\User\PersistableUser;
use MiniGameMessageApp\PersistableMiniGameUser;

interface MiniGameUserStore
{
    /**
     * Finds an user by its primary key / identifier.
     *
     * @param string $id The identifier.
     *
     * @return PersistableMiniGameUser The user.
     */
    public function find($id);

    /**
     * @param PersistableUser $user
     *
     * @return void
     */
    public function save(PersistableUser $user);
}
