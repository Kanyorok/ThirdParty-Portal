# 🛠️ **Licensing Scripts Directory**

This directory contains command-line tools for managing the enterprise licensing system.

## 📁 **Available Scripts**

### **🔑 Key Generation**
- **`generate_vendor_keys.php`** - Generate Ed25519 key pair for signing licenses
  ```bash
  php scripts/generate_vendor_keys.php
  ```

### **🎫 License Management**
- **`generate_sample_license.php`** - Interactive license generator with templates
  ```bash
  php scripts/generate_sample_license.php
  ```

- **`import_license.php`** - Import license file without web interface
  ```bash
  php scripts/import_license.php license_file.json
  ```

- **`manage_licenses.php`** - Complete license management tool
  ```bash
  # List all licenses
  php scripts/manage_licenses.php list
  
  # Check current status
  php scripts/manage_licenses.php status
  
  # Remove specific license
  php scripts/manage_licenses.php remove LIC-2025-00123
  
  # Remove all licenses (testing reset)
  php scripts/manage_licenses.php clear
  
  # Refresh license cache
  php scripts/manage_licenses.php refresh
  ```

## 🚀 **Quick Start Workflow**

1. **Generate Keys** (one-time setup)
   ```bash
   php scripts/generate_vendor_keys.php
   ```

2. **Add Public Key to .env**
   ```env
   LICENSING_VENDOR_PUBLIC_KEY=your_public_key_here
   ```

3. **Generate Test License**
   ```bash
   php scripts/generate_sample_license.php
   ```

4. **Import License**
   ```bash
   php scripts/import_license.php your_license_file.json
   ```

5. **Verify Status**
   ```bash
   php scripts/manage_licenses.php status
   ```

## 🧪 **Testing Scenarios**

### **Basic Testing**
```bash
# Generate community license
php scripts/generate_sample_license.php  # Choose option 1

# Import and test
php scripts/import_license.php license_*.json
php scripts/manage_licenses.php status
```

### **Multi-License Testing**
```bash
# Generate different editions
php scripts/generate_sample_license.php  # Community
php scripts/generate_sample_license.php  # Professional  
php scripts/generate_sample_license.php  # Enterprise

# Import each and test behavior
```

### **Cleanup Testing**
```bash
# Reset all licenses for clean testing
php scripts/manage_licenses.php clear

# Verify clean state
php scripts/manage_licenses.php status
```

## 🔒 **Security Notes**

- **Private Keys**: Never commit private keys to version control
- **Production**: Use these scripts only in development/testing
- **Permissions**: Ensure scripts are not web-accessible
- **Backup**: Keep secure backup of your private key

## 📊 **Output Files**

Scripts generate these files:
- `license_*.json` - Generated license files
- `private_key_*.txt` - Generated private keys (secure immediately!)
- `public_key_*.txt` - Generated public keys

## 🆘 **Troubleshooting**

### **Common Issues**
- **Sodium extension**: Install php-sodium
- **Database connection**: Verify Laravel .env configuration
- **Permission errors**: Check file permissions on scripts directory
- **Invalid signature**: Ensure public key matches in .env

### **Getting Help**
1. Check the main `LICENSING_TESTING_GUIDE.md` for comprehensive documentation
2. Use `php scripts/manage_licenses.php status` for current system state
3. Check Laravel logs at `storage/logs/laravel.log`
4. Review audit logs in the web interface: Settings → Licensing → Audit

## 🎯 **Best Practices**

1. **Always clear cache** after configuration changes:
   ```bash
   php artisan config:clear
   ```

2. **Verify licenses** after import:
   ```bash
   php scripts/manage_licenses.php status
   ```

3. **Use web interface** for production license management

4. **Keep audit trail** by checking logs regularly

---

**For complete testing workflows and detailed documentation, see `LICENSING_TESTING_GUIDE.md`**
