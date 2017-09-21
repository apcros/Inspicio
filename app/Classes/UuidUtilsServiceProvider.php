<?php 
namespace App\Classes;

use Illuminate\Support\ServiceProvider;

class UuidUtilsServiceProvider extends ServiceProvider{
    public function register()
    {
        $this->app->bind('uuidutils', '\App\Classes\UuidUtils' );
    }
}