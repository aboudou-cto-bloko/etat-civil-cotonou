# Documentation de présentation — État Civil Cotonou

---

## 1. Contexte et problème résolu

La Mairie de Cotonou gère 13 arrondissements, chacun disposant de son propre bureau d'état civil. Aujourd'hui, les actes de naissance, de mariage et de décès sont enregistrés manuellement dans des registres papier. Cette situation entraîne plusieurs problèmes concrets :

- **Perte de données** : les registres papier se dégradent, se perdent ou sont détruits lors d'inondations ou d'incendies
- **Accès difficile** : retrouver un acte précis nécessite de fouiller physiquement des registres sur plusieurs années
- **Aucune vision globale** : la Mairie centrale n'a pas de vue d'ensemble sur les statistiques de l'état civil en temps réel
- **Risque d'erreur** : la retranscription manuelle favorise les erreurs et les incohérences entre registres

L'application État Civil Cotonou résout ces problèmes en numérisant l'ensemble du processus d'enregistrement, en garantissant l'intégrité des données et en offrant une interface de consultation centralisée.

---

## 2. Ce que fait l'application

### Les trois domaines métier

**Naissances**
Enregistrement complet de chaque naissance : identité de l'enfant, date, heure et lieu, informations sur le père et la mère, déclarant, témoins, officier d'état civil. Un numéro d'acte unique est attribué par arrondissement et par année. L'acte peut être généré en PDF officiel.

**Mariages**
Enregistrement des mariages monogamiques et polygamiques (conformément au droit béninois, Art. 143 Loi n°2002-07). Les mariages polygamiques disposent d'une table dédiée pour les épouses supplémentaires plutôt que de colonnes redondantes. Témoins de chaque époux, officier d'état civil, régime matrimonial.

**Décès**
Enregistrement des décès avec identité du défunt, cause du décès, déclarant, témoins. Contrainte légale béninoise : la déclaration doit intervenir dans les 24h — rappel affiché dans le formulaire.

### Tableau de bord

Vue synthétique selon le rôle de l'utilisateur :
- Compteurs annuels par type d'acte (naissances / mariages / décès) avec répartition homme/femme
- Graphique d'évolution mensuelle sur l'année en cours
- Journal d'activité récente (connexions, enregistrements, modifications)
- Accès rapides aux actions les plus fréquentes

### Statistiques

Tableaux croisés par période, par arrondissement et par type d'acte. Export des données.

### Gestion des utilisateurs (admin)

Création, modification et désactivation de comptes. Assignation du rôle et de l'arrondissement. Chaque compte est lié à un arrondissement précis, ce qui détermine automatiquement son périmètre d'action.

### Profil et mot de passe

Chaque agent peut modifier ses informations personnelles et changer son mot de passe depuis son espace profil.

---

## 3. Architecture technique

### Choix : PHP natif sans framework

L'application est construite en **PHP 8.2 pur**, sans Laravel, Symfony ou autre framework. Ce choix est intentionnel :

- **Maîtrise complète** : chaque composant du code est écrit et compris par l'équipe, sans boîte noire
- **Légèreté** : pas de dépendances lourdes à maintenir ou à mettre à jour
- **Pédagogie** : le code illustre les mécanismes fondamentaux (routing, MVC, middleware) sans abstraction qui les masque
- **Souveraineté** : un VPS béninois avec PHP de base suffit à faire tourner l'application, sans environnement exotique

### Pattern MVC

L'application suit le pattern **Modèle — Vue — Contrôleur** :

```
Requête HTTP
    │
    ▼
Router  →  vérifie la méthode et l'URL, applique les middlewares
    │
    ▼
Controller  →  reçoit la requête, appelle le modèle, passe les données à la vue
    │
    ▼
Model  →  requêtes SQL via PDO, retourne des tableaux associatifs
    │
    ▼
View  →  template PHP qui génère le HTML final
    │
    ▼
Réponse HTTP
```

### Le Router

