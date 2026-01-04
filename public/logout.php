<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BioMap - Terminar Sessão</title>
  <script src="js/session.js?v=<?php echo time(); ?>"></script>
</head>
<body>
  <script>
    // Clear stored session info (localStorage and cookie) and redirect to login
    localStorage.removeItem('biomapUser');
    
    // Clear cookie session
    if (typeof SessionHelper !== 'undefined') {
        SessionHelper.clearUser();
    } else {
        // Fallback: clear cookie manually
        document.cookie = 'biomap_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    }
    
    // Also clear PHP session via server-side logout
    fetch('logout_server.php', { method: 'POST' })
        .catch(() => {}) // Ignore errors if endpoint doesn't exist
        .finally(() => {
            window.location.replace('login.php');
        });
  </script>
</body>
</html>

