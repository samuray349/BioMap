# PHP API Implementation Guide

This guide explains the structure and how to complete the remaining PHP API endpoints.

## Completed Endpoints ✅

### Authentication
- ✅ `api/auth/login.php` - POST /api/login
- ✅ `api/auth/signup.php` - POST /api/signup
- ✅ `api/auth/check_user.php` - POST /api/check-user
- ✅ `api/auth/forgot_password.php` - POST /api/forgot-password
- ✅ `api/auth/reset_password.php` - POST /api/reset-password

### Users (Partial)
- ✅ `api/users/list.php` - GET /users
- ✅ `api/users/get.php` - GET /users/:id
- ✅ `api/users/estados.php` - GET /users/estados
- ✅ `api/users/estatutos.php` - GET /users/estatutos

## Remaining Endpoints to Implement

### Users
- ⏳ `api/users/update.php` - PUT /users/:id
- ⏳ `api/users/update_password.php` - PUT /users/:id/password
- ⏳ `api/users/update_funcao.php` - PUT /users/:id/funcao
- ⏳ `api/users/update_estado.php` - PUT /users/:id/estado
- ⏳ `api/users/delete.php` - DELETE /users/:id

### Animals
- ⏳ `api/animais/list.php` - GET /animais
- ⏳ `api/animais/get.php` - GET /animaisDesc/:id
- ⏳ `api/animais/familias.php` - GET /animais/familias
- ⏳ `api/animais/estados.php` - GET /animais/estados
- ⏳ `api/animais/create.php` - POST /animais
- ⏳ `api/animais/update.php` - PUT /animais/:id
- ⏳ `api/animais/delete.php` - DELETE /animais/:id

### Institutions
- ⏳ `api/instituicoes/list.php` - GET /instituicoes
- ⏳ `api/instituicoes/get.php` - GET /instituicoesDesc/:id
- ⏳ `api/instituicoes/create.php` - POST /instituicoes
- ⏳ `api/instituicoes/update.php` - PUT /instituicoes/:id
- ⏳ `api/instituicoes/delete.php` - DELETE /instituicoes/:id

### Alerts
- ⏳ `api/alerts/list.php` - GET /api/alerts
- ⏳ `api/alerts/create.php` - POST /api/alerts
- ⏳ `api/alerts/delete.php` - DELETE /api/alerts/:id

## Implementation Pattern

Each endpoint follows this pattern:

```php
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { // or POST, PUT, DELETE
    sendError('Method not allowed', 405);
}

try {
    // Your logic here
    // Use Database::query() for SELECT
    // Use Database::execute() for UPDATE/DELETE
    // Use Database::insert() for INSERT
    // Use Database::queryOne() for single row
    
    sendJson($data);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    sendError('Error message', 500);
}
?>
```

## Key Functions Available

### Database Functions (from `api/config/database.php`)
- `Database::query($sql, $params)` - Execute SELECT, returns array of rows
- `Database::queryOne($sql, $params)` - Execute SELECT, returns single row or null
- `Database::execute($sql, $params)` - Execute UPDATE/DELETE, returns row count
- `Database::insert($sql, $params)` - Execute INSERT with RETURNING, returns inserted row
- `Database::beginTransaction()` - Start transaction
- `Database::commit()` - Commit transaction
- `Database::rollback()` - Rollback transaction

### Helper Functions (from `api/config/helpers.php`)
- `setCorsHeaders()` - Set CORS headers
- `handlePreflight()` - Handle OPTIONS requests
- `getJsonInput()` - Get JSON from request body
- `sendJson($data, $statusCode)` - Send JSON response
- `sendError($message, $statusCode)` - Send error response
- `hashPassword($password)` - Hash password (SHA256)
- `validateRequired($data, $fields)` - Validate required fields
- `getQueryParam($name, $default)` - Get query parameter

## Router Configuration

After creating each endpoint, update `api/index.php` to route requests to it.

## Next Steps

1. Complete the remaining user endpoints
2. Complete animal endpoints
3. Complete institution endpoints
4. Complete alert endpoints
5. Update router in `api/index.php`
6. Test all endpoints
7. Update `config.js` API_PROVIDER to 'php' when ready
