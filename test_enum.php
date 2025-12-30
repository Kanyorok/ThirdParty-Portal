<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $enum = \App\Enums\ThirdParty\ThirdPartyTypeEnum::cases();
    echo "Enum loaded successfully.\n";
    foreach ($enum as $case) {
        echo $case->name . "\n";
    }
} catch (\Throwable $e) {
    echo "Error loading Enum: " . $e->getMessage() . "\n";
    exit(1);
}
