# Railway Setup Guide for PHP API

This guide explains how to set up your PHP API on Railway so it can be called from your Hostinger-hosted website.

## Prerequisites

- GitHub account (for connecting Railway to your repository)
- Railway account (sign up at https://railway.app)
- Your project code in a Git repository

## Step 1: Prepare Your PHP API Files

1. Create the PHP API directory structure on Railway:
   ```
   api/
   ├── index.php (or your entry point)
   ├── auth/
   │   ├── login.php
   │   └── signup.php
   └── ...
   ```

2. Your PHP API files should be in the root directory or a subdirectory that Railway can access.

## Step 2: Create railway.json Configuration

Create a `railway.json` file in your project root:

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "echo 'PHP build complete'"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t api",
    "healthcheckPath": "/health",
    "healthcheckTimeout": 100,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

## Step 3: Create a Simple PHP Router (if needed)

Since Railway uses PHP built-in server, you might need a simple router. Create `api/index.php`:

```php
<?php
// Simple router for Railway PHP server
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Route to appropriate PHP file
if ($path === '/health' || $path === '/health.php') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Add your routing logic here
// For example: /api/login -> api/auth/login.php
```

## Step 4: Set Up Railway Project

1. **Go to Railway Dashboard**: https://railway.app/dashboard
2. **Create New Project**: Click "New Project"
3. **Deploy from GitHub**: Select "Deploy from GitHub repo"
4. **Select Repository**: Choose your BioMap repository
5. **Add Service**: Railway will detect your project

## Step 5: Configure Environment Variables

In Railway dashboard, go to your service → Variables tab and add:

```
PGHOST=34.175.211.25
PGPORT=5432
PGDATABASE=biomap
PGUSER=admin
PGPASSWORD=Passwordbd1!
PGSSL=true
```

**Important**: Railway automatically provides a `PORT` variable - use this in your PHP server command.

## Step 6: Configure CORS for Hostinger

In your PHP API files, add CORS headers to allow requests from Hostinger:

```php
header('Access-Control-Allow-Origin: https://lucped.antrob.eu');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
```

Or allow all origins for development:
```php
header('Access-Control-Allow-Origin: *');
```

## Step 7: Get Railway URL

1. After deployment, Railway will provide a URL like: `https://your-app.railway.app`
2. **Important**: Railway automatically handles port routing - you don't need to specify a port in the URL
3. Railway uses standard ports:
   - **HTTP**: Port 80 (automatically redirected to HTTPS)
   - **HTTPS**: Port 443 (used for the domain)
4. Your PHP application listens on `$PORT` internally (Railway assigns this automatically)
5. Copy the Railway URL (without port) - you'll need it for configuration

## Step 8: Update config.js

Update `public/js/config.js` to use Railway URL:

```javascript
// PHP API Configuration (Railway)
function getPhpApiBaseUrl() {
    // Railway provides a public URL after deployment
    return 'https://your-app.railway.app';
}
```

Or use environment detection:
```javascript
function getPhpApiBaseUrl() {
    if (typeof window !== 'undefined') {
        // Production: Use Railway URL
        // You can also use environment variables or detect based on domain
        const isProduction = window.location.hostname === 'lucped.antrob.eu';
        return isProduction 
            ? 'https://your-app.railway.app'  // Railway URL
            : 'http://localhost:3000';         // Local development
    }
    return 'https://your-app.railway.app';
}
```

## Step 9: Verify PostgreSQL Extension

Railway's PHP should include PostgreSQL extension, but verify by:

1. Creating a test endpoint: `api/test_db.php`
2. Add: `<?php phpinfo(); ?>` and check for `pdo_pgsql`

## Step 10: Deploy and Test

1. **Commit and Push** your code to GitHub
2. Railway will automatically deploy
3. Check Railway logs for any errors
4. Test the API endpoint: `https://your-app.railway.app/health`
5. Test from your Hostinger site

## Troubleshooting

### Issue: 404 Errors
- Check that your PHP files are in the correct directory
- Verify the `startCommand` in railway.json points to the right directory
- Check Railway logs for routing issues

### Issue: Database Connection Failed
- Verify environment variables are set correctly
- Check that Railway can access Google Cloud PostgreSQL (firewall rules)
- Test database connection with a simple PHP script

### Issue: CORS Errors
- Verify CORS headers are set in PHP files
- Check that Hostinger domain is allowed
- Test with Postman/curl to verify API works

### Issue: PHP Extension Missing
- Railway uses Nixpacks which should include pdo_pgsql
- If missing, you may need to create a `nixpacks.toml` file

## Alternative: Use composer.json for Dependencies

If you need specific PHP extensions, create `composer.json`:

```json
{
    "require": {
        "php": "^8.2",
        "ext-pdo": "*",
        "ext-pdo_pgsql": "*"
    }
}
```

## Cost Estimate

Railway's free tier:
- $5/month credit (usually enough for small projects)
- Charges only for actual usage
- Perfect for school projects

## Next Steps

1. Set up Railway account and project
2. Configure environment variables
3. Deploy your PHP API
4. Update config.js with Railway URL
5. Test from Hostinger site
6. Switch API_PROVIDER to 'php' in config.js

## Notes

- Railway automatically handles HTTPS
- Railway provides a public URL for your API
- Railway services don't sleep (unlike Render free tier)
- Monitor usage in Railway dashboard to stay within free tier
