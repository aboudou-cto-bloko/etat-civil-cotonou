#!/usr/bin/env bash

echo "==> PORT utilisé : ${PORT:-8080}"

echo "==> Attente que MySQL soit prêt..."
MAX=60
COUNT=0
until php -r "
  \$h = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
  \$p = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
  \$u = getenv('MYSQLUSER') ?: getenv('DB_USERNAME') ?: 'root';
  \$pw = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
  \$db = getenv('MYSQLDATABASE') ?: getenv('DB_DATABASE') ?: '';
  new PDO(\"mysql:host={\$h};port={\$p};dbname={\$db};charset=utf8mb4\", \$u, \$pw);
  echo 'ok';
" 2>/dev/null | grep -q ok; do
  COUNT=$((COUNT + 1))
  if [ $COUNT -ge $MAX ]; then
    echo "⚠ MySQL non disponible après ${MAX}s — on continue quand même."
    break
  fi
  echo "  MySQL pas encore prêt (${COUNT}/${MAX})..."
  sleep 1
done

echo "==> Migration base de données..."
php database/migrate.php || echo "⚠ Migration échouée ou déjà appliquée, on continue."

echo "==> Démarrage du serveur sur le port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public/
