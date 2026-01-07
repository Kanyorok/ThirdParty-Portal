<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Auth\PersonalAccessToken;

echo "=== Checking Tokenable Types in Database ===\n\n";

try {
    // Get unique tokenable_type values
    $types = DB::table('t_SYSPersonalAccessTokens')
        ->select('tokenable_type', DB::raw('count(*) as count'))
        ->groupBy('tokenable_type')
        ->get();
    
    echo "Found types:\n";
    foreach ($types as $type) {
        echo "  - {$type->tokenable_type} (Count: {$type->count})\n";
        
        // Try to resolve the class
        $resolvedClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($type->tokenable_type) ?? $type->tokenable_type;
        echo "    Resolves to: " . ($resolvedClass ?: 'NULL') . "\n";
        
        if ($resolvedClass && class_exists($resolvedClass)) {
            echo "    Class exists: Yes\n";
            $traits = class_uses_recursive($resolvedClass);
            $hasTrait = in_array('Laravel\Sanctum\HasApiTokens', $traits);
            echo "    HasApiTokens: " . ($hasTrait ? 'Yes' : 'NO') . "\n";
            echo "    Traits: " . implode(', ', $traits) . "\n";
        } else {
            echo "    Class exists: NO\n";
        }
        echo "\n";
    }
    
    // Also verify Sanctum model configuration
    echo "Sanctum::$personalAccessTokenModel: " . \Laravel\Sanctum\Sanctum::$personalAccessTokenModel . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
