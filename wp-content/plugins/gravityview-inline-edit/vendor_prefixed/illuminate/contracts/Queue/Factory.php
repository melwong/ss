<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 20-February-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string  $name
     * @return \GravityKit\GravityEdit\Foundation\ThirdParty\Illuminate\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
