<?php
/**
 * @license MIT
 *
 * Modified by gravitykit on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityCharts\Foundation\ThirdParty\Illuminate\Support\Facades;

use GravityKit\GravityCharts\Foundation\ThirdParty\Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactoryContract;

/**
 * @see \GravityKit\GravityCharts\Foundation\ThirdParty\Illuminate\Contracts\Broadcasting\Factory
 */
class Broadcast extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BroadcastingFactoryContract::class;
    }
}
