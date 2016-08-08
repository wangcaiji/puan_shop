<?php


namespace App\BasicShop\Post\Facades;


use Illuminate\Support\Facades\Facade;

class Post extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'post';
    }
}