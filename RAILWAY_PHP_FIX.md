# Fix for Railway npm ci Error

Railway was trying to install Node.js dependencies because it detected `package.json`. Since this is a PHP-only API, we've created a `Dockerfile` to explicitly use PHP only.

## Solution

1. **Use Dockerfile instead of Nixpacks**: The `Dockerfile` explicitly installs PHP 8.2 with PostgreSQL extensions and skips Node.js entirely.

2. **Railway Configuration**: Updated `railway.json` to use `DOCKERFILE` builder instead of `NIXPACKS`.

## Files Changed

- `Dockerfile`: New file that sets up PHP-only environment
- `railway.json`: Changed builder from `NIXPACKS` to `DOCKERFILE`
- `nixpacks.toml`: Can be deleted (no longer needed with Dockerfile)

## Next Steps

1. Commit these changes to your Git repository
2. Push to GitHub
3. Railway will automatically detect the `Dockerfile` and use it instead of Nixpacks
4. Railway will build using Docker, which only installs PHP (no Node.js/npm)

## Why This Works

- Dockerfile gives us full control over what gets installed
- We explicitly install PHP 8.2 with PostgreSQL extensions
- We skip Node.js entirely - no `package.json` processing
- Railway will build using Docker instead of auto-detecting Node.js

## Alternative: If You Still Want to Use Nixpacks

If you prefer to use Nixpacks, you would need to:
1. Fix the `package-lock.json` sync issue by running `npm install` locally
2. Commit the updated `package-lock.json`
3. Railway will install Node.js dependencies (even though you don't need them for PHP API)

But using Dockerfile is cleaner since we don't need Node.js at all.
