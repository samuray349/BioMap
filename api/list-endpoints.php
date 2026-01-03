<?php
/**
 * API Endpoints List
 * Returns a list of all available API endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$endpoints = [
    'status' => 'PHP API on Railway',
    'base_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
    'endpoints' => [
        'health' => [
            'method' => 'GET',
            'path' => '/health.php',
            'status' => '✅ Working',
            'description' => 'Health check endpoint'
        ],
        'list_endpoints' => [
            'method' => 'GET',
            'path' => '/list-endpoints.php',
            'status' => '✅ Working',
            'description' => 'List all available endpoints (this endpoint)'
        ]
    ],
    'nodejs_api' => [
        'note' => 'All other endpoints are currently available via Node.js API on Vercel',
        'endpoints' => [
            'authentication' => [
                'POST /api/login',
                'POST /api/signup',
                'POST /api/check-user',
                'POST /api/forgot-password',
                'POST /api/reset-password'
            ],
            'users' => [
                'GET /users',
                'GET /users/estados',
                'GET /users/estatutos',
                'GET /users/:id',
                'PUT /users/:id',
                'PUT /users/:id/password',
                'PUT /users/:id/funcao',
                'PUT /users/:id/estado',
                'DELETE /users/:id'
            ],
            'animais' => [
                'GET /animais',
                'GET /animais/familias',
                'GET /animais/estados',
                'GET /animaisDesc/:id',
                'POST /animais',
                'PUT /animais/:id',
                'DELETE /animais/:id'
            ],
            'instituicoes' => [
                'GET /instituicoes',
                'GET /instituicoesDesc/:id',
                'POST /instituicoes',
                'PUT /instituicoes/:id',
                'DELETE /instituicoes/:id'
            ],
            'alerts' => [
                'GET /api/alerts',
                'POST /api/alerts',
                'DELETE /api/alerts/:id'
            ],
            'cron' => [
                'GET /cron/cleanup-tokens',
                'GET /cron/cleanup-avistamentos'
            ]
        ]
    ],
    'documentation' => [
        'full_list' => 'See API_ENDPOINTS.md in the repository root for complete documentation',
        'nodejs_source' => 'See server.js for Node.js API implementation',
        'php_router' => 'See api/index.php for PHP API router'
    ]
];

echo json_encode($endpoints, JSON_PRETTY_PRINT);
