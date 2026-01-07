<?php

require __DIR__ . '/vendor/autoload.php';

use Laravel\Sanctum\HasApiTokens;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Auth\PersonalAccessToken;

echo "=== Checking HasApiTokens Trait Methods ===\n";
$reflection = new ReflectionClass(HasApiTokens::class);
$methods = $reflection->getMethods();

echo "\nMethods in HasApiTokens trait:\n";
foreach ($methods as $method) {
    echo "  - " . $method->getName() . "\n";
}

echo "\n=== Checking PersonalAccessToken Model Methods ===\n";
$tokenReflection = new ReflectionClass(PersonalAccessToken::class);
$tokenMethods = $tokenReflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC);

echo "\nStatic methods in PersonalAccessToken:\n";
foreach ($tokenMethods as $method) {
    if ($method->isStatic()) {
        echo "  - " . $method->getName() . " (from " . $method->getDeclaringClass()->getName() . ")\n";
    }
}

echo "\n=== Checking ThirdPartyUser Model ===\n";
$userReflection = new ReflectionClass(ThirdPartyUser::class);
echo "\nTraits used by ThirdPartyUser:\n";
$traits = class_uses_recursive(ThirdPartyUser::class);
foreach ($traits as $trait) {
    echo "  - $trait\n";
}

echo "\nDone!\n";
