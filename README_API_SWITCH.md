# API Switch System - Node.js ↔ PHP

This project supports switching between Node.js API (Vercel) and PHP API (Hostinger) using a configuration switch.

## How It Works

The API switch system allows you to use either:
- **Node.js API** (hosted on Vercel) - ✅ Currently working
- **PHP API** (hosted on Hostinger) - ⚠️ Requires PostgreSQL extension

## Switching APIs

Edit `public/js/config.js` and change the `API_PROVIDER` constant:

```javascript
// Use Node.js API (Vercel)
const API_PROVIDER = 'nodejs';

// OR use PHP API (Hostinger)  
const API_PROVIDER = 'php';
```

## Current Status

### Node.js API (Vercel)
- ✅ **Working** - All endpoints functional
- ✅ Database: PostgreSQL (Google Cloud)
- ✅ Hosting: Vercel serverless functions

### PHP API (Hostinger)
- ⚠️ **Not Working** - PostgreSQL extension not available on Hostinger
- 📝 **Code Structure Created** - Demonstrates PHP/PDO knowledge
- 📝 **Can be demonstrated** - Show code, explain architecture

## For School Project Demonstration

Even though PHP API won't work on Hostinger, you can demonstrate:

1. **Code Structure:** Show PHP API files (demonstrates PHP/PDO/PostgreSQL knowledge)
2. **Architecture:** Explain how PHP endpoints mirror Node.js endpoints
3. **Switch System:** Demonstrate the configuration switch
4. **Documentation:** Explain the limitation and why Node.js is used in production

## File Structure

```
public/
├── js/
│   └── config.js          # API switch configuration
├── api/
│   └── php/               # PHP API endpoints (mirror Node.js)
│       ├── auth/
│       │   ├── login.php
│       │   └── signup.php
│       ├── users/
│       │   └── list.php
│       └── ...
└── config/
    └── database.php       # Database configuration (if using PHP API)
```

## Endpoint Mapping

The system automatically maps Node.js endpoints to PHP endpoints:

| Node.js Endpoint | PHP Endpoint |
|-----------------|--------------|
| `POST /api/login` | `POST /public/api/php/auth/login.php` |
| `GET /users` | `GET /public/api/php/users/list.php` |
| `GET /users/:id` | `GET /public/api/php/users/get.php?id=:id` |
| `GET /animais` | `GET /public/api/php/animais/list.php` |

## Benefits for School Project

1. ✅ **Demonstrates Both Technologies:**
   - Node.js/Express API (working)
   - PHP/PDO API (code structure)

2. ✅ **Shows Architecture Understanding:**
   - RESTful API design
   - Database abstraction
   - API routing

3. ✅ **Demonstrates Problem-Solving:**
   - Identified hosting limitation
   - Implemented working solution (Node.js)
   - Created alternative (PHP) for demonstration

## Recommendation

For your school project:
- **Use Node.js API in production** (it works!)
- **Show PHP API code** in your presentation/documentation
- **Explain the architecture** and how both APIs work
- **Document the hosting limitation** as part of your analysis

This demonstrates understanding of both technologies while having a working solution!
