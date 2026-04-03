<?php

namespace App\Policies;

use App\Models\Sondaggio;
use App\Models\User;

class SondaggioPolicy
{
    public function update(User $user, Sondaggio $sondaggio): bool
    {
        return (int) $sondaggio->autore_id === (int) $user->id;
    }

    public function delete(User $user, Sondaggio $sondaggio): bool
    {
        return (int) $sondaggio->autore_id === (int) $user->id;
    }

    public function viewStats(User $user, Sondaggio $sondaggio): bool
    {
        return (int) $sondaggio->autore_id === (int) $user->id;
    }
}
