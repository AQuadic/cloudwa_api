<?php

namespace AQuadic\Cloudwa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AQuadic\Cloudwa\Cloudwa
 */
class Cloudwa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AQuadic\Cloudwa\Cloudwa::class;
    }
}
