<?php

return [
    'drivers' => [
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION_ID'),
        ],
    ],

    'api_request_timeout' => env('AI_API_REQUEST_TIMEOUT', 30),

    'embeddings' => [
        'model' => 'text-embedding-3-small',
    ],
];
