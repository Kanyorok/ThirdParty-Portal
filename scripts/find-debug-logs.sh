#!/bin/bash
# Script to find and report development debug log statements that need removal

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║          Finding Development Debug Log Statements to Remove              ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

echo "Searching for debug log patterns..."
echo ""

# Find all development log statements
RESULTS=$(grep -rn "Log::\(info\|debug\|warning\|error\)" app/ --include="*.php" | \
    grep -E "(BEFORE|AFTER|SKIPPED|TODO|TEST|DEBUG|DUMP|\[DEBUG)")

if [ -z "$RESULTS" ]; then
    echo "✅ No development debug log statements found!"
    exit 0
fi

echo "❌ Development debug log statements found:"
echo ""
echo "$RESULTS" | while IFS=: read -r file line content; do
    echo "📁 $file"
    echo "   Line $line: $(echo "$content" | xargs)"
    echo ""
done

echo ""
echo "══════════════════════════════════════════════════════════════════════════"
echo "Total files with debug logs: $(echo "$RESULTS" | cut -d: -f1 | sort -u | wc -l)"
echo "Total debug log statements: $(echo "$RESULTS" | wc -l)"
echo ""
echo "To remove these, you can:"
echo "  1. Manually edit each file"
echo "  2. Delete the debug log lines"
echo "  3. Or replace with proper production logging"
echo ""
echo "Example of proper logging:"
echo "  Instead of: Log::info('BEFORE Auth::login', \$data);"
echo "  Use:        Log::info('User login attempt', ['user_id' => \$user->id]);"
echo "══════════════════════════════════════════════════════════════════════════"

exit 1
