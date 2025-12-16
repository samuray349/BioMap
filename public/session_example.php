<?php
/**
 * Example: How to use sessions in both PHP and JavaScript
 */

require_once 'session_helper.php';

// Example: Setting session (typically done after login)
// setUserSession([
//     'id' => 1,
//     'name' => 'John Doe',
//     'email' => 'john@example.com',
//     'funcao_id' => 1
// ]);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Session Example - PHP + JavaScript</title>
    <script src="js/session.js"></script>
</head>
<body>
    <h1>Session Example</h1>
    
    <h2>PHP Side:</h2>
    <?php if (isUserLoggedIn()): ?>
        <?php $user = getCurrentUser(); ?>
        <p><strong>User is logged in (PHP)!</strong></p>
        <ul>
            <li>ID: <?php echo htmlspecialchars($user['id'] ?? 'N/A'); ?></li>
            <li>Name: <?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></li>
            <li>Email: <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></li>
            <li>Role ID: <?php echo htmlspecialchars($user['funcao_id'] ?? 'N/A'); ?></li>
            <li>Is Admin: <?php echo isAdmin() ? 'Yes' : 'No'; ?></li>
        </ul>
    <?php else: ?>
        <p>User is not logged in (PHP).</p>
    <?php endif; ?>
    
    <h2>JavaScript Side:</h2>
    <div id="js-user-info">Loading...</div>
    
    <script>
        // Method 1: Read from cookie directly
        const user = SessionHelper.getCurrentUser();
        if (user) {
            document.getElementById('js-user-info').innerHTML = `
                <p><strong>User is logged in (JavaScript from Cookie)!</strong></p>
                <ul>
                    <li>ID: ${user.id || 'N/A'}</li>
                    <li>Name: ${user.name || 'N/A'}</li>
                    <li>Email: ${user.email || 'N/A'}</li>
                    <li>Role ID: ${user.funcao_id || 'N/A'}</li>
                    <li>Is Admin: ${SessionHelper.isAdmin() ? 'Yes' : 'No'}</li>
                </ul>
            `;
        } else {
            document.getElementById('js-user-info').innerHTML = '<p>User is not logged in (JavaScript).</p>';
        }
        
        // Method 2: Fetch from PHP endpoint (alternative approach)
        // fetch('get_session.php')
        //     .then(r => r.json())
        //     .then(data => {
        //         if (data.loggedIn) {
        //             console.log('User from API:', data.user);
        //         }
        //     });
    </script>
</body>
</html>
