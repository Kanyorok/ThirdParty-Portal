<?php

return [
    // Minimum overall percentage (0-100) required to pass prequalification
    'passing_threshold' => (int)env('PREQUALIFICATION_PASSING_THRESHOLD', 60),
];