Le router maison (`app/Core/Router.php`) gère les méthodes GET, POST, PUT, DELETE. Il supporte :
- Les paramètres dans l'URL : `/naissances/{id}`
- Les **groupes de routes** avec middlewares communs (toutes les routes protégées partagent `AuthMiddleware` + `ArrondissementIsolationMiddleware`)
- Le **method override** via le champ `_method` en POST (pour simuler PUT/DELETE depuis les formulaires HTML)

Exemple de définition de route :
```php
$router->post('/naissances/{id}/modifier', [NaissanceController::class, 'update'],
    [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);
```

### Les Middlewares

Les middlewares s'exécutent en pipeline avant d'atteindre le contrôleur. On en compte 4 :

| Middleware | Rôle |
|---|---|
| `AuthMiddleware` | Vérifie qu'une session utilisateur existe, redirige vers /login sinon |
| `ArrondissementIsolationMiddleware` | Injecte l'arrondissement de l'agent dans la requête pour limiter son périmètre |
| `RoleMiddleware` | Vérifie que le rôle de l'agent est autorisé pour cette route |
| `CsrfMiddleware` | Valide le token CSRF sur toutes les requêtes POST, retourne 419 si invalide |

### Les Modèles

Chaque modèle hérite d'une classe de base `Model` qui expose les opérations CRUD communes. Les requêtes SQL sont toutes préparées via PDO — aucune concaténation de variable directement dans le SQL.

Les modèles métier : `Naissance`, `Mariage`, `Deces`, `User`, `AuditLog`.

### Le système de vues

Les vues sont des templates PHP simples. Le moteur (`app/Core/View.php`) fonctionne en deux temps :
1. Le template de la page est rendu dans un buffer (output buffering)
2. Le résultat est injecté dans le layout (`app.php` ou `auth.php`) via la variable `$content`

L'échappement XSS est assuré par `View::e()` — un alias de `htmlspecialchars()` — utilisé systématiquement dans toutes les vues.

---

## 4. Base de données

### Les 15 tables

