-- ============================================================
-- ÉTAT CIVIL COTONOU — Schéma MySQL
-- Référence : Ordonnance 69-23 du 10 juillet 1969
--             Loi 2002-07 du 24 août 2004 (CPF Bénin)
-- MySQL >= 5.7 / MariaDB >= 10.4
-- Encodage : utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE DATABASE IF NOT EXISTS etat_civil_cotonou
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE etat_civil_cotonou;

-- ============================================================
-- BLOC 1 — RÉFÉRENTIELS
-- ============================================================

CREATE TABLE IF NOT EXISTS arrondissements (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    numero     INT          NOT NULL,
    nom        VARCHAR(100) NOT NULL,
    code       VARCHAR(10)  NOT NULL,
    chef_lieu  VARCHAR(150),
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_arrondissement_numero (numero),
    UNIQUE KEY uk_arrondissement_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50)  NOT NULL,
    libelle     VARCHAR(100) NOT NULL,
    description TEXT,
    UNIQUE KEY uk_role_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(100) NOT NULL,
    description TEXT,
    UNIQUE KEY uk_permission_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 2 — UTILISATEURS ET SESSIONS
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id                 CHAR(36)     NOT NULL PRIMARY KEY,
    arrondissement_id  INT,
    role_id            INT          NOT NULL,
    matricule          VARCHAR(50),
    nom                VARCHAR(100) NOT NULL,
    prenom             VARCHAR(200) NOT NULL,
    email              VARCHAR(255) NOT NULL,
    telephone          VARCHAR(20),
    password_hash      VARCHAR(255) NOT NULL,
    is_active          TINYINT(1)   DEFAULT 1,
    last_login_at      TIMESTAMP    NULL,
    created_by         CHAR(36),
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         TIMESTAMP    NULL,
    UNIQUE KEY uk_users_email (email),
    UNIQUE KEY uk_users_matricule (matricule),
    INDEX idx_users_arrondissement (arrondissement_id),
    INDEX idx_users_role (role_id),
    FOREIGN KEY (arrondissement_id) REFERENCES arrondissements(id),
    FOREIGN KEY (role_id)           REFERENCES roles(id),
    FOREIGN KEY (created_by)        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id          CHAR(36)     NOT NULL PRIMARY KEY,
    user_id     CHAR(36)     NOT NULL,
    token_hash  VARCHAR(255) NOT NULL,
    adresse_ip  VARCHAR(45),
    user_agent  TEXT,
    expires_at  TIMESTAMP    NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    revoked_at  TIMESTAMP    NULL,
    UNIQUE KEY uk_session_token (token_hash),
    INDEX idx_session_user (user_id),
    INDEX idx_session_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 3 — PERSONNES (référentiel partagé)
-- ============================================================

