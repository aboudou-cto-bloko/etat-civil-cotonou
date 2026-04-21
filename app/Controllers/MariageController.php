<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Mariage;
class MariageController extends Controller {
    public function index(Request $r): void   { $this->render('actes/naissances/index', ['title'=>'Mariages','resultats'=>Mariage::search([],$this->arrondissementId()),'filters'=>[]]); }
    public function create(Request $r): void  { $this->render('actes/naissances/form',  ['title'=>'Nouveau mariage','acte'=>null,'errors'=>[],'arrondissements'=>[]]); }
    public function store(Request $r): void   { $this->redirect('/mariages'); }
    public function show(Request $r): void    { $this->redirect('/mariages'); }
    public function edit(Request $r): void    { $this->redirect('/mariages'); }
    public function update(Request $r): void  { $this->redirect('/mariages'); }
}
