# État Civil — Mairie de Cotonou

Système de gestion numérique de l'état civil pour la Mairie de Cotonou (Bénin).  
Couvre les actes de **naissance**, **mariage** et **décès** sur les 13 arrondissements, conformément à l'Ordonnance n°69-23 du 10 juillet 1969 et à la Loi n°2002-07.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Langage | PHP 8.1 (natif, sans framework) |
| Base de données | MySQL 8+ / MariaDB 10.6+ |
| PDF | Dompdf 2.x |
| Validation | Respect/Validation 2.x |
| Logs | Monolog 3.x |
| Front | CSS custom (design system Sanity-inspired), vanilla JS |

---

## Architecture

```
etat-civil-cotonou/
├── app/
│   ├── Controllers/          # Un contrôleur par domaine métier
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── NaissanceController.php
│   │   ├── MariageController.php
│   │   ├── DecesController.php
│   │   ├── StatistiqueController.php
│   │   ├── UserController.php
│   │   └── DocumentController.php
│   ├── Core/                 # Noyau MVC maison
│   │   ├── Controller.php    # Classe de base (render, redirect, flash, abort)
│   │   ├── Database.php      # Singleton PDO
│   │   ├── Model.php         # Base CRUD + pagination
│   │   ├── Request.php       # Wrapper $_GET / $_POST / attributs
│   │   ├── Router.php        # Routeur avec pipeline de middlewares
│   │   └── View.php          # Moteur de templates PHP (extract + output buffering)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php               # Vérifie la session
│   │   ├── ArrondissementIsolationMiddleware.php  # Injecte l'arrondissement de l'agent
│   │   ├── CsrfMiddleware.php               # Vérifie le token CSRF
│   │   └── RoleMiddleware.php               # Contrôle d'accès par rôle
│   ├── Models/
│   │   ├── Naissance.php
│   │   ├── Mariage.php
│   │   ├── Deces.php
│   │   ├── User.php
│   │   └── AuditLog.php
│   ├── Views/
│   │   ├── layouts/          # app.php (interface principale), auth.php (login)
│   │   ├── actes/
│   │   │   ├── naissances/   # index, form, show
│   │   │   ├── mariages/     # index, form, show
│   │   │   ├── deces/        # index, form, show
│   │   │   └── pdf/          # Templates PDF officiels (Dompdf)
│   │   ├── auth/             # login.php
│   │   ├── dashboard/        # Tableau de bord avec graphiques
│   │   ├── statistiques/     # Rapports filtrables
│   │   ├── users/            # index, form, profile
│   │   └── errors/           # 403, 404
│   └── routes.php            # Registre centralisé de toutes les routes
├── config/
│   └── routes.php            # Déclaration des routes et groupes de middleware
├── database/
│   ├── schema.sql            # 15 tables MySQL (InnoDB, UTF8MB4, UUID CHAR(36))
│   ├── seeds.sql             # Arrondissements, rôles, permissions, admin par défaut
│   └── migrate.php           # Script d'exécution du schéma + seeds
├── public/
│   ├── index.php             # Point d'entrée unique
│   └── assets/
│       ├── css/app.css       # Design system complet (~700 lignes)
│       └── js/app.js
├── storage/
│   ├── logs/                 # Logs applicatifs (Monolog)
│   └── pdf/                  # PDFs générés (gitignorés)
├── .env.example
└── composer.json
```

---

## Modèle de données

15 tables principales :

| Table | Description |
|---|---|
| `arrondissements` | 13 arrondissements de Cotonou |
| `roles` | admin, superviseur, analytics |
| `permissions` / `role_permissions` | Matrice de droits |
| `users` | Agents avec isolation par arrondissement |
| `naissances` | Actes de naissance (Ordonnance 69-23) |
| `mariages` | Actes de mariage (monogamique et polygamique) |
| `mariage_epouses_supplementaires` | Épouses additionnelles (polygamie) |
| `deces` | Actes de décès |
| `temoins` | Témoins polymorphiques (naissances, mariages, décès) |
| `audit_log` | Journal immuable avec snapshots JSON avant/après |
| `statistiques_cache` | Cache précalculé pour les tableaux de bord |

Clés primaires UUID v4 générées applicativement (compatibilité MySQL 5.7+).  
Numérotation des actes séquentielle par arrondissement et par année — unicité garantie par contrainte composite `(arrondissement_id, numero_acte, annee)`.

