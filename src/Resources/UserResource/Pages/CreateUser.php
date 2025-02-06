<?php

namespace Backstage\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Backstage\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