| Table | Rôle |
|---|---|
| `arrondissements` | Les 13 arrondissements de Cotonou (référentiel stable) |
| `roles` | Trois rôles : `admin`, `superviseur`, `analytics` |
| `permissions` | Droits granulaires (ex. `acte.create`, `stats.view`) |
| `role_permissions` | Association many-to-many rôles ↔ permissions |
| `users` | Comptes des agents avec hash bcrypt du mot de passe |
| `sessions` | Tokens de session avec expiration |
| `personnes` | Référentiel mutualisé de personnes physiques |
| `naissances` | Actes de naissance |
| `mariages` | Actes de mariage |
| `mariage_epouses_supplementaires` | Épouses supplémentaires pour les mariages polygamiques |
| `deces` | Actes de décès |
| `temoins` | Témoins, reliés à n'importe quel type d'acte |
| `documents_generes` | Traçabilité des PDF produits (hash SHA-256, numéro d'expédition) |
| `audit_logs` | Journal d'audit immuable — toutes les actions |
| `statistiques_periodiques` | Cache des agrégats du tableau de bord |

### Décisions d'architecture de données

**UUID sur les tables transactionnelles**

Les tables `users`, `naissances`, `mariages`, `deces`, `personnes`, `audit_logs`... utilisent des UUID v4 comme clé primaire plutôt que des entiers auto-incrémentés.

Pourquoi ? Un ID séquentiel comme `42` est prédictible. Un utilisateur malveillant peut modifier l'URL `/naissances/42` en `/naissances/43` et tenter d'accéder à un acte qui ne lui appartient pas. Un UUID comme `a3f8c2d1-7e45-4b9f-...` est impossible à deviner. Les tables de référentiel stables (`arrondissements`, `roles`) conservent des entiers simples car elles ne sont jamais exposées directement par URL.

**Table `personnes` mutualisée**

Une même personne peut apparaître comme père d'une naissance ET comme déclarant d'un décès ET comme témoin d'un mariage. Sans table centrale, ses données seraient dupliquées avec des incohérences inévitables. La table `personnes` centralise les données de toute personne physique. Les tables d'actes conservent également des champs texte dupliqués (`pere_nom`, `mere_nom`...) pour les cas où la personne n'est pas encore enregistrée dans le système.

**Numérotation par contrainte unique composite**

```sql
UNIQUE(arrondissement_id, numero_acte, annee)
```

Conformément à la pratique béninoise, les actes sont numérotés séquentiellement par registre annuel et par arrondissement. L'acte n°47/2024 de l'arrondissement 3 est distinct du n°47/2024 de l'arrondissement 7. La contrainte est enforced directement en base — impossible d'enregistrer un doublon même en cas de bug applicatif.

**Soft delete et chaîne de rectification**

Les actes ne sont **jamais supprimés physiquement** de la base. Ils passent par des statuts :
- `ACTIF` — acte valide
- `RECTIFIÉ` — remplacé par un acte corrigé (lié par `acte_source_id`)
- `ANNULÉ` — acte invalidé
- `DISSOUS` — pour les mariages dissous

La colonne `acte_source_id` (auto-référentielle) chaîne l'historique des rectifications sans perdre la valeur probante de l'acte original. Cette exigence est posée par l'Ordonnance n°69-23 sur l'état civil.

**Témoins polymorphiques**

La table `temoins` utilise un couple `(type_acte, acte_id)` plutôt que trois colonnes `naissance_id`, `mariage_id`, `deces_id`. Avec l'approche naïve, deux des trois colonnes seraient NULL pour chaque ligne. L'approche polymorphique est plus propre et permet d'interroger tous les actes pour lesquels une personne a été témoin en une seule requête.

**Audit log append-only**

Chaque action sur l'application (connexion, création d'acte, modification, déconnexion) est enregistrée dans `audit_logs` avec un snapshot JSON de l'état avant/après. Aucun UPDATE ni DELETE n'est autorisé sur cette table. C'est la fondation de la traçabilité légale et de la lutte contre la fraude.

**Cache de statistiques**

`statistiques_periodiques` stocke des agrégats précalculés (totaux par mois, par arrondissement, par type). Calculer ces statistiques en temps réel sur des millions d'actes serait lent. Un job CRON recalcule ces agrégats périodiquement. `arrondissement_id = NULL` représente l'agrégat global pour la Mairie centrale.

---

## 5. Rôles et isolation multi-arrondissement

### Les trois rôles

| Rôle | Qui | Ce qu'il voit | Ce qu'il peut faire |
|---|---|---|---|
| `admin` | Administrateur Mairie centrale | Tous les arrondissements | Tout : actes, stats, gestion des comptes |
| `superviseur` | Responsable d'un arrondissement | Son arrondissement uniquement | Enregistrer et modifier les actes |
| `analytics` | Observateur statistique | Son arrondissement uniquement | Consulter les statistiques, aucun accès aux actes nominatifs |

### Comment l'isolation fonctionne

C'est le mécanisme le plus important côté sécurité. Voici comment il fonctionne en pratique :

1. À la connexion, l'`arrondissement_id` de l'agent est stocké dans la session
2. Sur chaque requête protégée, `ArrondissementIsolationMiddleware` s'exécute
3. Si le rôle est `superviseur` ou `analytics`, le middleware injecte l'`arrondissement_id` dans l'objet `Request`
4. Chaque modèle (`Naissance`, `Mariage`, `Deces`) reçoit cet `arrondissement_id` et l'ajoute automatiquement à la clause `WHERE` de toutes ses requêtes SQL

Résultat : **un superviseur ne peut jamais accéder aux données d'un autre arrondissement, même en modifiant l'URL à la main**. La contrainte est appliquée côté serveur dans le SQL, pas seulement dans l'interface.

Un admin a `arrondissement_id = NULL` en base, ce qui signifie : pas de filtre → accès global.

