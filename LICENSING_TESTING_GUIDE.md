# 🧪 **Complete Licensing System Testing Guide**

This comprehensive guide covers everything you need to test, develop, and manage the licensing system in your ERP.

---

## 🚀 **Quick Start (5-Minute Setup)**

### **1. Generate Your Keys**
```bash
# Generate Ed25519 key pair
php scripts/generate_vendor_keys.php
```

### **2. Update Environment**
Copy the public key to your `.env`:
```env
LICENSING_VENDOR_PUBLIC_KEY=your_generated_public_key_here
```

### **3. Clear Config Cache**
```bash
php artisan config:clear
```

### **4. Generate Test License**
```bash
# Interactive license generator
php scripts/generate_sample_license.php
```

### **5. Test in Web Interface**
Go to **Settings → Licensing** to see your license status!

---

## 📚 **Detailed Testing Workflows**

## 🔑 **Workflow 1: First-Time Setup**

### **Step 1: Key Generation**
```bash
cd C:\Users\Daniel.Mbugua\Desktop\BRERP
php scripts/generate_vendor_keys.php
```

**Expected Output:**
```
🔐 Generating Ed25519 Vendor Key Pair
============================================================

✅ Keys generated successfully!

📋 PUBLIC KEY (add to .env file):
----------------------------------------
LICENSING_VENDOR_PUBLIC_KEY=abcd1234...

🔑 PRIVATE KEY (keep EXTREMELY secure!):
----------------------------------------
efgh5678...

💾 Private key saved to: private_key_2025-09-24_15-30-45.txt
💾 Public key saved to: public_key_2025-09-24_15-30-45.txt
```

### **Step 2: Environment Configuration**
Edit your `.env` file:
```env
LICENSING_VENDOR_PUBLIC_KEY=abcd1234...
LICENSING_GRACE_PERIOD_DAYS=7
LICENSING_CACHE_DURATION=300
```

### **Step 3: Clear Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 🎫 **Workflow 2: Generate Test Licenses**

### **Interactive License Generator**
```bash
php scripts/generate_sample_license.php
```

**Interactive Flow:**
1. **Enter Private Key**: Paste your base64 private key
2. **Choose Template**: 
   - Community (5 users, basic modules)
   - Professional (25 users, business modules)
   - Enterprise (100 users, all modules)
   - Custom (your selection)
3. **Set Duration**: 30 days, 1 year, 2 years, or custom
4. **Company Name**: Test Organization or custom

**Generated Files:**
- `license_LIC-2025-00123_2025-09-24_15-30-45.json`

---

## 📤 **Workflow 3: Apply Licenses**

### **Option A: Web Interface (Recommended)**
1. Go to **Settings → Licensing**
2. Click **Upload License**
3. Choose your license file
4. Enter public key ID (e.g., "vendor-2025")
5. Click **Upload**

### **Option B: Command Line**
```bash
php scripts/import_license.php license_LIC-2025-00123_2025-09-24_15-30-45.json
```

**Expected Output:**
```
🎫 Importing License: license_LIC-2025-00123_2025-09-24_15-30-45.json
============================================================

📋 License Information:
   License ID: LIC-2025-00123
   Tenant: Test Organization
   Edition: Professional
   Modules: PROCUREMENT, FINANCE, INVENTORY
   Expires: 2026-09-24 15:30:45

✅ License imported successfully!

🔍 Verification Results:
   Status: ✅ Valid
   Licensed Modules: PROCUREMENT, FINANCE, INVENTORY
   Days Until Expiry: 365
   Max Users: 25

🎯 License is now active!
```

---

## 🗑️ **Workflow 4: Remove/Test Different Licenses**

### **List Current Licenses**
```bash
php scripts/manage_licenses.php list
```

### **Remove Specific License**
```bash
php scripts/manage_licenses.php remove LIC-2025-00123
```

### **Remove ALL Licenses (Testing Reset)**
```bash
php scripts/manage_licenses.php clear
```

### **Check License Status**
```bash
php scripts/manage_licenses.php status
```

