# État Civil — Mairie de Cotonou

Système de gestion numérique de l'état civil pour la Mairie de Cotonou (Bénin).  
Couvre les actes de **naissance**, **mariage** et **décès** sur les 13 arrondissements, conformément à l'Ordonnance n°69-23 du 10 juillet 1969 et à la Loi n°2002-07.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Langage | PHP 8.2 (natif, sans framework) |
| Base de données | MySQL 8+ / MariaDB 10.6+ |
| PDF | Dompdf 2.x |
| Validation | Respect/Validation 2.x |
| Logs | Monolog 3.x |
| Conteneur | Docker (déploiement) |
| Front | CSS custom, vanilla JS |

---

## Architecture applicative

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
│   │   ├── AuthMiddleware.php
│   │   ├── ArrondissementIsolationMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Models/
│   │   ├── Naissance.php
│   │   ├── Mariage.php
│   │   ├── Deces.php
│   │   ├── User.php
│   │   └── AuditLog.php
│   └── Views/
│       ├── layouts/          # app.php, auth.php
│       ├── actes/            # naissances/, mariages/, deces/, pdf/
│       ├── auth/
│       ├── dashboard/
│       ├── statistiques/
│       ├── users/
│       └── errors/
├── config/
│   └── routes.php
├── database/
│   ├── schema.sql
│   ├── seeds.sql
│   └── migrate.php
├── public/
│   ├── index.php
│   └── assets/
├── storage/
│   ├── logs/
│   └── pdf/
├── Dockerfile
└── .env.example
```

---

## Modèle de données

### Tableau des 15 tables

| Table | Rôle | Clé primaire |
|---|---|---|
| `arrondissements` | 13 arrondissements de Cotonou | int |
| `roles` | ADMIN / SUPERVISEUR / ANALYTICS | int |
| `permissions` | Droits granulaires | int |
| `role_permissions` | Association rôles ↔ droits | composite |
| `users` | Agents de l'état civil | uuid |
| `sessions` | Jetons de session | uuid |
| `personnes` | Référentiel partagé de personnes physiques | uuid |
| `naissances` | Actes de naissance | uuid |
| `mariages` | Actes de mariage | uuid |
| `mariage_epouses_supplementaires` | Épouses supplémentaires (polygamie) | uuid |
| `deces` | Actes de décès | uuid |
| `temoins` | Témoins des 3 types d'actes | uuid |
| `documents_generes` | Traçabilité des PDF officiels | uuid |
| `audit_logs` | Journal d'audit immuable | uuid |
| `statistiques_periodiques` | Cache tableaux de bord | uuid |

### Décisions d'architecture

**UUID comme clé primaire (tables transactionnelles)**  
Les IDs séquentiels sont prédictibles — un utilisateur malveillant peut deviner l'ID d'un acte et tenter d'y accéder via l'URL. L'UUID v4 évite toute fuite d'information sur le volume de données. Les tables de référentiel stables (`arrondissements`, `roles`, `permissions`) conservent des `int` auto-incrémentés car elles sont petites et non exposées directement.

**Table `personnes` mutualisée**  
Centralise les informations sur toute personne physique (père, mère, époux, défunt, déclarant, témoin). Sans cette table, les données d'un même individu seraient dupliquées dans plusieurs tables d'actes avec incohérence garantie. Les champs dupliqués (`pere_nom`, `mere_nom`…) sont conservés dans les tables d'actes pour le cas où la personne n'est pas encore enregistrée (père inconnu, personne décédée avant la numérisation).

**Numérotation des actes : contrainte unique composite**  
```sql
UNIQUE(arrondissement_id, numero_acte, annee)
```
Conformément à la pratique béninoise, les actes sont numérotés séquentiellement par registre annuel et par arrondissement. L'acte n°47 de 2024 de l'arrondissement 3 est distinct du n°47 de 2024 de l'arrondissement 7.

**Soft delete et chaîne de rectification**  
Les actes ne sont jamais supprimés physiquement. Ils passent par des statuts : `ACTIF`, `RECTIFIÉ`, `ANNULÉ`, `DISSOUS`. La colonne `acte_source_id` (auto-référentielle) chaîne l'historique des rectifications sans perdre la valeur probante de l'acte original.

**Table `mariage_epouses_supplementaires`**  
Le droit béninois (Art. 143, Loi n°2002-07) reconnaît le mariage polygamique. Plutôt que des colonnes `epouse2_*`, `epouse3_*` (NULL à 95%), une table dédiée stocke les épouses supplémentaires avec leur ordre.

**Témoins polymorphiques**  
La table `temoins` utilise un couple `(type_acte, acte_id)` plutôt que trois FK `naissance_id`, `mariage_id`, `deces_id`. Évite 2 FK NULL sur 3 pour chaque témoin et permet une requête unique sur tous les actes d'un témoin.

**Audit log append-only**  
Capture chaque action avec un snapshot JSON de l'état avant/après. Aucun UPDATE ni DELETE autorisé. Alimente les statistiques par utilisateur et par période.

**Statistiques précalculées**  
`statistiques_periodiques` est un cache de tableaux de bord. Les agrégations en temps réel sur de grands volumes seraient lentes — un job CRON recalcule quotidiennement/hebdomadairement/mensuellement. `arrondissement_id = NULL` représente l'agrégat global mairie.

---

## Rôles et isolation multi-arrondissement

| Rôle | Périmètre | Actes | Statistiques | Utilisateurs |
|---|---|---|---|---|
| `admin` | Tous les arrondissements | Lecture + écriture | Tous | Gestion complète |
| `superviseur` | Son arrondissement uniquement | Lecture + écriture | Son arrondissement | — |
| `analytics` | Son arrondissement uniquement | — | Son arrondissement | — |

L'isolation est enforced par `ArrondissementIsolationMiddleware` : les agents dont `arrondissement_id IS NOT NULL` ne peuvent jamais accéder aux données d'un autre arrondissement, via l'interface ou directement par URL. Un admin a `arrondissement_id = NULL` dans `users`, ce qui signifie accès global.

---

## Vues et flux par rôle

### Authentification (tous rôles)

| Vue | Description |
|---|---|
| **Login** | Formulaire email / mot de passe. Redirection vers le tableau de bord propre au rôle après authentification. |

---

### Administrateur — Mairie centrale

Accès complet à tous les arrondissements, gestion des utilisateurs et paramétrage global.

| Vue | Fonctionnalités |
|---|---|
| **Tableau de bord** | Synthèse globale : total actes par type, top arrondissements, graphique d'évolution sur période sélectionnable. |
| **Liste des actes** | Filtrage par arrondissement, période, statut. Export CSV. Actions : voir, éditer, générer PDF. |
| **Formulaire enregistrement / modification** | Saisie de tous les champs obligatoires. Sélection de l'arrondissement. Modification tracée dans l'audit. |
| **Détail d'un acte** | Affichage complet + bouton **Générer le PDF officiel** + bouton Modifier. |
| **Statistiques détaillées** | Tableaux croisés par arrondissement, utilisateur, période. Export PDF/Excel. |
| **Gestion des utilisateurs** | Liste filtrée par rôle et arrondissement. Création / modification / désactivation de compte. |

---

### Superviseur — Responsable arrondissement

Lecture et écriture **uniquement dans son arrondissement**.

| Vue | Fonctionnalités |
|---|---|
| **Tableau de bord arrondissement** | Compteurs par type, évolution mensuelle, derniers actes enregistrés. |
| **Liste des actes** | Identique admin, limité à l'arrondissement. |
| **Formulaire enregistrement / modification** | Arrondissement pré-rempli et non modifiable. |
| **Détail d'un acte** | Visualisation complète + **Générer PDF** + **Modifier**. |
| **Statistiques arrondissement** | Graphiques et totaux propres à l'arrondissement. Export possible. |

---

### Analytics — Observateur statistique

Lecture seule des statistiques agrégées. Aucun accès aux données nominatives.

| Vue | Fonctionnalités |
|---|---|
| **Tableau de bord statistique** | Compteurs totaux, répartition naissances / mariages / décès, évolution temporelle. |
| **Rapports statistiques** | Filtrage par période. Export PDF ou Excel. |

---

### Flux principaux

**Enregistrement d'une naissance (Superviseur)**
1. Connexion → Tableau de bord arrondissement
2. Clic **"Nouvelle naissance"**
3. Saisie du formulaire (noms, date, lieu, parents, déclarant)
4. Validation → Vue **Détail de l'acte créé**
5. Option : **"Générer l'acte en PDF"** → Téléchargement

**Modification d'un acte (Admin ou Superviseur)**
1. Recherche par nom ou numéro d'acte
2. Clic **"Modifier"** → formulaire pré-rempli
3. Correction → Enregistrement avec trace d'audit
4. Option : génération d'un nouveau PDF mis à jour

**Consultation statistiques (Analytics)**
1. Connexion → Tableau de bord statistique
2. Sélection d'une période
3. Visualisation des agrégats
4. Export si nécessaire

---

## Infrastructure de déploiement

### Architecture actuelle (démonstration)

```
[Navigateurs — 13 arrondissements]
           │ HTTPS
           ▼
    [Render — Conteneur Docker]
     PHP 8.2 · MVC · Dompdf
           │ TCP 3306
           ▼
  [Railway — MySQL managé]
   Base de données cloud
