<?php
/**
 * status_check.php
 *
 * Reusable include to verify the current user's role (`funcao_id`) by calling
 * the Node API (/users/:id). Designed to be required at the top of other PHP
 * pages. It sets $STATUS_CHECK with the API result and provides helper
 * function require_funcao_or_redirect() to enforce role-based redirects.
 *
 * Note: The API base URL defaults to https://bio-map-xi.vercel.app. If your Node API
 * runs on a different host, set the $_SERVER['API_BASE_URL'] value before
 * including this file or edit the $apiBase fallback below.
 */

require_once __DIR__ . '/session_helper.php';

// Default API base (adjust if your Node server is elsewhere)
$apiBase = $_SERVER['API_BASE_URL'] ?? 'https://bio-map-xi.vercel.app';

$STATUS_CHECK = [
    'loggedIn' => false,
    'user' => null,
    'api_user' => null,
    'error' => null
];

$current = getCurrentUser();
if (!$current) {
    // Not logged in
    $STATUS_CHECK['loggedIn'] = false;
} else {
    $STATUS_CHECK['loggedIn'] = true;
    $STATUS_CHECK['user'] = $current;

    $userId = intval($current['id'] ?? 0);
    if ($userId > 0) {
        $url = rtrim($apiBase, '/') . '/users/' . $userId;

        // Use cURL to call the API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        // Accept JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $code >= 400) {
            $STATUS_CHECK['api_user'] = null;
            $STATUS_CHECK['error'] = $err ?: "API returned HTTP $code";
        } else {
            $decoded = json_decode($resp, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $STATUS_CHECK['api_user'] = $decoded;

                // Optionally sync back important fields into PHP session (name, email, funcao_id)
                $synced = [
                    'id' => $decoded['utilizador_id'] ?? $userId,
                    'name' => $decoded['nome_utilizador'] ?? ($current['name'] ?? null),
                    'email' => $decoded['email'] ?? ($current['email'] ?? null),
                    'funcao_id' => $decoded['funcao_id'] ?? ($current['funcao_id'] ?? null)
                ];

                // Update session cookie so PHP pages can rely on fresh funcao_id
                setUserSession($synced);
                // Also update the STATUS_CHECK user payload
                $STATUS_CHECK['user'] = $synced;
            } else {
                $STATUS_CHECK['api_user'] = null;
                $STATUS_CHECK['error'] = 'Invalid JSON from API';
            }
        }
    }
}

/**
 * Helper: enforce role or redirect.
 * Usage: require_funcao_or_redirect(1, 'login.php');
 * Accepts single int or array of allowed funcao_id values.
 */
function require_funcao_or_redirect($allowed, $redirectUrl = 'login.php') {
    global $STATUS_CHECK;

    if (!is_array($allowed)) $allowed = [$allowed];

    if (empty($STATUS_CHECK['loggedIn']) || empty($STATUS_CHECK['user'])) {
        header('Location: ' . $redirectUrl);
        exit();
    }

    $funcao = intval($STATUS_CHECK['user']['funcao_id'] ?? 0);
    if (!in_array($funcao, $allowed, true)) {
        // Redirect everyone to the canonical profile page
        header('Location: perfil.php');
        exit();
    }
}

// End of status_check.php
