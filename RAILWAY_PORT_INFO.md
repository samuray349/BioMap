# Railway Port Configuration

## How Railway Handles Ports

### For Your Application (Internal)
- Railway automatically assigns a port via the `$PORT` environment variable
- Your PHP server should listen on: `0.0.0.0:$PORT`
- This is already configured in `railway.json`: `php -S 0.0.0.0:$PORT -t api`
- **You don't need to know or set a specific port number**

### For Your Domain (External)
- Railway domain URLs use standard ports:
  - **HTTPS**: Port 443 (default, no need to specify)
  - **HTTP**: Port 80 (automatically redirected to HTTPS)
- Your Railway URL will be: `https://your-app.railway.app`
- **No port number needed in the URL** - Railway handles routing automatically

## Example Configuration

```json
// railway.json
{
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t api"
    //                                      ^^^^^ Railway provides this automatically
  }
}
```

```javascript
// config.js
const PHP_API_BASE_URL = 'https://your-app.railway.app';
//                        ^^^^^^^^^^^^^^^^^^^^^^^^^^^ No port needed!
```

## What Happens Behind the Scenes

1. **Railway assigns a port** (e.g., 12345) and sets `$PORT=12345`
2. **Your PHP server starts** listening on `0.0.0.0:12345` internally
3. **Railway's proxy** routes traffic from `https://your-app.railway.app:443` → internal port 12345
4. **You only use the domain URL** - Railway handles the port mapping

## Summary

- ✅ **Use**: `https://your-app.railway.app` (no port)
- ✅ **Listen on**: `0.0.0.0:$PORT` (in your start command)
- ❌ **Don't use**: `https://your-app.railway.app:443` (unnecessary)
- ❌ **Don't hardcode**: A specific port number

The `$PORT` variable is for Railway's internal routing - you only need the domain URL in your configuration!