```

L'application est conteneurisée via Docker et déployée sur **Render** (plateforme cloud). La base de données MySQL est hébergée sur **Railway** et accessible via une connexion TCP chiffrée. Chaque push sur `main` déclenche un redéploiement automatique.

Cette architecture est identique dans ses principes à un hébergement VPS classique — seule la gestion de l'infrastructure est déléguée aux plateformes cloud.

### Architecture cible (production — Mairie de Cotonou)

Pour un déploiement souverain et conforme aux exigences légales béninoises, l'architecture recommandée est un **VPS hébergé au Bénin** (Afriregister, CloudStore Africa) :

```
[Navigateurs — 13 arrondissements]
           │ HTTPS (443) · Fibre / 4G secours
           ▼
    [Internet — Réseau béninois]
           │
           ▼
  [Pare-feu WAF · Reverse Proxy Nginx]
   Terminaison TLS · Rate limiting · Filtrage IP
           │
           ▼
  [Serveur Applicatif — Ubuntu 22.04 LTS]
   Nginx · PHP 8.2-FPM · Application MVC
           │ TCP 3306 (réseau privé)
           ▼
  [Base de données — MySQL 8.0]
           │
           ▼
  [Sauvegarde externalisée — Cloud ou NAS distant]
   Dumps SQL quotidiens · Actes PDF · Archives
