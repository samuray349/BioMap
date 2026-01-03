#!/bin/sh
# Startup script for PHP server on Railway
# This ensures PORT environment variable is properly expanded
PORT=${PORT:-8080}
exec php -S 0.0.0.0:$PORT -t .
