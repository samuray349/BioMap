# PHP API Endpoints

This directory contains PHP versions of the Node.js API endpoints.

## How to Switch Between APIs

Edit `public/js/config.js` and change:
```javascript
const API_PROVIDER = 'nodejs'; // Change to 'php' to use PHP API
```

## Important Note

**The PHP API endpoints require the PostgreSQL PDO extension (`pdo_pgsql`) to be enabled on your server.**

Since Hostinger shared hosting doesn't support PostgreSQL connections, these endpoints won't work on Hostinger. However, you can:

1. **For School Project Demonstration:**
   - Show the PHP API code structure (demonstrates PHP/PDO/PostgreSQL knowledge)
   - Explain the architecture and how it mirrors the Node.js API
   - Document that it would work if PostgreSQL extension was available

2. **For Local Testing:**
   - Enable PostgreSQL extension in your local XAMPP
   - Test the PHP endpoints locally

3. **For Production:**
   - Use Node.js API (Vercel) - which works perfectly
   - Or use a hosting provider that supports PostgreSQL

## Endpoint Structure

Each PHP endpoint mirrors a Node.js endpoint:

- **Node.js:** `GET /users` → **PHP:** `GET /public/api/php/users/list.php`
- **Node.js:** `POST /api/login` → **PHP:** `POST /public/api/php/auth/login.php`
- **Node.js:** `GET /users/:id` → **PHP:** `GET /public/api/php/users/get.php?id=123`

## Database Configuration

All PHP endpoints use the database configuration in:
```
public/config/database.php
```

Make sure this file has the correct database credentials for your PostgreSQL database.

## Creating PHP Endpoints

To create a new PHP endpoint:

1. Map the Node.js endpoint to a PHP file path
2. Create the PHP file with the same logic as Node.js
3. Use PDO for database operations
4. Return JSON responses matching Node.js format
5. Add the mapping to `ENDPOINT_MAP` in `config.js`