CREATE TABLE IF NOT EXISTS personnes (
    id                    CHAR(36)     NOT NULL PRIMARY KEY,
    nom                   VARCHAR(100) NOT NULL,
    prenom                VARCHAR(200) NOT NULL,
    date_naissance        DATE,
    lieu_naissance        VARCHAR(200),
    pays_naissance        VARCHAR(100) DEFAULT 'Bénin',
    nationalite           VARCHAR(100) DEFAULT 'Béninoise',
    sexe                  CHAR(1),
    profession            VARCHAR(150),
    adresse_domicile      TEXT,
    telephone             VARCHAR(20),
    numero_piece_identite VARCHAR(100),
    type_piece_identite   VARCHAR(50),
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 4 — ACTES DE NAISSANCE
-- ============================================================

CREATE TABLE IF NOT EXISTS naissances (
    id                        CHAR(36)     NOT NULL PRIMARY KEY,
    arrondissement_id         INT          NOT NULL,
    numero_acte               VARCHAR(50)  NOT NULL,
    annee                     INT          NOT NULL,
    date_declaration          DATE         NOT NULL,

    -- Enfant
    enfant_nom                VARCHAR(100) NOT NULL,
    enfant_prenom             VARCHAR(200) NOT NULL,
    enfant_sexe               CHAR(1)      NOT NULL,
    date_naissance            DATETIME     NOT NULL,
    lieu_naissance_commune    VARCHAR(150) NOT NULL,
    lieu_naissance_localite   VARCHAR(200),
    enfant_est_jumeau         TINYINT(1)   DEFAULT 0,
    ordre_jumeau              INT,

    -- Père
    pere_id                   CHAR(36),
    pere_nom                  VARCHAR(100),
    pere_prenom               VARCHAR(200),
    pere_date_naissance       DATE,
    pere_lieu_naissance       VARCHAR(200),
    pere_nationalite          VARCHAR(100),
    pere_profession           VARCHAR(150),
    pere_domicile             TEXT,
    pere_statut               VARCHAR(30)  DEFAULT 'CONNU',

    -- Mère
    mere_id                   CHAR(36),
    mere_nom                  VARCHAR(100) NOT NULL,
    mere_prenom               VARCHAR(200) NOT NULL,
    mere_date_naissance       DATE,
    mere_lieu_naissance       VARCHAR(200),
    mere_nationalite          VARCHAR(100),
    mere_profession           VARCHAR(150),
    mere_domicile             TEXT,

    -- Déclarant
    declarant_id              CHAR(36),
    declarant_qualite         VARCHAR(100) NOT NULL,
    declarant_nom             VARCHAR(100),
    declarant_prenom          VARCHAR(200),
    declarant_domicile        TEXT,

    -- Registre
    officier_etat_civil_id    CHAR(36)     NOT NULL,
    enregistre_par            CHAR(36)     NOT NULL,
    modifie_par               CHAR(36),

    -- Métadonnées
    observations              TEXT,
    statut                    VARCHAR(30)  DEFAULT 'ACTIF',
    acte_source_id            CHAR(36),
    created_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_naissance_acte (arrondissement_id, numero_acte, annee),
    INDEX idx_naissance_nom (enfant_nom),
    INDEX idx_naissance_prenom (enfant_prenom),
    INDEX idx_naissance_date (date_naissance),
    INDEX idx_naissance_statut (statut),
    INDEX idx_naissance_arr (arrondissement_id),

    FOREIGN KEY (arrondissement_id)       REFERENCES arrondissements(id),
    FOREIGN KEY (pere_id)                 REFERENCES personnes(id),
    FOREIGN KEY (mere_id)                 REFERENCES personnes(id),
    FOREIGN KEY (declarant_id)            REFERENCES personnes(id),
    FOREIGN KEY (officier_etat_civil_id)  REFERENCES users(id),
    FOREIGN KEY (enregistre_par)          REFERENCES users(id),
    FOREIGN KEY (modifie_par)             REFERENCES users(id),
    FOREIGN KEY (acte_source_id)          REFERENCES naissances(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 5 — ACTES DE MARIAGE
-- ============================================================

CREATE TABLE IF NOT EXISTS mariages (
    id                           CHAR(36)     NOT NULL PRIMARY KEY,
    arrondissement_id            INT          NOT NULL,
    numero_acte                  VARCHAR(50)  NOT NULL,
    annee                        INT          NOT NULL,
    date_mariage                 DATE         NOT NULL,
    heure_mariage                TIME,
    lieu_celebration             VARCHAR(300) NOT NULL,
    type_mariage                 VARCHAR(30)  NOT NULL DEFAULT 'MONOGAMIQUE',
    regime_matrimonial           VARCHAR(100),

    -- Époux
    epoux_id                     CHAR(36),
    epoux_nom                    VARCHAR(100) NOT NULL,
    epoux_prenom                 VARCHAR(200) NOT NULL,
    epoux_date_naissance         DATE         NOT NULL,
    epoux_lieu_naissance         VARCHAR(200),
    epoux_nationalite            VARCHAR(100),
    epoux_profession             VARCHAR(150),
    epoux_domicile               TEXT,
    epoux_statut_anterieur       VARCHAR(30),
    epoux_pere_nom_prenom        VARCHAR(300),
    epoux_mere_nom_prenom        VARCHAR(300),

    -- Épouse principale
    epouse_id                    CHAR(36),
    epouse_nom                   VARCHAR(100) NOT NULL,
    epouse_prenom                VARCHAR(200) NOT NULL,
    epouse_date_naissance        DATE         NOT NULL,
    epouse_lieu_naissance        VARCHAR(200),
    epouse_nationalite           VARCHAR(100),
    epouse_profession            VARCHAR(150),
    epouse_domicile              TEXT,
    epouse_statut_anterieur      VARCHAR(30),
    epouse_pere_nom_prenom       VARCHAR(300),
    epouse_mere_nom_prenom       VARCHAR(300),

    -- Bans
    date_publication_bans        DATE,
    lieu_publication_bans        VARCHAR(200),
    opposition_recue             TINYINT(1)   DEFAULT 0,
    detail_opposition            TEXT,

    -- Consentements
    consentement_parents_epoux   TINYINT(1)   DEFAULT 0,
    consentement_parents_epouse  TINYINT(1)   DEFAULT 0,
    autorisation_judiciaire      TINYINT(1)   DEFAULT 0,

    -- Registre
    officier_etat_civil_id       CHAR(36)     NOT NULL,
    enregistre_par               CHAR(36)     NOT NULL,
    modifie_par                  CHAR(36),
    observations                 TEXT,
    statut                       VARCHAR(30)  DEFAULT 'ACTIF',
    acte_source_id               CHAR(36),
    created_at                   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at                   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_mariage_acte (arrondissement_id, numero_acte, annee),
    INDEX idx_mariage_epoux (epoux_nom),
    INDEX idx_mariage_epouse (epouse_nom),
    INDEX idx_mariage_date (date_mariage),
    INDEX idx_mariage_statut (statut),
    INDEX idx_mariage_arr (arrondissement_id),

    FOREIGN KEY (arrondissement_id)       REFERENCES arrondissements(id),
    FOREIGN KEY (epoux_id)                REFERENCES personnes(id),
    FOREIGN KEY (epouse_id)               REFERENCES personnes(id),
    FOREIGN KEY (officier_etat_civil_id)  REFERENCES users(id),
    FOREIGN KEY (enregistre_par)          REFERENCES users(id),
    FOREIGN KEY (modifie_par)             REFERENCES users(id),
    FOREIGN KEY (acte_source_id)          REFERENCES mariages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mariage_epouses_supplementaires (
    id                      CHAR(36)     NOT NULL PRIMARY KEY,
    mariage_id              CHAR(36)     NOT NULL,
    epouse_id               CHAR(36),
    ordre_epouse            INT          NOT NULL,
    epouse_nom              VARCHAR(100) NOT NULL,
    epouse_prenom           VARCHAR(200) NOT NULL,
    epouse_date_naissance   DATE,
    epouse_lieu_naissance   VARCHAR(200),
    epouse_nationalite      VARCHAR(100),
    epouse_profession       VARCHAR(150),
    epouse_domicile         TEXT,
    epouse_statut_anterieur VARCHAR(30),
    epouse_pere_nom_prenom  VARCHAR(300),
    epouse_mere_nom_prenom  VARCHAR(300),
    created_at              TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_epouse_sup_mariage (mariage_id),
    FOREIGN KEY (mariage_id) REFERENCES mariages(id) ON DELETE CASCADE,
    FOREIGN KEY (epouse_id)  REFERENCES personnes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 6 — ACTES DE DÉCÈS
-- ============================================================

CREATE TABLE IF NOT EXISTS deces (
    id                             CHAR(36)     NOT NULL PRIMARY KEY,
    arrondissement_id              INT          NOT NULL,
    numero_acte                    VARCHAR(50)  NOT NULL,
    annee                          INT          NOT NULL,
    date_declaration               DATE         NOT NULL,

    -- Défunt
    defunt_id                      CHAR(36),
    defunt_nom                     VARCHAR(100) NOT NULL,
    defunt_prenom                  VARCHAR(200) NOT NULL,
    defunt_sexe                    CHAR(1)      NOT NULL,
    defunt_date_naissance          DATE,
    defunt_lieu_naissance          VARCHAR(200),
    defunt_nationalite             VARCHAR(100),
    defunt_profession              VARCHAR(150),
    defunt_domicile                TEXT,
    defunt_situation_matrimoniale  VARCHAR(30),
    defunt_pere_nom_prenom         VARCHAR(300),
    defunt_mere_nom_prenom         VARCHAR(300),

    -- Circonstances
    date_deces                     DATETIME     NOT NULL,
    lieu_deces                     VARCHAR(300) NOT NULL,
    cause_deces                    VARCHAR(300),
    certificat_medical_fourni      TINYINT(1)   DEFAULT 0,
    numero_certificat_medical      VARCHAR(100),

    -- Déclarant
    declarant_id                   CHAR(36),
    declarant_nom                  VARCHAR(100) NOT NULL,
    declarant_prenom               VARCHAR(200) NOT NULL,
    declarant_qualite              VARCHAR(150) NOT NULL,
    declarant_domicile             TEXT,

    -- Registre
    officier_etat_civil_id         CHAR(36)     NOT NULL,
    enregistre_par                 CHAR(36)     NOT NULL,
    modifie_par                    CHAR(36),
    observations                   TEXT,
    statut                         VARCHAR(30)  DEFAULT 'ACTIF',
    acte_source_id                 CHAR(36),
    created_at                     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at                     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_deces_acte (arrondissement_id, numero_acte, annee),
    INDEX idx_deces_nom (defunt_nom),
    INDEX idx_deces_prenom (defunt_prenom),
    INDEX idx_deces_date (date_deces),
    INDEX idx_deces_statut (statut),
    INDEX idx_deces_arr (arrondissement_id),

    FOREIGN KEY (arrondissement_id)       REFERENCES arrondissements(id),
    FOREIGN KEY (defunt_id)               REFERENCES personnes(id),
    FOREIGN KEY (declarant_id)            REFERENCES personnes(id),
    FOREIGN KEY (officier_etat_civil_id)  REFERENCES users(id),
    FOREIGN KEY (enregistre_par)          REFERENCES users(id),
    FOREIGN KEY (modifie_par)             REFERENCES users(id),
    FOREIGN KEY (acte_source_id)          REFERENCES deces(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 7 — TÉMOINS (polymorphique)
-- ============================================================

CREATE TABLE IF NOT EXISTS temoins (
    id                    CHAR(36)     NOT NULL PRIMARY KEY,
    personne_id           CHAR(36),
    type_acte             VARCHAR(20)  NOT NULL,
    acte_id               CHAR(36)     NOT NULL,
    ordre                 INT          NOT NULL DEFAULT 1,
    nom                   VARCHAR(100) NOT NULL,
    prenom                VARCHAR(200) NOT NULL,
    age                   INT,
    profession            VARCHAR(150),
    domicile              TEXT,
    nationalite           VARCHAR(100),
    numero_piece_identite VARCHAR(100),
    type_piece_identite   VARCHAR(50),
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_temoins_acte (type_acte, acte_id),
    INDEX idx_temoins_personne (personne_id),
    FOREIGN KEY (personne_id) REFERENCES personnes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 8 — DOCUMENTS GÉNÉRÉS
-- ============================================================

CREATE TABLE IF NOT EXISTS documents_generes (
    id                CHAR(36)     NOT NULL PRIMARY KEY,
    type_acte         VARCHAR(20)  NOT NULL,
    acte_id           CHAR(36)     NOT NULL,
    type_document     VARCHAR(60)  NOT NULL,
    numero_expedition INT,
    genere_par        CHAR(36)     NOT NULL,
    genere_le         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    fichier_chemin    VARCHAR(500),
    fichier_hash      VARCHAR(64),
    demandeur_nom     VARCHAR(200),
    motif_demande     TEXT,
    is_valide         TINYINT(1)   DEFAULT 1,

    INDEX idx_doc_acte (type_acte, acte_id),
    INDEX idx_doc_genere_par (genere_par),
    INDEX idx_doc_genere_le (genere_le),
    FOREIGN KEY (genere_par) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 9 — AUDIT LOGS
-- ============================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id                CHAR(36)     NOT NULL PRIMARY KEY,
    user_id           CHAR(36),
    arrondissement_id INT,
    action            VARCHAR(60)  NOT NULL,
    type_entite       VARCHAR(30),
    entite_id         CHAR(36),
    anciennes_valeurs JSON,
    nouvelles_valeurs JSON,
    adresse_ip        VARCHAR(45),
    user_agent        TEXT,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_audit_user (user_id),
    INDEX idx_audit_arr (arrondissement_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_entite (type_entite, entite_id),
    INDEX idx_audit_date (created_at),
    FOREIGN KEY (user_id)           REFERENCES users(id)           ON DELETE SET NULL,
    FOREIGN KEY (arrondissement_id) REFERENCES arrondissements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOC 10 — STATISTIQUES PRÉCALCULÉES
-- ============================================================

CREATE TABLE IF NOT EXISTS statistiques_periodiques (
    id                      CHAR(36)    NOT NULL PRIMARY KEY,
    arrondissement_id       INT,
    periode_type            VARCHAR(20) NOT NULL,
    periode_debut           DATE        NOT NULL,
    periode_fin             DATE        NOT NULL,
    total_naissances        INT         DEFAULT 0,
    naissances_masculin     INT         DEFAULT 0,
    naissances_feminin      INT         DEFAULT 0,
    naissances_jumeaux      INT         DEFAULT 0,
    total_mariages          INT         DEFAULT 0,
    mariages_monogamiques   INT         DEFAULT 0,
    mariages_polygamiques   INT         DEFAULT 0,
    total_deces             INT         DEFAULT 0,
    deces_masculin          INT         DEFAULT 0,
    deces_feminin           INT         DEFAULT 0,
    total_documents_generes INT         DEFAULT 0,
    total_operations        INT         DEFAULT 0,
    calcule_le              TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_stats_periode (arrondissement_id, periode_type, periode_debut),
    FOREIGN KEY (arrondissement_id) REFERENCES arrondissements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
