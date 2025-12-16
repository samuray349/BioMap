<?php
/**
 * Example: How to read user session data in PHP
 */

// Include the session helper
require_once 'session_helper.php';

// Start the session (automatically handled by helper functions)
startSessionIfNotStarted();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Session Example</title>
</head>
<body>
    <h1>User Session Data</h1>
    
    <?php if (isUserLoggedIn()): ?>
        <?php $user = getCurrentUser(); ?>
        <p><strong>User is logged in!</strong></p>
        <ul>
            <li>ID: <?php echo htmlspecialchars($user['id'] ?? 'N/A'); ?></li>
            <li>Name: <?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></li>
            <li>Email: <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></li>
            <li>Role ID: <?php echo htmlspecialchars($user['funcao_id'] ?? 'N/A'); ?></li>
            <li>Is Admin: <?php echo isAdmin() ? 'Yes' : 'No'; ?></li>
        </ul>
        
        <p>User ID (from helper): <?php echo getUserId(); ?></p>
        <p>User Name (from helper): <?php echo getUserName(); ?></p>
        
        <!-- You can also access session directly -->
        <h2>Direct Session Access:</h2>
        <pre><?php print_r($_SESSION['biomap_user'] ?? 'No session data'); ?></pre>
        
    <?php else: ?>
        <p>User is not logged in.</p>
    <?php endif; ?>
</body>
</html>