### **Refresh License Cache**
```bash
php scripts/manage_licenses.php refresh
```

---

## 🖥️ **Friendly Web Interface**

### **Admin Dashboard: Settings → Licensing**

The web interface provides a comprehensive licensing management experience:

#### **📊 License Status Dashboard**
- **Current License Status**: Valid/Invalid with visual indicators
- **Tenant Information**: Company name, edition, expiry date
- **Module Overview**: Licensed modules with badges
- **Usage Metrics**: Current users vs. limits, days until expiry
- **Feature Status**: Premium features enabled/disabled

#### **📤 License Upload Interface**
- **Drag & Drop Upload**: Easy license file upload
- **Live Preview**: See license details before importing
- **Validation Feedback**: Real-time error checking
- **Success Confirmation**: Clear upload status

#### **📱 Instance Management**
- **Instance Information**: DB GUID and host fingerprint
- **Download Instance Info**: Get JSON file for license requests
- **Copy to Clipboard**: Quick access to instance details

#### **📋 License History**
- **All Licenses**: View active and revoked licenses
- **License Details**: Click to see full license information
- **Revoke Licenses**: Deactivate licenses with confirmation
- **Activity Timeline**: Recent license events

#### **📈 Audit Logs**
- **Security Events**: All licensing activity logged
- **Filterable View**: By event type, license ID, date range
- **Export Capability**: Download audit reports
- **Real-time Updates**: Live activity monitoring

---

## 🧪 **Testing Scenarios**

### **Scenario 1: Module Access Testing**

#### **Test Different Editions**
1. **Community License**:
   ```bash
   # Generate community license
   php scripts/generate_sample_license.php
   # Choose option 1 (Community)
   ```
   - ✅ Should access: Third Party, Settings, Account
   - ❌ Should block: Procurement, Finance, others

2. **Professional License**:
   ```bash
   # Generate professional license  
   php scripts/generate_sample_license.php
   # Choose option 2 (Professional)
   ```
   - ✅ Should access: Business modules
   - ❌ Should block: Advanced modules (Legal, Property)

3. **Enterprise License**:
   ```bash
   # Generate enterprise license
   php scripts/generate_sample_license.php
   # Choose option 3 (Enterprise)
   ```
   - ✅ Should access: All modules

#### **Verify Module Protection**
1. **Try accessing unlicensed modules** → Should see licensing error page
2. **Check navigation menus** → Only licensed modules should appear
3. **Direct URL access** → Should be blocked by middleware

### **Scenario 2: License Expiry Testing**

#### **Test Expired License**
1. Generate license with past expiry date:
   ```bash
   php scripts/generate_sample_license.php
   # Choose option 4 (Custom)
   # Enter past date when prompted
   ```

2. **Expected Behavior**:
   - Grace period warning (if within 7 days)
   - Full blockage after grace period
   - Clear expiry messages in UI

#### **Test Grace Period**
1. Generate license expiring in 3 days
2. **Expected Behavior**:
   - System continues working
   - Warning banners displayed
   - Audit logs show grace period access

### **Scenario 3: Invalid License Testing**

#### **Test Tampered License**
1. Generate valid license
2. Edit the JSON file (change modules)
3. Try to import
4. **Expected**: Import should fail with signature error

#### **Test Wrong Instance**
1. Generate license for different DB GUID
2. Try to import
3. **Expected**: Should fail with instance binding error

### **Scenario 4: Multiple License Testing**

#### **License Replacement**
1. Import License A (Community)
2. Import License B (Professional)
3. **Expected**: License A automatically revoked, License B active

#### **License Revocation**
1. Import valid license
2. Revoke via web interface or CLI
3. **Expected**: Immediate access loss, audit log entry

---

## 🔧 **Development & Debug Tools**

### **Quick Status Check**
```bash
php scripts/manage_licenses.php status
```

### **Force Cache Refresh**
```bash
php scripts/manage_licenses.php refresh
```