---

## 6. Sécurité

### Authentification

- Les mots de passe ne sont jamais stockés en clair. Ils sont hachés avec **bcrypt au coût 12** via `password_hash()`. Le coût 12 est le standard actuel — il rend une attaque par force brute sur la base de données extrêmement coûteuse en temps de calcul.
- La vérification se fait avec `password_verify()`, qui résiste aux attaques de comparaison temporelle.

### Protection contre le brute force

Après 5 tentatives de connexion échouées depuis la même IP, le compte est bloqué pendant 5 minutes. Le compteur est stocké en session. Les tentatives échouées sont loguées dans `audit_logs` avec l'email et l'IP.

```
5 échecs → blocage 5 min → compteur remis à zéro au login réussi
```

### Protection CSRF

Toutes les actions qui modifient des données (POST) sont protégées par un token CSRF. Le mécanisme :
1. Un token aléatoire de 64 caractères est généré et stocké en session lors du chargement d'un formulaire
2. Le token est injecté dans un champ caché du formulaire
3. À la soumission, `CsrfMiddleware` compare le token du formulaire avec celui de la session via `hash_equals()` (comparaison en temps constant, résistante aux timing attacks)
4. Le token est **renouvelé après chaque consommation** — impossible de rejouer une requête

La déconnexion est également protégée par CSRF : elle passe par un formulaire POST, pas un simple lien `<a>`.

### Protection contre l'injection SQL

Toutes les requêtes utilisent des **requêtes préparées PDO** avec des paramètres bindés. Aucune variable n'est jamais concaténée directement dans le SQL. L'émulation des requêtes préparées est désactivée (`PDO::ATTR_EMULATE_PREPARES => false`) pour utiliser les vraies requêtes préparées du serveur MySQL.

### Protection XSS

Toutes les données affichées dans les vues passent par `View::e()`, un alias de `htmlspecialchars()` en UTF-8. Les caractères dangereux (`<`, `>`, `"`, `'`, `&`) sont convertis en entités HTML avant affichage.

### Détection d'IP sécurisée

Le header `X-Forwarded-For` est souvent spoofé par des attaquants pour contourner le rate limiting. L'application ne l'utilise que si l'IP source est listée dans la variable `TRUSTED_PROXIES` — c'est-à-dire si elle correspond réellement à un reverse proxy connu et de confiance.

### Protection contre les redirections ouvertes

La méthode `Controller::redirect()` n'accepte que des URLs relatives (commençant par `/`) ou des URLs du même domaine que `APP_URL`. Toute tentative de redirection vers un domaine externe est annulée et redirigée vers `/dashboard`.

### Fixation de session

Après une connexion réussie, l'ID de session est régénéré (`session_regenerate_id(true)`). Cela empêche les attaques par fixation de session où un attaquant aurait prédéfini l'ID de session de la victime.

### Headers de sécurité HTTP

Envoyés sur chaque réponse :

| Header | Valeur | Protection |
|---|---|---|
| `X-Content-Type-Options` | `nosniff` | Empêche le navigateur de deviner le type MIME |
| `X-Frame-Options` | `DENY` | Empêche le clickjacking via iframe |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Contrôle les infos envoyées au referrer |
| `Strict-Transport-Security` | `max-age=31536000` (prod uniquement) | Force HTTPS pendant 1 an |
| `Content-Security-Policy` | Sources explicites par type | Bloque les injections de scripts et ressources non autorisées |

### Cookies de session

- `HttpOnly` : le cookie de session n'est pas accessible en JavaScript — protège contre le vol de session via XSS
- `SameSite=Strict` : le cookie n'est pas envoyé dans les requêtes cross-site — protection CSRF complémentaire
- `Secure` : activé en production via la variable `SESSION_SECURE=true` — le cookie ne transite que sur HTTPS

### Erreurs et logs

