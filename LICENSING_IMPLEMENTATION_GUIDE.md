# 🔐 Enterprise Licensing System - Implementation Guide

This guide explains how to implement, configure, and use the enterprise-grade licensing system for your ERP.

## 🏗️ Architecture Overview

The licensing system is built on **Ed25519 cryptographic signatures** with the following components:

- **License Verification Service** - Validates signed licenses against embedded public key
- **Instance Binding** - Ties licenses to specific database/server instances  
- **Module Middleware** - Protects routes based on licensed modules
- **Audit System** - Comprehensive logging of all licensing events
- **Admin Interface** - License upload, management, and monitoring

## 📋 Prerequisites

### Required PHP Extensions
```bash
# Install sodium extension (required for Ed25519)
sudo apt-get install php-sodium  # Ubuntu/Debian
# OR uncomment extension=sodium in php.ini for Windows
```

### Environment Configuration
Add to your `.env` file:
```env
# Licensing Configuration
LICENSING_VENDOR_PUBLIC_KEY=your_base64_encoded_public_key_here
LICENSING_GRACE_PERIOD_DAYS=7
LICENSING_CACHE_DURATION=300
```

## 🔑 Key Generation Process

### Step 1: Generate Vendor Keys (One-time Setup)

Create a secure key generation script on your **development machine**:

```php
<?php
// generate_vendor_keys.php
if (!extension_loaded('sodium')) {
    die("Sodium extension required\n");
}

// Generate Ed25519 keypair
$keypair = sodium_crypto_sign_keypair();
$publicKey = base64_encode(sodium_crypto_sign_publickey($keypair));
$privateKey = base64_encode(sodium_crypto_sign_secretkey($keypair));

echo "PUBLIC KEY (for .env):\n{$publicKey}\n\n";
echo "PRIVATE KEY (keep secure):\n{$privateKey}\n";
?>
```

**🚨 Security Critical:**
- Store the **private key** in a secure, offline location
- Never commit the private key to your repository
- The **public key** goes in your ERP's `.env` file

### Step 2: License Structure

Licenses are signed JSON payloads with this structure:

```json
{
  "license_id": "LIC-2025-000123",
  "tenant_name": "Acme Bank PLC", 
  "tenant_id": "acme-bank",
  "edition": "Enterprise",
  "modules": ["FINANCE", "PROCUREMENT", "INVENTORY", "PROPERTY"],
  "max_users": 50,
  "expires_at": "2026-09-30T23:59:59Z",
  "issued_at": "2025-09-24T10:00:00Z",
  "instance": {
    "db_guid": "1f0b1a1a-3f2c-4a54-9b2e-5cb4d830abcd",
    "host_fingerprint": "SHA256_HASH_OF_SYSTEM_INFO"
  },
  "features": {
    "drivers_based_budgeting": true,
    "bancassurance": true
  },
  "limits": {
    "branches": 40
  },
  "nonce": 1
}
```

## 🏭 License Generation Workflow

### For Vendors (License Issuers)

1. **Receive License Request**
   - Client provides instance information (DB GUID + fingerprint)
   - Business requirements (modules, users, features)

2. **Generate License**
   ```php
   // Example license generation
   $payload = json_encode($licenseData);
   $signature = base64_encode(sodium_crypto_sign_detached($payload, $privateKey));
   
   $licenseFile = [
       "payload" => $payload,
       "signature" => $signature
   ];
   
   file_put_contents('client_license.json', json_encode($licenseFile));
   ```

3. **Deliver to Client**
   - Send the license JSON file securely
   - Include public key ID for reference

### For Clients (License Users)

1. **Request License**
   - Go to **Settings → Licensing → Upload License**
   - Download instance information file
   - Send to vendor with requirements

2. **Install License**
   - Upload received license file
   - System automatically validates signature
   - Licensed modules become available

## 🛡️ Module Protection

### Automatic Route Protection

All major modules are automatically protected:

```php
// Example: Procurement routes
Route::middleware(['module:PROCUREMENT'])->group(function () {
    Route::resource('orders', PurchaseOrderController::class);
    // ... other procurement routes
});
```

### Available Module Keys (Parent Modules Only)

