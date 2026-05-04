<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ThrottleRequests extends ThrottleRequests
{
    /**
     * The rate limit key resolvers.
     *
     * @var array
     */
    protected $limiters = [
        'global' => '',
    ];
}
