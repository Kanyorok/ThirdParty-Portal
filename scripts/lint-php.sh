#!/bin/bash
# Comprehensive PHP linting script for BRERP Laravel Application

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║                 BRERP PHP Code Quality & Linting Check                    ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

EXIT_CODE=0

# 1. Detect Debug Log Statements
echo "┌─────────────────────────────────────────────────────────────────────────┐"
echo "│ 1/5 Detecting Debug/Development Log Statements                         │"
echo "└─────────────────────────────────────────────────────────────────────────┘"
php scripts/detect-debug-logs.php
if [ $? -ne 0 ]; then
    EXIT_CODE=1
fi
echo ""

# 2. PHP CS Fixer (Code Formatting)
echo "┌─────────────────────────────────────────────────────────────────────────┐"
echo "│ 2/5 PHP CS Fixer - Code Formatting Standards (PSR-12)                  │"
echo "└─────────────────────────────────────────────────────────────────────────┘"
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff --allow-risky=yes
if [ $? -ne 0 ]; then
    EXIT_CODE=1
fi
echo ""

# 3. PHP CodeSniffer (Coding Standards)
echo "┌─────────────────────────────────────────────────────────────────────────┐"
echo "│ 3/5 PHP CodeSniffer - Detecting Violations & Debug Code                │"
echo "└─────────────────────────────────────────────────────────────────────────┘"
vendor/bin/phpcs --standard=phpcs.xml --report=summary
if [ $? -ne 0 ]; then
    EXIT_CODE=1
fi
echo ""

# 4. PHPStan (Static Analysis)
echo "┌─────────────────────────────────────────────────────────────────────────┐"
echo "│ 4/5 PHPStan - Static Analysis (Level 5)                                │"
echo "└─────────────────────────────────────────────────────────────────────────┘"
vendor/bin/phpstan analyse --memory-limit=2G --error-format=table
if [ $? -ne 0 ]; then
    EXIT_CODE=1
fi
echo ""

# 5. Check for specific debug patterns
echo "┌─────────────────────────────────────────────────────────────────────────┐"
echo "│ 5/5 Checking for Development-Only Log Statements                       │"
echo "└─────────────────────────────────────────────────────────────────────────┘"

DEBUG_LOGS=$(grep -rn "Log::\(info\|debug\|warning\)" app/ --include="*.php" | \
    grep -E "(BEFORE|AFTER|SKIPPED|TODO|TEST|DEBUG|DUMP)" | wc -l)

if [ "$DEBUG_LOGS" -gt 0 ]; then
    echo "❌ Found $DEBUG_LOGS development log statement(s)"
    echo ""
    echo "Development log statements found in:"
    grep -rn "Log::\(info\|debug\|warning\)" app/ --include="*.php" | \
        grep -E "(BEFORE|AFTER|SKIPPED|TODO|TEST|DEBUG|DUMP)" | head -10
    echo ""
    echo "Run: grep -rn \"Log::\" app/ | grep -E \"(BEFORE|AFTER|SKIPPED)\" to see all"
    EXIT_CODE=1
else
    echo "✅ No development log statements found"
fi
echo ""

# Summary
echo "╔═══════════════════════════════════════════════════════════════════════════╗"
if [ $EXIT_CODE -eq 0 ]; then
    echo "║                     ✅ ALL CHECKS PASSED                                   ║"
else
    echo "║                     ❌ SOME CHECKS FAILED                                  ║"
    echo "║                                                                           ║"
    echo "║  To automatically fix some issues, run:                                   ║"
    echo "║    composer lint-fix                                                      ║"
    echo "║    vendor/bin/phpcbf                                                      ║"
fi
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

exit $EXIT_CODE
