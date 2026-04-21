-- ============================================================
-- DONNÉES INITIALES — État Civil Cotonou
-- À exécuter après schema.sql
-- ============================================================

USE etat_civil_cotonou;

-- ============================================================
-- 13 Arrondissements de Cotonou
-- ============================================================

INSERT INTO arrondissements (numero, nom, code, chef_lieu) VALUES
(1,  'Arrondissement 1',  'ARR-01', 'Zongo'),
(2,  'Arrondissement 2',  'ARR-02', 'Missèbo'),
(3,  'Arrondissement 3',  'ARR-03', 'Gbédégbé'),
(4,  'Arrondissement 4',  'ARR-04', 'Fidjrossè-Kpota'),
(5,  'Arrondissement 5',  'ARR-05', 'Sainte-Rita'),
(6,  'Arrondissement 6',  'ARR-06', 'Akpakpa'),
(7,  'Arrondissement 7',  'ARR-07', 'Agla'),
(8,  'Arrondissement 8',  'ARR-08', 'Sikècodji'),
(9,  'Arrondissement 9',  'ARR-09', 'Cadjèhoun'),
(10, 'Arrondissement 10', 'ARR-10', 'Kouhounou'),
(11, 'Arrondissement 11', 'ARR-11', 'Gbèdjromèdé'),
(12, 'Arrondissement 12', 'ARR-12', 'Vodjè'),
(13, 'Arrondissement 13', 'ARR-13', 'Fifadji');

-- ============================================================
-- Rôles
-- ============================================================

INSERT INTO roles (code, libelle, description) VALUES
('admin',       'Administrateur',  'Accès complet à toutes les données de tous les arrondissements. Gestion des utilisateurs et paramétrage.'),
('superviseur', 'Superviseur',     'Responsable état civil d''un arrondissement. Peut enregistrer, modifier et consulter les actes de son arrondissement.'),
('analytics',   'Analytics',       'Consultation des statistiques uniquement, limitée à son arrondissement. Aucun accès aux données nominatives.');

-- ============================================================
-- Permissions granulaires
-- ============================================================

INSERT INTO permissions (code, description) VALUES
('naissance:create',    'Enregistrer un acte de naissance'),
('naissance:read',      'Consulter les actes de naissance'),
('naissance:update',    'Modifier un acte de naissance'),
('mariage:create',      'Enregistrer un acte de mariage'),
('mariage:read',        'Consulter les actes de mariage'),
('mariage:update',      'Modifier un acte de mariage'),
('deces:create',        'Enregistrer un acte de décès'),
('deces:read',          'Consulter les actes de décès'),
('deces:update',        'Modifier un acte de décès'),
('pdf:generate',        'Générer un document PDF officiel'),
('stats:view',          'Consulter les statistiques'),
('stats:export',        'Exporter les statistiques'),
('users:manage',        'Gérer les comptes utilisateurs'),
('audit:view',          'Consulter le journal d''audit');

-- Association admin (toutes les permissions)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.code = 'admin';

-- Association superviseur
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.code = 'superviseur'
AND p.code IN (
    'naissance:create', 'naissance:read', 'naissance:update',
    'mariage:create', 'mariage:read', 'mariage:update',
    'deces:create', 'deces:read', 'deces:update',
    'pdf:generate', 'stats:view', 'stats:export'
);

-- Association analytics
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.code = 'analytics'
AND p.code IN ('stats:view', 'stats:export');

-- ============================================================
-- Compte administrateur par défaut
-- Mot de passe : Admin@Cotonou2026 (à changer impérativement)
-- Hash bcrypt cost=12
-- ============================================================

INSERT INTO users (
    id, arrondissement_id, role_id, matricule, nom, prenom,
    email, telephone, password_hash, is_active
)
SELECT
    '00000000-0000-0000-0000-000000000001',
    NULL,
    r.id,
    'ADM-001',
    'ADMINISTRATEUR',
    'Système',
    'admin@etatcivil-cotonou.bj',
    NULL,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    1
FROM roles r WHERE r.code = 'admin';

-- Note : Remplacez le hash ci-dessus par celui généré avec :
-- php -r "echo password_hash('VotreMotDePasse', PASSWORD_BCRYPT, ['cost' => 12]);"
