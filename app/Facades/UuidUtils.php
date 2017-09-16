<?php 

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UuidUtils extends Facade {
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'uuidutils'; // the IoC binding.
    }


}