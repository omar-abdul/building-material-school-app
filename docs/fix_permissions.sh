#!/bin/bash

echo "Fixing BMMS file permissions for Linux/Unix..."
echo

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run as root (use sudo)"
    echo "Usage: sudo ./fix_permissions.sh"
    exit 1
fi

echo "Running as root - proceeding with permission fixes..."
echo

# Detect web server user
WEB_USER=""
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
elif id "httpd" &>/dev/null; then
    WEB_USER="httpd"
else
    echo "Warning: Could not identify web server user"
    echo "Setting permissions to 666 (readable/writable by all)"
    chmod -R 666 config/
    exit 0
fi

echo "Detected web server user: $WEB_USER"
echo "Setting ownership and permissions..."

# Change ownership to web server user
chown -R $WEB_USER:$WEB_USER config/

# Set proper permissions
chmod -R 755 config/
chmod 644 config/*.php
chmod 644 config/*.js

echo "Permission fixes completed!"
echo "Config directory is now owned by $WEB_USER with proper permissions"
echo
