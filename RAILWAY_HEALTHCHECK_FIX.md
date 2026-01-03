# Fix for Railway Healthcheck Failure

## Problem
The healthcheck was failing with "service unavailable" errors even though the build was successful.

## Root Cause
The Dockerfile was copying `api/` to `/app/api/` and serving with `-t api`, which could cause path resolution issues with Railway's healthcheck.

## Solution
Changed the Dockerfile to:
1. Copy `api/` contents directly to `/app/` (not `/app/api/`)
2. Serve from `.` (current directory) instead of `api/`
3. Updated `railway.json` startCommand to match: `php -S 0.0.0.0:$PORT -t .`

## Files Changed
- `Dockerfile`: Changed `COPY api/ /app/api/` to `COPY api/ /app/` and `-t api` to `-t .`
- `railway.json`: Changed startCommand from `-t api` to `-t .`

## Result
Now when Railway serves from `/app/`, the healthcheck at `/health.php` will correctly find `/app/health.php`.

## Next Steps
1. Commit and push these changes
2. Railway will rebuild with the new configuration
3. Healthcheck should now pass successfully
