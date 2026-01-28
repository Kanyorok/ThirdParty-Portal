# PHP Linting & Code Quality Guide for BRERP

## Overview

This project now has comprehensive PHP linting and code quality tools to ensure:
- **Code Formatting**: PSR-12 standard compliance
- **Debug Detection**: Catches development log statements and debug code
- **Static Analysis**: Finds potential bugs and type errors
- **Commented Code Detection**: Identifies commented-out code blocks

## Tools Installed

1. **PHP CS Fixer** - Automatic code formatting
2. **PHP_CodeSniffer (PHPCS)** - Coding standards detection
3. **PHPStan with Larastan** - Static analysis for Laravel
4. **Custom Debug Detector** - Finds development log statements

## Quick Commands

### Run All Linters
```bash
composer lint
# OR
./scripts/lint-php.sh
```

### Fix Formatting Issues Automatically
```bash
composer lint-fix
# OR
vendor/bin/php-cs-fixer fix --allow-risky=yes
```

### Fix PHPCS Issues Automatically
```bash
composer phpcbf
# OR
vendor/bin/phpcbf
```

### Individual Checks

#### 1. PHP CS Fixer (Formatting)
```bash
# Check only (dry-run)
composer php-cs-fixer

# Fix automatically
vendor/bin/php-cs-fixer fix --allow-risky=yes
```

#### 2. PHP CodeSniffer (Standards)
```bash
# Summary report
composer phpcs

# Full detailed report
composer phpcs-full

# Auto-fix
composer phpcbf
```

#### 3. PHPStan (Static Analysis)
```bash
composer phpstan

# With more details
vendor/bin/phpstan analyse --memory-limit=2G -vvv
```

#### 4. Debug Log Detection
```bash
composer detect-debug

# Or directly
php scripts/detect-debug-logs.php
```

## What Gets Detected

### ❌ Development Log Statements (Will Fail Lint)

```php
// BAD - Development debugging logs
Log::info('BEFORE Auth::login', $data);
Log::info('AFTER Auth::login', $data);
Log::info('SKIPPED session regenerate', $data);
Log::debug('TESTING this feature', $data);
Log::warning('[DEBUG_SANCTUM] User loaded', $data);

// BAD - Debug functions
dd($variable);
dump($variable);
var_dump($variable);
print_r($variable);
die('debug message');
exit('stopping here');
```

### ✅ Proper Logging (Allowed)

```php
// GOOD - Production-appropriate logging
Log::error('Failed to process payment', [
    'user_id' => $user->id,
    'error' => $exception->getMessage()
]);

Log::warning('Supplier has no associated ThirdParty record', [
    'supplier_id' => $supplier->id
]);

Log::info('User logged in successfully', [
    'user_id' => $user->id,
    'ip' => request()->ip()
]);

// GOOD - Using Laravel's built-in logging channels
logger()->error('Payment processing failed', ['order_id' => $orderId]);
```

### Commented Code Detection

The linters will flag commented-out code blocks:

```php
// BAD - Commented out code (should be removed or uncommented)
// public function oldMethod() {
//     return $this->doSomething();
// }

// GOOD - Explanatory comments
// This method processes payments using the Stripe API
public function processPayment() {
    // Implementation
}
```

## Configuration Files

- **`.php-cs-fixer.dist.php`** - PHP CS Fixer configuration (PSR-12 + Laravel rules)
- **`phpcs.xml`** - PHP_CodeSniffer configuration (PSR-12 + custom rules)
- **`phpstan.neon`** - PHPStan configuration (Level 5 with Larastan)
- **`scripts/detect-debug-logs.php`** - Custom debug detection script
- **`scripts/lint-php.sh`** - Comprehensive linting script

## Pre-Commit Setup (Recommended)

To automatically check code before commits, add to `.git/hooks/pre-commit`:

```bash
#!/bin/bash
./scripts/lint-php.sh
exit $?
```

Then make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

## CI/CD Integration

Add to your CI/CD pipeline (e.g., GitHub Actions, GitLab CI):

```yaml
- name: PHP Linting
  run: |
    composer install --no-interaction
    composer lint
```

## Fixing Issues

### Auto-Fix Formatting
```bash
# Fix all formatting issues
composer lint-fix

# Fix PHPCS issues
vendor/bin/phpcbf
```

### Manual Fixes Required

1. **Remove debug log statements**: Search and remove development logs
   ```bash
   grep -rn "Log::" app/ | grep -E "(BEFORE|AFTER|SKIPPED|DEBUG|TEST)"
   ```

2. **Remove commented code**: Uncomment useful code or delete dead code

3. **Fix PHPStan errors**: Add type hints, fix logic errors

## Excluding Files

### Exclude from PHP CS Fixer
Edit `.php-cs-fixer.dist.php` and add to the Finder:

```php
->exclude('some-directory')
->notPath('path/to/file.php')
```

### Exclude from PHPCS
Edit `phpcs.xml` and add:

```xml
<exclude-pattern>*/path/to/exclude/*</exclude-pattern>
```

### Exclude from PHPStan
Edit `phpstan.neon` and add to `excludePaths`:

```yaml
excludePaths:
    - path/to/exclude
```

## Best Practices

1. **Run linters locally before committing**
   ```bash
   composer lint
   ```

2. **Fix formatting automatically when possible**
   ```bash
   composer lint-fix
   ```

3. **Remove debug statements before production deployment**

4. **Use proper log levels**:
   - `Log::emergency()` - System unusable
   - `Log::alert()` - Immediate action required
   - `Log::critical()` - Critical conditions
   - `Log::error()` - Error conditions
   - `Log::warning()` - Warning conditions
   - `Log::notice()` - Normal but significant
   - `Log::info()` - Informational messages
   - `Log::debug()` - Debug-level messages (disable in production)

5. **Use descriptive log messages** without ALL CAPS debug keywords

## Troubleshooting

### Memory Limit Errors
```bash
# Increase memory for PHPStan
vendor/bin/phpstan analyse --memory-limit=4G
```

### Too Many Errors
```bash
# Start with specific directories
vendor/bin/phpcs app/Http/Controllers

# Or specific files
vendor/bin/phpcs app/Models/User.php
```

### Cache Issues
```bash
# Clear caches
rm -f .php-cs-fixer.cache .phpcs-cache
vendor/bin/phpstan clear-result-cache
```

## Support

For issues or questions about linting:
1. Check this README
2. Review configuration files
3. Run individual linters for detailed output
4. Check tool documentation:
   - [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
   - [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
   - [PHPStan](https://phpstan.org/)
   - [Larastan](https://github.com/larastan/larastan)
