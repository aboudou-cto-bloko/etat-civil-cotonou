<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [
            'title' => 'Connexion — État Civil Cotonou',
            'error' => $request->get('error'),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        // Rate limiting : 5 tentatives max par IP sur 5 minutes
        $ip       = $request->ip();
        $key      = 'login_attempts_' . md5($ip);
        $attempts = (int) ($_SESSION[$key] ?? 0);
        $lockUntil = (int) ($_SESSION[$key . '_until'] ?? 0);

        if ($lockUntil > time()) {
            $wait = ceil(($lockUntil - time()) / 60);
            $this->render('auth/login', [
                'title'     => 'Connexion',
                'error'     => "Trop de tentatives. Réessayez dans {$wait} min.",
                'old_email' => $email,
            ], 'auth');
            return;
        }

        if (!$email || !$password) {
            $this->render('auth/login', [
                'title'      => 'Connexion',
                'error'      => 'Veuillez renseigner votre email et votre mot de passe.',
                'old_email'  => $email,
            ], 'auth');
            return;
        }

        $user = User::authenticate($email, $password);

        if (!$user) {
            $attempts++;
            $_SESSION[$key] = $attempts;
            if ($attempts >= 5) {
                $_SESSION[$key . '_until'] = time() + 300;
                $_SESSION[$key] = 0;
            }
            AuditLog::log('LOGIN_FAILED', 'USER', null, null, ['email' => $email, 'ip' => $ip]);
            $this->render('auth/login', [
                'title'     => 'Connexion',
                'error'     => 'Identifiants incorrects ou compte désactivé.',
                'old_email' => $email,
            ], 'auth');
            return;
        }

        // Réinitialiser le compteur de tentatives après succès
        unset($_SESSION[$key], $_SESSION[$key . '_until']);

        // Régénération d'ID de session (protection fixation de session)
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'                 => $user['id'],
            'nom'                => $user['nom'],
            'prenom'             => $user['prenom'],
            'email'              => $user['email'],
            'role_code'          => $user['role_code'],
            'role_libelle'       => $user['role_libelle'] ?? $user['role_code'],
            'arrondissement_id'  => $user['arrondissement_id'],
            'arrondissement_nom' => $user['arrondissement_nom'],
            'is_active'          => (bool) $user['is_active'],
        ];

        User::updateLastLogin($user['id']);
        AuditLog::log('LOGIN', 'USER', $user['id']);

        $this->redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        if (!empty($_SESSION['user'])) {
            AuditLog::log('LOGOUT', 'USER', $_SESSION['user']['id']);
        }

        session_destroy();
        $this->redirect('/login');
    }
}
