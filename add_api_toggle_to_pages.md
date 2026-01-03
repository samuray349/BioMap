# Adding API Toggle to All Pages

The API toggle has been added to the header template in `public/js/script.js`.

To ensure it works on all pages, you need to add the `api-toggle.js` script to pages that:
1. Include `js/config.js` (required for API_PROVIDER)
2. Have the header placeholder (`<div id="header-placeholder"></div>`)

## Files that need the script

Add this line AFTER `js/config.js` and BEFORE `js/script.js`:
```html
<script src="js/api-toggle.js"></script>
```

Pages that likely need it:
- index.php ✅ (already added)
- login.php
- sign_up.php
- animais.php
- sobre_nos.php
- doar.php
- perfil.php
- admin_animal.php
- admin_util.php
- editar_perfil.php
- adicionar_animal.php
- adicionar_fundacao.php
- animal_desc.php
- etc.

## Pattern to follow

```html
<script src="js/config.js"></script>
<script src="js/api-toggle.js"></script>  <!-- Add this line -->
<script src="js/session.js"></script>
<script src="js/script.js"></script>
```

The toggle will automatically work once:
1. The script is included
2. The header is loaded (which happens in script.js)
3. The toggle initialization runs
