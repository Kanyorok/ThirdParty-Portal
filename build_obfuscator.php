<?php

require 'vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node\Scalar\String_;

// --- CONFIGURATION ---
// Directories to obfuscate (Don't obfuscate the whole vendor folder!)
$directories = [
    __DIR__ . '/app/Http/Controllers',
    __DIR__ . '/app/Services',
    // Add your License Manager path here
];

$outputDir = __DIR__ . '/dist'; // Where the obfuscated code goes

// --- THE OBFUSCATOR VISITOR ---
class ObfuscatorVisitor extends NodeVisitorAbstract
{
    private $variableMap = [];

    // Helper to generate random variable names like $l1O0l
    private function generateRandomName()
    {
        $chars = 'Il10O'; // Visually confusing characters
        $len = rand(4, 8);
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[rand(0, strlen($chars) - 1)];
        }
        return '_' . $str; // Start with underscore to ensure valid var name
    }

    public function enterNode(Node $node)
    {
        // 1. Obfuscate Strings
        if ($node instanceof String_) {
            // Skip specific strings if needed (like configuration keys)
            // Simple Base64 encoding wrapper
            $original = $node->value;

            // We replace "string" with base64_decode("encoded_string")
            // Ideally, you would create a custom helper function in your app 
            // called something generic like `_x()` to do the decoding so `base64_decode` isn't obvious.

            // For this demo, we perform a simple HEX encoding
            $hex = bin2hex($original);

            // Replace the node with a function call: hex2bin('...')
            return new Node\Expr\FuncCall(
                new Node\Name('hex2bin'),
                [
                    new Node\Arg(new String_($hex))
                ]
            );
        }

        // 2. Obfuscate Variables
        // Note: This is risky in Laravel due to compact(), dynamic properties, etc.
        // Use with caution. Safe for internal logic methods.
        if ($node instanceof Node\Expr\Variable) {
            $name = $node->name;

            // Don't rename $this, $_GET, $_POST, or global vars
            if (is_string($name) && !in_array($name, ['this', '_GET', '_POST', '_SERVER'])) {
                if (!isset($this->variableMap[$name])) {
                    $this->variableMap[$name] = $this->generateRandomName();
                }
                $node->name = $this->variableMap[$name];
            }
        }
    }
}

// --- THE BUILD PROCESS ---

// In php-parser 5.x, use createForNewestSupportedVersion() to auto-detect the best parser
$parser = (new ParserFactory)->createForNewestSupportedVersion();
$traverser = new NodeTraverser();
$traverser->addVisitor(new ObfuscatorVisitor());
$printer = new PrettyPrinter\Standard();

echo "Starting Obfuscation...\n";

foreach ($directories as $dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;

        $code = file_get_contents($file->getPathname());

        try {
            // 1. Parse
            $stmts = $parser->parse($code);

            // 2. Obfuscate
            $stmts = $traverser->traverse($stmts);

            // 3. Print (Minified logic)
            $obfuscatedCode = $printer->prettyPrintFile($stmts);

            // 4. Save to dist folder
            $relativePath = str_replace(__DIR__, '', $file->getPathname());
            $targetPath = $outputDir . $relativePath;

            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0777, true);
            }

            file_put_contents($targetPath, $obfuscatedCode);
            echo "Obfuscated: $relativePath\n";
        } catch (Error $e) {
            echo "Parse Error: {$e->getMessage()} in {$file->getPathname()}\n";
        }
    }
}

echo "Done. Copy the contents of /dist into your Docker image.\n";
