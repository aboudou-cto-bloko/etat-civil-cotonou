<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\Deces;
use App\Models\AuditLog;

class DecesController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'nom'        => $request->get('nom', ''),
            'numero_acte'=> $request->get('numero_acte', ''),
            'annee'      => $request->get('annee', ''),
            'date_debut' => $request->get('date_debut', ''),
            'date_fin'   => $request->get('date_fin', ''),
        ];
        $sort      = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $page      = max(1, (int) $request->get('page', '1'));
        $resultats = Deces::search($filters, $this->arrondissementId(), 20, $page, $sort, $direction);

        $this->render('actes/deces/index', [
            'title'     => 'Actes de décès',
            'resultats' => $resultats,
            'filters'   => $filters,
            'sort'      => $sort,
            'direction' => $direction,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->render('actes/deces/form', [
            'title'           => 'Nouveau acte de décès',
            'arrondissements' => $this->getArrondissements(),
            'acte'            => null,
            'errors'          => [],
        ]);
    }

    public function store(Request $request): void
    {
        $data   = $this->extractFormData($request);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('actes/deces/form', [
                'title'           => 'Nouveau acte de décès',
                'arrondissements' => $this->getArrondissements(),
                'acte'            => $data,
                'errors'          => $errors,
            ]);
            return;
        }

        if ($this->arrondissementId() !== null) {
            $data['arrondissement_id'] = $this->arrondissementId();
        }

        $annee               = (int) date('Y');
        $data['annee']       = $annee;
        $data['numero_acte'] = Deces::prochainNumeroActe((int) $data['arrondissement_id'], $annee);
        $data['enregistre_par']         = $this->user()['id'];
        $data['officier_etat_civil_id'] = $this->user()['id'];
        $data['statut']                 = 'ACTIF';

        $id = Deces::insert($data);
        AuditLog::log('CREATE', 'DECES', $id, null, $data);

        $this->flash('success', "Acte de décès n°{$data['numero_acte']}/{$annee} enregistré avec succès.");
        $this->redirect('/deces/' . $id);
    }

    public function show(Request $request): void
    {
        $acte    = $this->findAndAuthorize($request->param('id'));
        $temoins = $this->getTemoins($acte['id']);

        $this->render('actes/deces/show', [
            'title'   => 'Acte de décès n°' . $acte['numero_acte'] . '/' . $acte['annee'],
            'acte'    => $acte,
            'temoins' => $temoins,
            'flash'   => $this->getFlash(),
        ]);
    }

    public function edit(Request $request): void
    {
        $acte = $this->findAndAuthorize($request->param('id'));

        $this->render('actes/deces/form', [
            'title'           => 'Modifier acte de décès',
            'arrondissements' => $this->getArrondissements(),
            'acte'            => $acte,
            'errors'          => [],
        ]);
    }

    public function update(Request $request): void
    {
        $acte   = $this->findAndAuthorize($request->param('id'));
        $data   = $this->extractFormData($request);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('actes/deces/form', [
                'title'           => 'Modifier acte de décès',
                'arrondissements' => $this->getArrondissements(),
                'acte'            => array_merge($acte, $data),
                'errors'          => $errors,
            ]);
            return;
        }

        $data['modifie_par'] = $this->user()['id'];
        AuditLog::log('UPDATE', 'DECES', $acte['id'], $acte, $data);

        Deces::update($acte['id'], $data);
        $this->flash('success', 'Acte de décès modifié avec succès.');
        $this->redirect('/deces/' . $acte['id']);
    }

    private function findAndAuthorize(string $id): array
    {
        $acte = Deces::findWithDetails($id);

        if (!$acte) {
            $this->abort(404);
        }

        $arrondissementId = $this->arrondissementId();
        if ($arrondissementId !== null && (int) $acte['arrondissement_id'] !== $arrondissementId) {
            $this->abort(403);
        }

        return $acte;
    }

    private function extractFormData(Request $request): array
    {
        return [
            'arrondissement_id'           => $request->post('arrondissement_id'),
            'date_declaration'            => $request->post('date_declaration'),
            'defunt_nom'                  => strtoupper(trim($request->post('defunt_nom', ''))),
            'defunt_prenom'               => trim($request->post('defunt_prenom', '')),
            'defunt_sexe'                 => $request->post('defunt_sexe'),
            'defunt_date_naissance'       => $request->post('defunt_date_naissance') ?: null,
            'defunt_lieu_naissance'       => trim($request->post('defunt_lieu_naissance', '')),
            'defunt_nationalite'          => trim($request->post('defunt_nationalite', 'Béninoise')),
            'defunt_profession'           => trim($request->post('defunt_profession', '')),
            'defunt_domicile'             => trim($request->post('defunt_domicile', '')),
            'defunt_situation_matrimoniale' => $request->post('defunt_situation_matrimoniale', ''),
            'defunt_pere_nom_prenom'      => trim($request->post('defunt_pere_nom_prenom', '')),
            'defunt_mere_nom_prenom'      => trim($request->post('defunt_mere_nom_prenom', '')),
            'date_deces'                  => $request->post('date_deces'),
            'lieu_deces'                  => trim($request->post('lieu_deces', '')),
            'cause_deces'                 => trim($request->post('cause_deces', '')),
            'certificat_medical_fourni'   => $request->post('certificat_medical_fourni') ? 1 : 0,
            'numero_certificat_medical'   => trim($request->post('numero_certificat_medical', '')),
            'declarant_nom'               => strtoupper(trim($request->post('declarant_nom', ''))),
            'declarant_prenom'            => trim($request->post('declarant_prenom', '')),
            'declarant_qualite'           => trim($request->post('declarant_qualite', '')),
            'declarant_domicile'          => trim($request->post('declarant_domicile', '')),
            'observations'                => trim($request->post('observations', '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['defunt_nom']))       $errors['defunt_nom']       = 'Le nom du défunt est requis.';
        if (empty($data['defunt_prenom']))    $errors['defunt_prenom']    = 'Le prénom du défunt est requis.';
        if (empty($data['defunt_sexe']))      $errors['defunt_sexe']      = 'Le sexe est requis.';
        if (empty($data['date_deces']))       $errors['date_deces']       = 'La date du décès est requise.';
        if (empty($data['lieu_deces']))       $errors['lieu_deces']       = 'Le lieu du décès est requis.';
        if (empty($data['date_declaration'])) $errors['date_declaration'] = 'La date de déclaration est requise.';
        if (empty($data['declarant_nom']))    $errors['declarant_nom']    = 'Le nom du déclarant est requis.';
        if (empty($data['declarant_qualite'])) $errors['declarant_qualite'] = 'La qualité du déclarant est requise.';
        return $errors;
    }

    private function getArrondissements(): array
    {
        $arrondissementId = $this->arrondissementId();
        if ($arrondissementId !== null) {
            $stmt = Database::getConnection()->prepare('SELECT * FROM arrondissements WHERE id = ?');
            $stmt->execute([$arrondissementId]);
            return $stmt->fetchAll();
        }
        return Database::getConnection()->query('SELECT * FROM arrondissements ORDER BY numero ASC')->fetchAll();
    }

    private function getTemoins(string $acteId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM temoins WHERE type_acte = 'DECES' AND acte_id = ? ORDER BY ordre ASC"
        );
        $stmt->execute([$acteId]);
        return $stmt->fetchAll();
    }
}
