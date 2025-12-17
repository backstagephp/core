<?php

namespace Backstage\Resources\UserResource\Pages;

use Backstage\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    public static function getResource(): string
    {
        return config('backstage.users.resources.users', UserResource::class);
    }
}
