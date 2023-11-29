<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 20-February-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Support\Facades;

use Illuminate\Database\Eloquent\Model;
use GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Support\Testing\Fakes\EventFake;

/**
 * @see \Illuminate\Events\Dispatcher
 */
class Event extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap($fake = new EventFake);

        Model::setEventDispatcher($fake);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
