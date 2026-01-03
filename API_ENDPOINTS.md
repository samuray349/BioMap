# API Endpoints Documentation

This document lists all available API endpoints in the BioMap project.

## Current Status

- **Node.js API (Vercel)**: ✅ Fully functional - all endpoints available
- **PHP API (Railway)**: ⚠️ Currently only `/health.php` is implemented

## Available Endpoints

### Health Check
- **GET** `/health` or `/health.php`
  - **Status**: ✅ Works in both APIs
  - **Description**: Service health check
  - **Response**: `{ "status": "ok", "service": "PHP API", "timestamp": "..." }`

### Authentication Endpoints

#### Node.js API (Vercel) - Currently Active
- **POST** `/api/login` - User login
- **POST** `/api/signup` - User registration
- **POST** `/api/check-user` - Check if username/email exists
- **POST** `/api/forgot-password` - Request password reset
- **POST** `/api/reset-password` - Reset password with token

#### PHP API (Railway) - Not Yet Implemented
These endpoints are mapped in `public/js/config.js` but PHP files don't exist yet:
- `auth/login.php`
- `auth/signup.php`
- `auth/check_user.php`
- `auth/forgot_password.php`
- `auth/reset_password.php`

### User Endpoints

#### Node.js API (Vercel)
- **GET** `/users` - Get all users
- **GET** `/users/estados` - Get user states
- **GET** `/users/estatutos` - Get user statuses
- **GET** `/users/:id` - Get user by ID
- **PUT** `/users/:id` - Update user
- **PUT** `/users/:id/password` - Update user password
- **PUT** `/users/:id/funcao` - Update user role
- **PUT** `/users/:id/estado` - Update user state
- **DELETE** `/users/:id` - Delete user

#### PHP API (Railway) - Not Yet Implemented
Mapped endpoints (files don't exist yet):
- `users/list.php`
- `users/estados.php`
- `users/estatutos.php`
- `users/get.php`
- `users/update_password.php`
- `users/update_funcao.php`
- `users/update_estado.php`

### Animal Endpoints

#### Node.js API (Vercel)
- **GET** `/animais` - Get all animals (with filters)
- **GET** `/animais/familias` - Get animal families
- **GET** `/animais/estados` - Get animal states
- **GET** `/animaisDesc/:id` - Get animal details by ID
- **POST** `/animais` - Create new animal
- **PUT** `/animais/:id` - Update animal
- **DELETE** `/animais/:id` - Delete animal

#### PHP API (Railway) - Not Yet Implemented
Mapped endpoints (files don't exist yet):
- `animais/list.php`
- `animais/familias.php`
- `animais/estados.php`
- `animais/get.php`

### Institution Endpoints

#### Node.js API (Vercel)
- **GET** `/instituicoes` - Get all institutions
- **GET** `/instituicoesDesc/:id` - Get institution details by ID
- **POST** `/instituicoes` - Create new institution
- **PUT** `/instituicoes/:id` - Update institution
- **DELETE** `/instituicoes/:id` - Delete institution

#### PHP API (Railway) - Not Yet Implemented
Mapped endpoints (files don't exist yet):
- `instituicoes/list.php`
- `instituicoes/get.php`

### Alert Endpoints

#### Node.js API (Vercel)
- **GET** `/api/alerts` - Get all alerts
- **POST** `/api/alerts` - Create new alert
- **DELETE** `/api/alerts/:id` - Delete alert

#### PHP API (Railway) - Not Yet Implemented
Mapped endpoints (files don't exist yet):
- `alerts/list.php`

### Cron Jobs

#### Node.js API (Vercel)
- **GET** `/cron/cleanup-tokens` - Clean up expired tokens
- **GET** `/cron/cleanup-avistamentos` - Clean up old sightings

## Where to Find API Files

### Node.js API (Vercel)
- **Location**: `server.js` (root directory)
- **Handler**: `api/index.js` (Vercel serverless function)
- **Configuration**: `vercel.json`

### PHP API (Railway)
- **Location**: `api/` directory
- **Router**: `api/index.php` (currently basic router)
- **Configuration**: `railway.json`, `Dockerfile`
- **Status**: Only `/health.php` exists and works

## Testing APIs

### Test Node.js API (Vercel)
```bash
# Health check
curl https://your-vercel-app.vercel.app/health

# Login (example)
curl -X POST https://your-vercel-app.vercel.app/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Test PHP API (Railway)
```bash
# Health check
curl https://your-railway-app.railway.app/health.php

# Other endpoints (when implemented)
curl https://your-railway-app.railway.app/api/login
```

## Creating New PHP Endpoints

To create a new PHP endpoint:

1. **Create the PHP file** in the appropriate directory:
   ```
   api/auth/login.php
   api/users/list.php
   api/animais/get.php
   ```

2. **Add routing** in `api/index.php`:
   ```php
   $routes = [
       'login' => 'auth/login.php',
       'users' => 'users/list.php',
       // ... etc
   ];
   ```

3. **Update mapping** in `public/js/config.js` (if needed):
   ```javascript
   const ENDPOINT_MAP = {
       'api/login': 'auth/login.php',
       // ... etc
   };
   ```

## API Configuration

The API provider is configured in `public/js/config.js`:
```javascript
const API_PROVIDER = 'nodejs'; // Change to 'php' to use PHP API
```

Currently set to `'nodejs'` (using Vercel API).

## Next Steps

To implement PHP endpoints, you would need to:
1. Create PHP files in the `api/` directory structure
2. Implement database connections using PDO PostgreSQL
3. Update the router in `api/index.php`
4. Test each endpoint
5. Switch `API_PROVIDER` to `'php'` in `config.js` when ready

For now, all API calls go through the Node.js API on Vercel, which is fully functional.
