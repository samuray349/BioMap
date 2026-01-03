# API Switch Guide

This project now supports switching between Node.js API (Vercel) and PHP API (Railway).

## How to Switch APIs

### In `public/js/config.js`

Change the `API_PROVIDER` constant:

```javascript
// Use Node.js API (Vercel)
const API_PROVIDER = 'nodejs';

// Use PHP API (Railway)
const API_PROVIDER = 'php';
```

## Current Status

### Node.js API (Vercel) ✅
- **Status**: Fully functional
- **All endpoints**: Working
- **Location**: Vercel serverless functions
- **Entry point**: `api/index.js` → `server.js`

### PHP API (Railway) ⚠️
- **Status**: Partially implemented
- **Working endpoints**:
  - ✅ Authentication: login, signup, check-user, forgot-password, reset-password
  - ✅ Users: list, get, estados, estatutos
  - ⏳ Users: update, update_password, update_funcao, update_estado, delete (to be implemented)
  - ⏳ Animals: all endpoints (to be implemented)
  - ⏳ Institutions: all endpoints (to be implemented)
  - ⏳ Alerts: all endpoints (to be implemented)
- **Location**: Railway PHP server
- **Entry point**: `api/index.php`

## Configuration

### Railway PHP API
- Set `RAILWAY_API_URL` in `config.js` to your Railway domain
- Or use same domain if hosting on same server

### Node.js API (Vercel)
- Automatically detected from current domain
- Or set `NODEJS_API_BASE_URL` in `config.js`

## Testing

1. Set `API_PROVIDER = 'nodejs'` - test all features
2. Set `API_PROVIDER = 'php'` - test implemented endpoints
3. Compare functionality

## Completion

To complete the PHP API implementation, see `PHP_API_IMPLEMENTATION_GUIDE.md` for patterns and remaining endpoints.