```

**Configuration minimale recommandée :**
- CPU : 4 cœurs
- RAM : 8 Go
- Stockage : 200 Go SSD
- OS : Ubuntu Server 22.04 LTS
- Stack : Nginx + PHP 8.2-FPM + MySQL 8.0

**Sécurité :**
- HTTPS via certificat SSL/TLS (Let's Encrypt)
- Pare-feu applicatif (WAF) — filtrage IP, rate limiting
- Mots de passe hachés (bcrypt, coût 12)
- Requêtes SQL préparées (PDO)
- Protection CSRF sur tous les formulaires
- Sauvegardes quotidiennes externalisées

**Conformité légale :**
- Loi n°2008-12 du 25 janvier 2008 — protection des données à caractère personnel (Bénin)
- Hébergement au Bénin pour souveraineté des données et latence minimale

---

## Installation locale

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

Éditer `.env` avec les paramètres de la base de données locale :

```dotenv
APP_ENV=development
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=etat_civil_cotonou
DB_USERNAME=root
DB_PASSWORD=secret
```

### 3. Créer la base de données et migrer

```bash
mysql -u root -p -e "CREATE DATABASE etat_civil_cotonou CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php database/migrate.php
```

### 4. Démarrer le serveur de développement

```bash
php -S localhost:8000 -t public/
```

### Compte admin par défaut

| Champ | Valeur |
|---|---|
| Email | `admin@etatcivil-cotonou.bj` |
| Mot de passe | `Admin@Cotonou2026` |

> Changer le mot de passe immédiatement après la première connexion via `/profil`.

---

## Déploiement Render + Railway MySQL

L'application inclut un `Dockerfile` prêt à l'emploi.

### 1. Base de données Railway

1. Créer un projet sur [railway.app](https://railway.app)
2. Ajouter le plugin **MySQL**
3. Récupérer les variables de connexion dans l'onglet **Connect**

### 2. Application Render

1. Connecter GitHub sur [render.com](https://render.com)
2. **New → Web Service** → sélectionner le repo
3. Render détecte le `Dockerfile` automatiquement

### 3. Variables d'environnement (onglet Environment de Render)

```
APP_ENV=production
APP_NAME=Etat Civil Cotonou
APP_URL=https://<votre-url>.onrender.com
SESSION_LIFETIME=7200
SESSION_SECURE=true
LOG_LEVEL=warning
LOG_PATH=storage/logs/app.log
PDF_STORAGE_PATH=storage/pdf/
MAIRIE_NOM=Mairie de Cotonou
MAIRIE_LOGO=public/assets/images/logo-mairie.png
MYSQLHOST=<host Railway>
MYSQLPORT=<port Railway>
MYSQLDATABASE=<database Railway>
MYSQLUSER=<user Railway>
MYSQLPASSWORD=<password Railway>
```

### 4. Déployer

Cliquer **Deploy Web Service**. La migration s'exécute automatiquement au démarrage. Chaque push sur `main` redéploie automatiquement.

---

## Configuration Nginx (VPS auto-hébergé)

```nginx
server {
    listen 443 ssl;
    server_name etatcivil.mairie-cotonou.bj;
    root /var/www/etat-civil-cotonou/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/etatcivil.mairie-cotonou.bj/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/etatcivil.mairie-cotonou.bj/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
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

server {
    listen 80;
    server_name etatcivil.mairie-cotonou.bj;
    return 301 https://$host$request_uri;
}
```

---

## Conformité légale

- **Ordonnance n°69-23 du 10 juillet 1969** — champs obligatoires pour chaque type d'acte
- **Loi n°2002-07 du 24 août 2004** — Code des personnes et de la famille (Bénin)
- **Décret n°73-292 du 20 août 1973** — modalités d'application de l'Ordonnance 69-23
- **Loi n°2008-12 du 25 janvier 2008** — protection des données à caractère personnel (Bénin)
- Déclaration de décès dans les **24h** (rappel affiché dans le formulaire)
- Support du **mariage polygamique** (table `mariage_epouses_supplementaires`)
- Actes **jamais supprimés physiquement** — statut `ACTIF / ANNULÉ / RECTIFIÉ / DISSOUS`
- Journal d'audit **append-only** avec snapshot JSON avant/après chaque modification
- Documents PDF tracés avec hash SHA-256 et numéro d'expédition officiel
