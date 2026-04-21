<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Deces;
class DecesController extends Controller {
    public function index(Request $r): void   { $this->render('actes/naissances/index', ['title'=>'Décès','resultats'=>Deces::search([],$this->arrondissementId()),'filters'=>[]]); }
    public function create(Request $r): void  { $this->render('actes/naissances/form',  ['title'=>'Nouveau décès','acte'=>null,'errors'=>[],'arrondissements'=>[]]); }
    public function store(Request $r): void   { $this->redirect('/deces'); }
    public function show(Request $r): void    { $this->redirect('/deces'); }
    public function edit(Request $r): void    { $this->redirect('/deces'); }
    public function update(Request $r): void  { $this->redirect('/deces'); }
}
