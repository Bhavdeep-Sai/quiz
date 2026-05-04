<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature;

class ValidateSignature extends ValidateSignature
{
    /**
     * The names of the query string parameters that should be ignored.
     *
     * @var array<int, string>
     */
    protected $ignore = [
        'fbclid',
        'utm_campaign',
        'utm_content',
        'utm_medium',
        'utm_source',
        'utm_term',
    ];
}
