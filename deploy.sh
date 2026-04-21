#!/usr/bin/env bash
set -e

echo "==> Migration base de données..."
php database/migrate.php

echo "==> Démarrage du serveur sur le port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} -t public/