The licensing system enforces access at the **parent module level only**. Sub-modules automatically inherit access from their licensed parent:

- `THIRDPARTY` - Third Party Management (+ all supplier/customer sub-modules)
- `CRM` - Customer Management (+ all CRM workflow sub-modules)
- `PROCUREMENT` - Procurement (+ POs, tenders, RFQs, evaluations, etc.)
- `INVENTORY` - Inventory Management (+ stock, GRN, adjustments, etc.)
- `PROPERTY` - Property Management (+ leases, maintenance, etc.)
- `FLEET` - Fleet Management (+ vehicles, drivers, maintenance, etc.)
- `DMS` - Document Management (+ uploads, approvals, workflows, etc.)
- `LEGAL` - Legal & Compliance (+ contracts, cases, regulations, etc.)
- `INSURANCE` - Insurance & Bancassurance (+ policies, claims, products, etc.)
- `HRM` - Human Resources (+ employees, payroll, leave, etc.)
- `FINANCE` - Financial Management (+ GL, AP, AR, reconciliation, etc.)
- `BUDGET` - Budget & Analytics (+ planning, reports, analytics, etc.)

**Key Benefits:**
- ✅ **Simple Licensing**: License "PROCUREMENT" gives access to all 50+ procurement features
- ✅ **Automatic Inheritance**: New sub-modules don't require license updates
- ✅ **Clean Sales Process**: Customers choose broad functional areas, not individual features

### Custom Route Protection

Add module protection to custom routes:

```php
Route::middleware(['module:FINANCE'])->group(function () {
    Route::get('/financial-reports', [ReportsController::class, 'index']);
});
```

### Controller-Level Protection

Protect specific controller methods using the Feature class:

```php
use App\Support\Feature;

class ReportsController extends Controller
{
    public function financialReports()
    {
        // Enforce license requirement (throws 403 if not licensed)
        Feature::requires('FINANCE');
        
        // Your code here...
    }
    
    public function advancedAnalytics()
    {
        // Check multiple modules (needs at least one)
        Feature::requiresAny(['FINANCE', 'BUDGET']);
        
        // Check premium feature
        Feature::requiresFeature('advanced_reporting');
        
        // Your code here...
    }
}
```

### Using the License Facade

Quick licensing checks anywhere in your application:

```php
use License; // Facade auto-loaded

// Simple checks
if (License::hasModule('PROCUREMENT')) {
    // Show procurement menu
}

// Get license information
$edition = License::current()->getEdition();
$daysLeft = License::current()->getDaysUntilExpiry();
$maxUsers = License::current()->getMaxUsers();

// Check features
if (License::current()->hasFeature('bancassurance')) {
    // Enable bancassurance functionality
}
```

### Feature Class Examples

The Feature class provides elegant licensing checks:

```php
use App\Support\Feature;

// Basic module checks
Feature::hasModule('PROCUREMENT'); // Returns true/false
Feature::requires('FINANCE'); // Throws 403 if not licensed
Feature::requiresAll(['FINANCE', 'BUDGET']); // All must be licensed
Feature::requiresAny(['FINANCE', 'BUDGET']); // At least one must be licensed

// Feature checks  
Feature::hasFeature('drivers_based_budgeting');
Feature::requiresFeature('bancassurance');

// Usage limits
Feature::getLimit('max_users'); // Returns limit or null
Feature::enforceLimit('max_users', $currentUserCount); // Throws 403 if exceeded
Feature::isUnderLimit('branches', $currentBranches);

// License information
Feature::getEdition(); // 'Enterprise', 'Professional', etc.
Feature::getTenantName();
Feature::getDaysUntilExpiry();
Feature::isInGracePeriod();
```

## 📊 Edition & Feature Management

### Pre-defined Editions

Configure in `config/licensing.php`:

```php
'editions' => [
    'Community' => ['THIRDPARTY', 'SETTINGS', 'ACCOUNT'],
    'Professional' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'FINANCE'],
    'Enterprise' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'PROPERTY', 
                     'FLEET', 'DMS', 'LEGAL', 'INSURANCE', 'HRM', 'FINANCE', 'BUDGET']
],
```

### Premium Features

Enable/disable features in licenses:

