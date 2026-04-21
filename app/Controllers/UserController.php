<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
class UserController extends Controller {
    public function index(Request $r): void      { $this->render('users/index',   ['title'=>'Utilisateurs','users'=>User::allWithRole()]); }
    public function create(Request $r): void     { $this->render('users/form',    ['title'=>'Nouvel utilisateur','user'=>null,'errors'=>[]]); }
    public function store(Request $r): void      { $this->redirect('/utilisateurs'); }
    public function edit(Request $r): void       { $this->redirect('/utilisateurs'); }
    public function update(Request $r): void     { $this->redirect('/utilisateurs'); }
    public function deactivate(Request $r): void { $this->redirect('/utilisateurs'); }
    public function profile(Request $r): void    { $this->render('users/profile', ['title'=>'Mon profil']); }
    public function updateProfile(Request $r): void { $this->redirect('/profil'); }
}