### **Laravel Tinker Testing**
```php
php artisan tinker

// Check licensing service
$licensing = app(\App\Services\Licensing\LicensingService::class);
$status = $licensing->getStatus();
dd($status);

// Test module access
use App\Support\Feature;
Feature::hasModule('PROCUREMENT'); // true/false

// Check current license
use License;
License::current()->getTenantName();
License::current()->getEdition();
```

### **Debug License Issues**
```php
// Check instance binding
$instance = \App\Models\Licensing\Instance::current();
echo "DB GUID: " . $instance->DbGuid . "\n";
echo "Fingerprint: " . $instance->HostFingerprint . "\n";

// View audit logs
$audits = \App\Models\Licensing\LicenseAudit::latest()->take(10)->get();
foreach($audits as $audit) {
    echo "{$audit->EventAt}: {$audit->Event} - {$audit->Detail}\n";
}
```

---

## 📊 **Testing Checklist**

### **✅ Basic Functionality**
- [ ] Key generation works
- [ ] License creation succeeds
- [ ] License import via web interface works
- [ ] License import via CLI works  
- [ ] License status displays correctly
- [ ] Module access enforcement works
- [ ] License expiry handling works
- [ ] Grace period functions correctly

### **✅ Security Testing**
- [ ] Tampered licenses rejected
- [ ] Wrong instance licenses rejected
- [ ] Signature validation works
- [ ] Replay protection active
- [ ] Audit logging comprehensive

### **✅ Edge Cases**
- [ ] Multiple license handling
- [ ] License revocation immediate
- [ ] Cache invalidation works
- [ ] Database failure handling
- [ ] Network issues handled gracefully

### **✅ User Experience**
- [ ] Web interface intuitive
- [ ] Error messages helpful
- [ ] Upload process smooth
- [ ] Status information clear
- [ ] Admin tools accessible

---

## 🆘 **Troubleshooting Common Issues**

### **"Sodium extension not found"**
```bash
# Windows (XAMPP/WAMP)
# Edit php.ini, uncomment: extension=sodium
# Restart Apache

# Linux
sudo apt-get install php-sodium
sudo systemctl restart apache2
```

### **"Invalid signature" errors**
1. Check public key in `.env` matches license
2. Verify private key used for signing
3. Ensure JSON format is preserved
4. Run `php artisan config:clear`

### **"Wrong instance" errors**
1. Check if license was generated for this system
2. Verify DB GUID hasn't changed
3. Generate new license with current instance info

### **Module access denied**
1. Verify license includes required module
2. Check license hasn't expired  
3. Clear license cache: `php scripts/manage_licenses.php refresh`
4. Check route middleware configuration

### **Web interface not loading**
1. Verify route protection middleware
2. Check user has Super Admin role
3. Clear route cache: `php artisan route:clear`
4. Check for any PHP errors in logs

---

## 🎯 **Quick Reference Commands**

```bash
# Key Management
php scripts/generate_vendor_keys.php

# License Generation  
php scripts/generate_sample_license.php

# License Import
php scripts/import_license.php <license_file>

# License Management
php scripts/manage_licenses.php list
php scripts/manage_licenses.php status
php scripts/manage_licenses.php remove <license_id>
php scripts/manage_licenses.php clear
php scripts/manage_licenses.php refresh

# Cache Management
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 💡 **Pro Testing Tips**

1. **Keep Private Keys Safe**: Store in password manager, never in git
2. **Test All Editions**: Generate Community, Pro, Enterprise licenses
3. **Test Expiry Scenarios**: Create licenses with different expiry dates
4. **Use Multiple Instances**: Test instance binding on different systems
5. **Monitor Audit Logs**: Check that all events are logged properly
6. **Test Edge Cases**: Invalid formats, network issues, concurrent access
7. **User Experience Testing**: Get non-technical users to test the interface
8. **Performance Testing**: Generate licenses under load conditions

The licensing system is now ready for comprehensive testing! 🚀✨

**Need help?** Check the audit logs at **Settings → Licensing → Audit Logs** for detailed troubleshooting information.