```php
// In license payload
"features": {
    "drivers_based_budgeting": true,
    "bancassurance": true,
    "api_access": false
}

// In application code
if ($license->hasFeature('bancassurance')) {
    // Show bancassurance functionality
}
```

## 🔍 Monitoring & Audit

### Admin Interface

Access at **Settings → Licensing**:

- ✅ Current license status
- 📊 Module usage analytics  
- 📋 Audit logs and events
- ⬆️ License upload/renewal
- 💾 Instance information export

### Audit Events

System automatically logs:
- `validated` - Successful license validation
- `failed_signature` - Invalid signature detected
- `expired` - License expiry 
- `wrong_instance` - Instance binding mismatch
- `module_denied` - Unauthorized module access
- `license_uploaded` - New license installation

### Programmatic Monitoring

```php
use App\Services\Licensing\LicensingService;

$licensing = app(LicensingService::class);
$status = $licensing->getStatus();

// Check overall status
if (!$status['valid']) {
    // Handle invalid license
}

// Check specific modules
if (!$licensing->hasModule('PROCUREMENT')) {
    // Redirect or show message
}

// Check feature availability
$license = $licensing->current();
if (!$license->hasFeature('api_access')) {
    // Disable API features
}
```

## 🚨 Security Features

### Tamper Resistance

- **Cryptographic Signatures** - Ed25519 prevents license forgery
- **Instance Binding** - Licenses tied to specific installations
- **Nonce Protection** - Prevents replay/downgrade attacks  
- **Signature Verification** - Only valid signatures accepted
- **Cache Security** - Cached results invalidated on tampering

### Database Security

Even with database access, attackers cannot:
- ❌ Create valid licenses (need private key)
- ❌ Modify license content (signature validation fails)
- ❌ Reuse licenses on other instances (instance binding)
- ❌ Downgrade to older licenses (nonce protection)

### Grace Period

Configure graceful degradation:

```php
'grace_period_days' => 7,  // Continue working 7 days after expiry
```

## 🛠️ Troubleshooting

### Common Issues

1. **"Sodium extension not found"**
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-sodium
   
   # Windows
   # Uncomment extension=sodium in php.ini
   ```

2. **"Invalid signature" errors**
   - Verify public key in `.env` matches license
   - Check license file isn't corrupted
   - Ensure consistent JSON formatting

3. **"Wrong instance" errors**
   - License bound to different system
   - Request new license with current instance info

4. **Module access denied**
   - Check module included in license
   - Verify license hasn't expired
   - Clear license cache: `php artisan cache:clear`

### Debug Commands

```bash
# Check license status
php artisan tinker
>>> app(App\Services\Licensing\LicensingService::class)->getStatus()

# Clear license cache
php artisan cache:forget license_validation_result

# View recent audit logs
php artisan tinker
>>> App\Models\Licensing\LicenseAudit::latest()->take(10)->get()
```

## 🚀 Production Deployment

### Pre-deployment Checklist

- [ ] Sodium extension installed and enabled
- [ ] Public key added to `.env` 
- [ ] License file obtained and uploaded
- [ ] Module protection tested
- [ ] Grace period configured
- [ ] Audit logging enabled
- [ ] Backup procedures in place

### Performance Considerations

- License validation is cached (5 minutes default)
- Database queries optimized with indexes
- Minimal impact on route performance
- Background validation tasks optional

### Backup & Recovery

- Back up license files and instance information
- Document public key ID and vendor details
- Test license restoration procedures
- Monitor license expiry dates

## 📞 Support & Maintenance

### Regular Maintenance

- Monitor license expiry dates (30+ days notice)
- Review audit logs for unusual activity
- Update licenses before expiration
- Test module functionality regularly

### License Renewal Process

1. **30 days before expiry**: Request renewal from vendor
2. **7 days before expiry**: Grace period begins (warnings shown)
3. **After expiry**: System blocks access (except grace period)
4. **Upload new license**: Immediate restoration of access

---

## 🎯 Quick Start Summary

1. **Install** sodium extension
2. **Configure** public key in `.env`
3. **Upload** license via admin interface  
4. **Verify** modules are accessible
5. **Monitor** via licensing dashboard

The system is now fully operational with enterprise-grade licensing protection! 🔐✨
