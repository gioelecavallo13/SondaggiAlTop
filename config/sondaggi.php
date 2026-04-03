<?php

return [
    'response_ip_salt' => env('APP_RESPONSE_SALT', 'dev-response-salt-change-me'),
    'rate_limit_window_seconds' => 900,
    'rate_limit_max_attempts' => 30,
    'anonymous_vote_cookie' => 'sm_vote_client',
];
