#!/bin/sh
# Startup script for PHP server on Railway
# This ensures PORT environment variable is properly expanded
# Use index.php as router to handle all requests including direct file paths
PORT=${PORT:-8080}
exec php -S 0.0.0.0:$PORT -t . index.php
