# Storage Link Setup for Production

## Problem
Uploaded logos work in local development but not in production because the symbolic link from `public/storage` to `storage/app/public` is missing.

## Solution

### Step 1: Create Storage Symbolic Link
Run this command in your production environment:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, allowing publicly uploaded files to be accessible via the web.

### Step 2: Verify the Link
Check if the link was created successfully:

```bash
ls -la public/storage
```

You should see:
```
lrwxrwxrwx 1 user user 23 Nov 14 10:00 storage -> ../../storage/app/public
```

### Step 3: Set Correct Permissions (if needed)
If images still don't show, ensure proper permissions:

```bash
chmod -R 775 storage
chmod -R 775 public/storage
chown -R www-data:www-data storage
chown -R www-data:www-data public/storage
```

Replace `www-data` with your web server user (could be `nginx`, `apache`, etc.)

### Step 4: Verify APP_URL in Production
Make sure your `.env` file in production has the correct APP_URL:

```env
APP_URL=https://yourdomain.com
```

NOT:
```env
APP_URL=http://127.0.0.1:8000
```

### Step 5: Clear Configuration Cache
After making changes:

```bash
php artisan config:clear
php artisan config:cache
php artisan optimize:clear
```

## Code Fix Applied
The `IntegrationController::_saveOrganizationBranding()` method has been updated to:
- Strip out full URLs if accidentally saved
- Store only relative paths (e.g., `storage/branding/logo_xxx.png`)
- Ensure paths work across all environments

## Testing
1. Upload a new logo in Organization Branding
2. Check the database - the `Configuration` JSON should contain:
   ```json
   {
     "logo": "storage/branding/logo_xxxxxx.png",
     "name": "Your Org Name",
     "motto": "Your Motto"
   }
   ```
   
3. The logo should now display correctly in production

## Troubleshooting
If logo still doesn't show:
1. Check browser console for 404 errors
2. Verify file exists: `ls -la storage/app/public/branding/`
3. Check web server error logs
4. Ensure `.htaccess` or nginx config allows access to `/storage/` path