---

## Rôles et isolation multi-arrondissement

| Rôle | Périmètre données | Accès actes | Statistiques | Utilisateurs |
|---|---|---|---|---|
| `admin` | Tous les arrondissements | Lecture + écriture | Tous | Gestion complète |
| `superviseur` | Son arrondissement uniquement | Lecture + écriture | Son arrondissement | — |
| `analytics` | Son arrondissement uniquement | — | Son arrondissement | — |

L'isolation est appliquée par `ArrondissementIsolationMiddleware` : les agents dont `arrondissement_id IS NOT NULL` ne peuvent jamais accéder aux données d'un autre arrondissement, que ce soit via l'interface ou l'URL.

---

## Installation

### Prérequis

- PHP 8.1+ avec extensions : `pdo_mysql`, `mbstring`, `fileinfo`, `gd`
- MySQL 8+ ou MariaDB 10.6+
- Composer

### 1. Cloner et installer les dépendances

```bash
git clone https://github.com/aboudou-cto-bloko/etat-civil-cotonou.git
cd etat-civil-cotonou
composer install
```

### 2. Configurer l'environnement

```bash
cp .env.example .env
```

Éditer `.env` :

```dotenv
APP_ENV=production
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=etat_civil_cotonou
DB_USER=root
DB_PASS=secret

MAIRIE_NOM=Mairie de Cotonou
```

### 3. Créer la base de données et exécuter les migrations

```bash
mysql -u root -p -e "CREATE DATABASE etat_civil_cotonou CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php database/migrate.php
```

Le script exécute `schema.sql` puis `seeds.sql` (arrondissements, rôles, compte admin par défaut).

### 4. Démarrer le serveur de développement

```bash
composer dev
# ou directement :
php -S localhost:8000 -t public/
```

### Compte admin par défaut

| Champ | Valeur |
|---|---|
| Email | `admin@etatcivil-cotonou.bj` |
| Mot de passe | `Admin@Cotonou2026` |

> Changer le mot de passe à la première connexion via `/profil`.

---

## Déploiement Clever Cloud

### Prérequis

```bash
npm install -g clever-tools
clever login
```

### 1. Créer l'application et l'addon MySQL

```bash
cd etat-civil-cotonou
clever create --type php etat-civil-cotonou
clever addon create mysql-addon --plan dev --link etat-civil-cotonou --region par mysql-etat-civil
```

### 2. Configurer les variables d'environnement

```bash
clever env set APP_ENV production
clever env set APP_NAME "Etat Civil Cotonou"
clever env set APP_URL "https://<votre-url>.cleverapps.io"
clever env set SESSION_LIFETIME 7200
clever env set SESSION_SECURE true
clever env set LOG_LEVEL warning
clever env set LOG_PATH storage/logs/app.log
clever env set PDF_STORAGE_PATH storage/pdf/
clever env set MAIRIE_NOM "Mairie de Cotonou"
clever env set MAIRIE_LOGO public/assets/images/logo-mairie.png
clever env set CC_PRE_RUN_HOOK "php database/migrate.php"
```

Les variables `MYSQL_ADDON_*` sont injectées automatiquement par Clever Cloud via l'addon.

### 3. Déployer

```bash
clever deploy
```

La migration de la base de données s'exécute automatiquement avant le démarrage via `CC_PRE_RUN_HOOK`.

### Redéployer après un changement

```bash
git push origin main   # ou
clever deploy
```

---

## Configuration Nginx (production auto-hébergée)

```nginx
server {
    listen 80;
    server_name etatcivil.mairie-cotonou.bj;
    root /var/www/etat-civil-cotonou/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(css|js|png|jpg|ico|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\. { deny all; }
}
```

---

## Conformité légale

- **Ordonnance n°69-23 du 10 juillet 1969** — champs obligatoires pour chaque type d'acte
- **Loi n°2002-07** — modalités de l'état civil en République du Bénin
- Déclaration de décès dans les **24h** (rappel affiché dans le formulaire)
- Support du **mariage polygamique** (table `mariage_epouses_supplementaires`)
- Actes **jamais supprimés physiquement** — statut `ACTIF / ANNULÉ / RECTIFIÉ` + chaîne de rectification via `acte_source_id`
- Journal d'audit **append-only** avec snapshot JSON avant/après chaque modification
