# État Civil — Mairie de Cotonou

Outil de gestion numérique des actes d'état civil (naissances, mariages, décès) pour les 13 arrondissements de Cotonou.

---

## Stack

| | |
|---|---|
| Backend | PHP 8.2 natif (MVC maison, PDO) |
| Base de données | MySQL 8+ |
| PDF | Dompdf 2.x |
| Front | CSS custom, vanilla JS |
| Déploiement | Docker (Render) + MySQL managé (Railway) |

---

## Lancer en local

**Prérequis :** PHP 8.1+, MySQL 8+, Composer

```bash
git clone https://github.com/aboudou-cto-bloko/etat-civil-cotonou.git
cd etat-civil-cotonou
composer install
cp .env.example .env
# éditer .env avec tes paramètres MySQL
mysql -u root -p -e "CREATE DATABASE etat_civil_cotonou CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php database/migrate.php
php -S localhost:8000 -t public/
```

Compte admin par défaut :  
`admin@etatcivil-cotonou.bj` / `Admin@Cotonou2026`

---

## Déploiement (Render + Railway MySQL)

1. Créer un projet sur Railway → ajouter le plugin **MySQL** → copier les variables de connexion
2. Sur Render → **New Web Service** → connecter le repo GitHub → Render détecte le `Dockerfile` automatiquement
3. Ajouter les variables d'environnement dans Render :

```env
APP_ENV=production
APP_URL=https://<ton-app>.onrender.com
SESSION_SECURE=true
MYSQLHOST=<Railway host>
MYSQLPORT=<Railway port>
MYSQLDATABASE=<Railway db>
MYSQLUSER=<Railway user>
MYSQLPASSWORD=<Railway password>
```

4. Cliquer **Deploy** — la migration tourne automatiquement au démarrage. Chaque push sur `main` redéploie.

---

## Rôles

| Rôle | Périmètre | Ce qu'il peut faire |
|---|---|---|
| `admin` | Tous les arrondissements | Tout — actes, stats, utilisateurs |
| `superviseur` | Son arrondissement | Enregistrer et modifier les actes |
| `analytics` | Son arrondissement | Statistiques en lecture seule |

L'isolation par arrondissement est appliquée côté serveur — impossible d'accéder aux données d'un autre arrondissement par URL.

---

## Base de données

15 tables — [voir le diagramme en ligne](https://dbdiagram.io/d/etat-civil-69e762e1d80a958d1c9a1d60)

![Schéma de la base de données](public/assets/docs/SCHEMA.png)

Points notables :
- UUID comme clé primaire sur les tables transactionnelles (actes, personnes, users)
- Actes **jamais supprimés** — statuts `ACTIF / RECTIFIÉ / ANNULÉ / DISSOUS`
- Table `personnes` mutualisée entre naissances, mariages et décès
- Support du mariage polygamique (table `mariage_epouses_supplementaires`, Art. 143 Loi n°2002-07)
- Audit log append-only avec snapshot JSON avant/après chaque modification

---

## Sécurité

- Mots de passe hachés bcrypt (coût 12)
- Protection CSRF sur tous les formulaires (dont la déconnexion)
- Limitation des tentatives de connexion : 5 essais max, blocage 5 min
- Détection d'IP via `TRUSTED_PROXIES` (pas de confiance aveugle au header `X-Forwarded-For`)
- Headers HTTP : `CSP`, `HSTS`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`
- Requêtes SQL préparées (PDO, zéro concaténation)
- Redirections limitées au même domaine (pas d'open redirect)
- Logs d'audit sur connexions, déconnexions et actions métier

---

## Architecture cible (production)

Pour un déploiement souverain, l'architecture recommandée est un VPS hébergé au Bénin :

```
[13 arrondissements]
        │ HTTPS
        ▼
[WAF · Reverse Proxy Nginx]
        │
        ▼
[Ubuntu 22.04 · PHP 8.2-FPM · MySQL 8.0]
        │
        ▼
[Sauvegardes externalisées]
```

![Architecture cible](public/assets/docs/architecture_etat_civil_cotonou.drawio.png)

Config minimale recommandée : 4 cœurs, 8 Go RAM, 200 Go SSD.
