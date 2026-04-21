<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\Naissance;
use App\Models\AuditLog;

class NaissanceController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'nom'         => $request->get('nom', ''),
            'numero_acte' => $request->get('numero_acte', ''),
            'annee'       => $request->get('annee', ''),
            'date_debut'  => $request->get('date_debut', ''),
            'date_fin'    => $request->get('date_fin', ''),
        ];

        $sort      = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $page      = max(1, (int) $request->get('page', '1'));
        $resultats = Naissance::search($filters, $this->arrondissementId(), 20, $page, $sort, $direction);

        $this->render('actes/naissances/index', [
            'title'     => 'Actes de naissance',
            'resultats' => $resultats,
            'filters'   => $filters,
            'sort'      => $sort,
            'direction' => $direction,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function create(Request $request): void
    {
        $arrondissements = $this->getArrondissements();

        $this->render('actes/naissances/form', [
            'title'           => 'Nouveau acte de naissance',
            'arrondissements' => $arrondissements,
            'acte'            => null,
            'errors'          => [],
        ]);
    }

    public function store(Request $request): void
    {
        $data   = $this->extractFormData($request);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->render('actes/naissances/form', [
                'title'           => 'Nouveau acte de naissance',
                'arrondissements' => $this->getArrondissements(),
                'acte'            => $data,
                'errors'          => $errors,
            ]);
            return;
        }

        // Forcer l'arrondissement pour les superviseurs
        if ($this->arrondissementId() !== null) {
            $data['arrondissement_id'] = $this->arrondissementId();
        }

        $annee               = (int) date('Y');
        $data['annee']       = $annee;
        $data['numero_acte'] = Naissance::prochainNumeroActe((int) $data['arrondissement_id'], $annee);
        $data['enregistre_par']         = $this->user()['id'];
        $data['officier_etat_civil_id'] = $this->user()['id'];
        $data['statut']                 = 'ACTIF';

        $id = Naissance::insert($data);
        AuditLog::log('CREATE', 'NAISSANCE', $id, null, $data);

        $this->flash('success', "Acte de naissance n°{$data['numero_acte']}/{$annee} enregistré avec succès.");
        $this->redirect('/naissances/' . $id);
    }

    public function show(Request $request): void
    {
        $acte = $this->findAndAuthorize($request->param('id'));

        $temoins = $this->getTemoins($acte['id']);

        $this->render('actes/naissances/show', [
            'title'   => 'Acte de naissance n°' . $acte['numero_acte'] . '/' . $acte['annee'],
            'acte'    => $acte,
            'temoins' => $temoins,
            'flash'   => $this->getFlash(),
        ]);
    }

    public function edit(Request $request): void
    {
        $acte = $this->findAndAuthorize($request->param('id'));

        $this->render('actes/naissances/form', [
            'title'           => 'Modifier acte de naissance',
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
            $this->render('actes/naissances/form', [
                'title'           => 'Modifier acte de naissance',
                'arrondissements' => $this->getArrondissements(),
                'acte'            => array_merge($acte, $data),
                'errors'          => $errors,
            ]);
            return;
        }

        $data['modifie_par'] = $this->user()['id'];
        AuditLog::log('UPDATE', 'NAISSANCE', $acte['id'], $acte, $data);

        Naissance::update($acte['id'], $data);
        $this->flash('success', 'Acte de naissance modifié avec succès.');
        $this->redirect('/naissances/' . $acte['id']);
    }

    private function findAndAuthorize(string $id): array
    {
        $acte = Naissance::findWithDetails($id);

        if (!$acte) {
            $this->abort(404);
        }

        // Vérification isolation arrondissement
        $arrondissementId = $this->arrondissementId();
        if ($arrondissementId !== null && (int) $acte['arrondissement_id'] !== $arrondissementId) {
            $this->abort(403);
        }

        return $acte;
    }

    private function extractFormData(Request $request): array
    {
        return [
            'arrondissement_id'         => $request->post('arrondissement_id'),
            'date_declaration'          => $request->post('date_declaration'),
            'enfant_nom'                => trim($request->post('enfant_nom', '')),
            'enfant_prenom'             => trim($request->post('enfant_prenom', '')),
            'enfant_sexe'               => $request->post('enfant_sexe'),
            'date_naissance'            => $request->post('date_naissance'),
            'lieu_naissance_commune'    => trim($request->post('lieu_naissance_commune', '')),
            'lieu_naissance_localite'   => trim($request->post('lieu_naissance_localite', '')),
            'enfant_est_jumeau'         => $request->post('enfant_est_jumeau') ? 1 : 0,
            'ordre_jumeau'              => $request->post('ordre_jumeau') ?: null,
            'pere_nom'                  => trim($request->post('pere_nom', '')),
            'pere_prenom'               => trim($request->post('pere_prenom', '')),
            'pere_date_naissance'       => $request->post('pere_date_naissance') ?: null,
            'pere_lieu_naissance'       => trim($request->post('pere_lieu_naissance', '')),
            'pere_nationalite'          => trim($request->post('pere_nationalite', 'Béninoise')),
            'pere_profession'           => trim($request->post('pere_profession', '')),
            'pere_domicile'             => trim($request->post('pere_domicile', '')),
            'pere_statut'               => $request->post('pere_statut', 'CONNU'),
            'mere_nom'                  => trim($request->post('mere_nom', '')),
            'mere_prenom'               => trim($request->post('mere_prenom', '')),
            'mere_date_naissance'       => $request->post('mere_date_naissance') ?: null,
            'mere_lieu_naissance'       => trim($request->post('mere_lieu_naissance', '')),
            'mere_nationalite'          => trim($request->post('mere_nationalite', 'Béninoise')),
            'mere_profession'           => trim($request->post('mere_profession', '')),
            'mere_domicile'             => trim($request->post('mere_domicile', '')),
            'declarant_qualite'         => trim($request->post('declarant_qualite', '')),
            'declarant_nom'             => trim($request->post('declarant_nom', '')),
            'declarant_prenom'          => trim($request->post('declarant_prenom', '')),
            'declarant_domicile'        => trim($request->post('declarant_domicile', '')),
            'observations'              => trim($request->post('observations', '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['enfant_nom']))             $errors['enfant_nom']  = 'Le nom de l\'enfant est requis.';
        if (empty($data['enfant_prenom']))          $errors['enfant_prenom'] = 'Le(s) prénom(s) de l\'enfant est requis.';
        if (empty($data['enfant_sexe']))            $errors['enfant_sexe']   = 'Le sexe est requis.';
        if (empty($data['date_naissance']))         $errors['date_naissance'] = 'La date de naissance est requise.';
        if (empty($data['lieu_naissance_commune'])) $errors['lieu_naissance_commune'] = 'Le lieu de naissance est requis.';
        if (empty($data['date_declaration']))       $errors['date_declaration'] = 'La date de déclaration est requise.';
        if (empty($data['mere_nom']))               $errors['mere_nom']    = 'Le nom de la mère est requis.';
        if (empty($data['mere_prenom']))            $errors['mere_prenom'] = 'Le prénom de la mère est requis.';
        if (empty($data['declarant_qualite']))      $errors['declarant_qualite'] = 'La qualité du déclarant est requise.';

        return $errors;
    }

    private function getArrondissements(): array
    {
        // Si superviseur : retourner uniquement son arrondissement
        $arrondissementId = $this->arrondissementId();
        if ($arrondissementId !== null) {
            $stmt = Database::getConnection()->prepare('SELECT * FROM arrondissements WHERE id = ?');
            $stmt->execute([$arrondissementId]);
            return $stmt->fetchAll();
        }

        $stmt = Database::getConnection()->query('SELECT * FROM arrondissements ORDER BY numero ASC');
        return $stmt->fetchAll();
    }

    private function getTemoins(string $acteId): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM temoins WHERE type_acte = 'NAISSANCE' AND acte_id = ? ORDER BY ordre ASC"
        );
        $stmt->execute([$acteId]);
        return $stmt->fetchAll();
    }
}
