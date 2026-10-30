#!/bin/bash
set -e
echo "Checking PHP syntax..."
php -l public/index.php
echo "Build verification complete."
