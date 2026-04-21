<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\AuditLog;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'role'   => $request->get('role', ''),
            'arrondissement' => $request->get('arrondissement', ''),
            'statut' => $request->get('statut', ''),
        ];

        $users = $this->searchUsers($filters);

        $this->render('users/index', [
            'title'           => 'Utilisateurs',
            'users'           => $users,
            'filters'         => $filters,
            'arrondissements' => $this->getAllArrondissements(),
            'roles'           => $this->getRoles(),
            'flash'           => $this->getFlash(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->render('users/form', [
            'title'           => 'Nouveau compte',
            'utilisateur'     => [],
            'errors'          => [],
            'arrondissements' => $this->getAllArrondissements(),
            'roles'           => $this->getRoles(),
        ]);
    }

    public function store(Request $request): void
    {
        $data   = $this->extractFormData($request);
        $errors = $this->validateCreate($data);

        if (!empty($errors)) {
            $this->render('users/form', [
                'title'           => 'Nouveau compte',
                'utilisateur'     => $data,
                'errors'          => $errors,
                'arrondissements' => $this->getAllArrondissements(),
                'roles'           => $this->getRoles(),
            ]);
            return;
        }

        $id = User::create([
            'arrondissement_id' => $data['arrondissement_id'] ?: null,
            'role_id'           => $data['role_id'],
            'matricule'         => $data['matricule'] ?: null,
            'nom'               => strtoupper(trim($data['nom'])),
            'prenom'            => trim($data['prenom']),
            'email'             => strtolower(trim($data['email'])),
            'telephone'         => $data['telephone'] ?: null,
            'password'          => $data['password'],
            'is_active'         => 1,
            'created_by'        => $this->user()['id'],
        ]);

        AuditLog::log('CREATE', 'USER', $id, null, array_diff_key($data, ['password' => '', 'password_confirm' => '']));

        $this->flash('success', 'Compte créé avec succès.');
        $this->redirect('/utilisateurs');
    }

    public function edit(Request $request): void
    {
        $utilisateur = $this->findOrAbort($request->getAttribute('id'));

        $this->render('users/form', [
            'title'           => 'Modifier le compte',
            'utilisateur'     => $utilisateur,
            'errors'          => [],
            'arrondissements' => $this->getAllArrondissements(),
            'roles'           => $this->getRoles(),
        ]);
    }

    public function update(Request $request): void
    {
        $id          = $request->getAttribute('id');
        $utilisateur = $this->findOrAbort($id);
        $data        = $this->extractFormData($request);
        $errors      = $this->validateUpdate($data, $id);

        if (!empty($errors)) {
            $this->render('users/form', [
                'title'           => 'Modifier le compte',
                'utilisateur'     => array_merge($utilisateur, $data),
                'errors'          => $errors,
                'arrondissements' => $this->getAllArrondissements(),
                'roles'           => $this->getRoles(),
            ]);
            return;
        }

        $before = $utilisateur;

        $updateData = [
            'arrondissement_id' => $data['arrondissement_id'] ?: null,
            'role_id'           => $data['role_id'],
            'matricule'         => $data['matricule'] ?: null,
            'nom'               => strtoupper(trim($data['nom'])),
            'prenom'            => trim($data['prenom']),
            'email'             => strtolower(trim($data['email'])),
            'telephone'         => $data['telephone'] ?: null,
        ];

        if (!empty($data['password'])) {
            User::changePassword($id, $data['password']);
        }

        User::update($id, $updateData);
        AuditLog::log('UPDATE', 'USER', $id, $before, $updateData);

        $this->flash('success', 'Compte mis à jour.');
        $this->redirect('/utilisateurs');
    }

    public function deactivate(Request $request): void
    {
        $id          = $request->getAttribute('id');
        $utilisateur = $this->findOrAbort($id);

        // Impossible de désactiver son propre compte
        if ($id === $this->user()['id']) {
            $this->flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            $this->redirect('/utilisateurs');
            return;
        }

        $newStatus = $utilisateur['is_active'] ? 0 : 1;
        User::update($id, ['is_active' => $newStatus]);
        AuditLog::log('UPDATE', 'USER', $id, $utilisateur, ['is_active' => $newStatus]);

        $label = $newStatus ? 'réactivé' : 'désactivé';
        $this->flash('success', "Compte {$label} avec succès.");
        $this->redirect('/utilisateurs');
    }

    public function profile(Request $request): void
    {
        $sessionUser = $this->user();
        $utilisateur = User::allWithRole(['id' => $sessionUser['id']])[0] ?? $sessionUser;

        $this->render('users/profile', [
            'title'       => 'Mon profil',
            'utilisateur' => $utilisateur,
            'errors'      => [],
            'flash'       => $this->getFlash(),
        ]);
    }

    public function updateProfile(Request $request): void
    {
        $sessionUser = $this->user();
        $id          = $sessionUser['id'];
        $errors      = [];

        $telephone  = trim($request->post('telephone', ''));
        $pwdCurrent = $request->post('current_password', '');
        $pwdNew     = $request->post('new_password', '');
        $pwdConfirm = $request->post('new_password_confirm', '');

        if (!empty($pwdNew)) {
            $userRecord = User::find($id);
            if (!password_verify($pwdCurrent, $userRecord['password_hash'])) {
                $errors['current_password'] = 'Mot de passe actuel incorrect.';
            } elseif (strlen($pwdNew) < 8) {
                $errors['new_password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif ($pwdNew !== $pwdConfirm) {
                $errors['new_password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }
        }

        if (!empty($errors)) {
            $utilisateur = User::allWithRole(['id' => $id])[0] ?? $sessionUser;
            $this->render('users/profile', [
                'title'       => 'Mon profil',
                'utilisateur' => $utilisateur,
                'errors'      => $errors,
                'flash'       => null,
            ]);
            return;
        }

        User::update($id, ['telephone' => $telephone ?: null]);

        if (!empty($pwdNew)) {
            User::changePassword($id, $pwdNew);
            AuditLog::log('UPDATE', 'USER', $id, [], ['action' => 'password_change']);
        }

        $this->flash('success', 'Profil mis à jour.');
        $this->redirect('/profil');
    }

    // -------------------------------------------------------

    private function findOrAbort(string $id): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT u.*, r.code AS role_code, r.id AS role_id_val,
                    a.nom AS arrondissement_nom
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN arrondissements a ON u.arrondissement_id = a.id
             WHERE u.id = ? AND u.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->abort(404);
        }
        return $row;
    }

    private function extractFormData(Request $request): array
    {
        return [
            'nom'               => $request->post('nom', ''),
            'prenom'            => $request->post('prenom', ''),
            'email'             => $request->post('email', ''),
            'telephone'         => $request->post('telephone', ''),
            'matricule'         => $request->post('matricule', ''),
            'role_id'           => $request->post('role_id', ''),
            'arrondissement_id' => $request->post('arrondissement_id', ''),
            'password'          => $request->post('password', ''),
            'password_confirm'  => $request->post('password_confirm', ''),
        ];
    }

    private function validateCreate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['nom'])))    $errors['nom']    = 'Le nom est obligatoire.';
        if (empty(trim($data['prenom']))) $errors['prenom'] = 'Le prénom est obligatoire.';
        if (empty(trim($data['email'])))  $errors['email']  = 'L\'email est obligatoire.';
        if (empty($data['role_id']))      $errors['role_id'] = 'Le rôle est obligatoire.';

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide.';
        }

        if (!empty($data['email']) && empty($errors['email'])) {
            $existing = User::findByEmail($data['email']);
            if ($existing) $errors['email'] = 'Cet email est déjà utilisé.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Au moins 8 caractères.';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }

        return $errors;
    }

    private function validateUpdate(array $data, string $currentId): array
    {
        $errors = [];
        if (empty(trim($data['nom'])))    $errors['nom']    = 'Le nom est obligatoire.';
        if (empty(trim($data['prenom']))) $errors['prenom'] = 'Le prénom est obligatoire.';
        if (empty(trim($data['email'])))  $errors['email']  = 'L\'email est obligatoire.';
        if (empty($data['role_id']))      $errors['role_id'] = 'Le rôle est obligatoire.';

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format d\'email invalide.';
        }

        if (!empty($data['email']) && empty($errors['email'])) {
            $existing = User::findByEmail($data['email']);
            if ($existing && $existing['id'] !== $currentId) {
                $errors['email'] = 'Cet email est déjà utilisé par un autre compte.';
            }
        }

        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                $errors['password'] = 'Au moins 8 caractères.';
            } elseif ($data['password'] !== $data['password_confirm']) {
                $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
            }
        }

        return $errors;
    }

    private function searchUsers(array $filters): array
    {
        $where  = ['u.deleted_at IS NULL'];
        $values = [];

        if (!empty($filters['role'])) {
            $where[]  = 'r.code = ?';
            $values[] = $filters['role'];
        }
        if (!empty($filters['arrondissement'])) {
            $where[]  = 'u.arrondissement_id = ?';
            $values[] = $filters['arrondissement'];
        }
        if ($filters['statut'] !== '') {
            $where[]  = 'u.is_active = ?';
            $values[] = (int) $filters['statut'];
        }

        $sql  = 'SELECT u.id, u.matricule, u.nom, u.prenom, u.email, u.telephone,
                        u.is_active, u.last_login_at, u.created_at,
                        r.code AS role_code, r.libelle AS role_libelle,
                        a.nom AS arrondissement_nom, a.numero AS arrondissement_numero
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 LEFT JOIN arrondissements a ON u.arrondissement_id = a.id
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY a.numero ASC, u.nom ASC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    private function getRoles(): array
    {
        return Database::getConnection()->query('SELECT * FROM roles ORDER BY id ASC')->fetchAll();
    }

    private function getAllArrondissements(): array
    {
        return Database::getConnection()->query('SELECT * FROM arrondissements ORDER BY numero ASC')->fetchAll();
    }
}