En production, les erreurs PHP ne sont jamais affichées dans le navigateur (`display_errors = 0`). Les messages d'exception de la base de données sont masqués et remplacés par un message générique. Les erreurs réelles partent dans les logs serveur.

---

## 7. Cycle de vie d'une requête — exemple concret

**Un superviseur enregistre une naissance :**

```
1. POST /naissances  (formulaire soumis)
   │
2. Router  →  méthode POST + path /naissances → match
   │
3. Pipeline middleware :
   │   AuthMiddleware         →  session valide ? oui
   │   ArrondissementIsolation →  injecte arrondissement_id = 3 dans Request
   │   RoleMiddleware          →  rôle 'superviseur' autorisé ? oui
   │   CsrfMiddleware          →  token valide ? oui → rotation du token
   │
4. NaissanceController::store()
   │   Récupère les données POST
   │   Valide les champs requis
   │   Lit l'arrondissement_id depuis Request (celui injecté par le middleware)
   │
5. Naissance::create()
   │   Prépare et exécute l'INSERT via PDO
   │   Génère un UUID pour l'acte
   │   Applique la contrainte UNIQUE(arrondissement_id, numero_acte, annee)
   │
6. AuditLog::log('CREATE', 'NAISSANCE', $id)
   │   INSERT dans audit_logs avec snapshot JSON
   │
7. redirect('/naissances/' . $id)
   │
8. Page de détail de l'acte créé
```

---

## 8. Déploiement et infrastructure

### Architecture de démonstration (actuelle)

```
[Navigateur]
     │ HTTPS
     ▼
[Render — Conteneur Docker]
 PHP 8.2 · Application MVC
     │ TCP chiffré
     ▼
[Railway — MySQL 8 managé]
```

L'application est conteneurisée via un `Dockerfile` basé sur l'image officielle `php:8.2-cli`. Au démarrage, `deploy.sh` attend que MySQL soit disponible, exécute la migration (idempotente — `CREATE TABLE IF NOT EXISTS` + `INSERT IGNORE`), puis lance le serveur PHP intégré. Chaque push sur `main` déclenche un redéploiement automatique sur Render.

### Idempotence des migrations

Le script `database/migrate.php` peut être exécuté plusieurs fois sans erreur. Toutes les tables utilisent `CREATE TABLE IF NOT EXISTS`, les données de seed utilisent `INSERT IGNORE`. Cela permet de redéployer sans risquer de casser une base déjà initialisée.

### Architecture cible (production)

Pour une Mairie de Cotonou souveraine, l'architecture recommandée est un VPS hébergé au Bénin (Afriregister, CloudStore Africa) :

```
[13 arrondissements]
        │ HTTPS
        ▼
[WAF + Nginx reverse proxy]
 Terminaison TLS · Rate limiting
        │
        ▼
[Ubuntu 22.04 · PHP 8.2-FPM · MySQL 8.0]
        │
        ▼
[Sauvegardes externalisées]
```

Config minimale : 4 vCPU, 8 Go RAM, 200 Go SSD — suffisant pour les 13 arrondissements avec des volumes d'actes réalistes.

---

## 9. Conformité légale

L'application est conçue pour respecter le cadre légal béninois de l'état civil :

- **Ordonnance n°69-23 du 10 juillet 1969** — définit les champs obligatoires de chaque type d'acte
- **Loi n°2002-07 du 24 août 2004** — Code des personnes et de la famille, dont l'Art. 143 sur la polygamie
- **Décret n°73-292 du 20 août 1973** — modalités d'application de l'Ordonnance 69-23
- **Loi n°2008-12 du 25 janvier 2008** — protection des données à caractère personnel (Bénin)

Points de conformité implémentés :
- Rappel de la limite de 24h pour la déclaration de décès dans le formulaire
- Support du mariage polygamique dans le modèle de données
- Actes non supprimables (soft delete avec statuts)
- Traçabilité complète par audit log
- Documents PDF avec numéro d'expédition officiel et hash d'intégrité SHA-256
