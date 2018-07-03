<?php

// Specify queries count threshold for alert level
return [
    'enabled' => env('DB_QUERIES_ALERT_ENABLED', false),
    'error' => 100,
    'warning' => 50,
    'info' => 20,
];
