# Railway Networking Port Configuration

## When Creating a Public Domain

When Railway asks "which port should it be listening to", you need to specify the **internal port** your PHP application is listening on.

## Finding the Port

### Option 1: Check Railway Service Settings
1. Go to your Railway service
2. Click on "Settings" tab
3. Look for "Port" or "Environment Variables"
4. Check the `PORT` variable value (usually `8080` by default)

### Option 2: Check Deployment Logs
1. Go to your Railway service
2. Click on "Deployments" tab
3. View the latest deployment logs
4. Look for the port number in the startup logs

### Option 3: Default Port
Railway typically uses **port 8080** by default for the `$PORT` environment variable.

## Configuration Answer

When Railway asks for the port in the networking section:

**Answer: `8080`** (or whatever port Railway assigned to your service)

This should match the `$PORT` environment variable that your PHP server is listening on.

## Verify Your Configuration

Your `railway.json` should have:
```json
{
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t api"
  }
}
```

This means your PHP server listens on whatever port Railway assigns (typically 8080).

## Step-by-Step in Railway Dashboard

1. **Go to Networking Section**
   - Click on your service
   - Go to "Networking" tab
   - Click "Add Public Domain" or "Generate Domain"

2. **When Asked for Port**
   - Enter: `8080` (Railway's default)
   - Or check your service's `PORT` environment variable

3. **Alternative: Set Explicit Port**
   - In your service settings → Variables
   - Add: `PORT=8080` (if not already set)
   - Then use `8080` in the networking configuration

## Important Notes

- The port you specify in networking is the **internal port** your app listens on
- Railway's proxy will route external traffic (port 443/80) to this internal port
- Your domain URL won't show the port: `https://your-app.railway.app` (no `:8080`)
- Railway handles the port mapping automatically

## Troubleshooting

If you're not sure which port:
1. Check Railway service logs for the startup message
2. Look for: `PHP Development Server started at http://0.0.0.0:XXXX`
3. Use that port number in the networking configuration
