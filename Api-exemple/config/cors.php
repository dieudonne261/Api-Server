<?php
return [
    'paths' => ['api/*'], // Specify the paths affected by CORS
    'allowed_methods' => ['*'], // Allowed HTTP methods (e.g., GET, POST, etc.)
    'allowed_origins' => ['*'], // Allowed origins (domains)
    'allowed_headers' => ['*'], // Allowed request headers
    'exposed_headers' => [], // Exposed response headers
    'max_age' => 0, // Cache duration (in seconds)
    'supports_credentials' => false, // Enable credentials (true/false)
];
