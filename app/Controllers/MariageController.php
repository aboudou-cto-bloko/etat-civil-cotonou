<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\Mariage;
use App\Models\AuditLog;

class MariageController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'nom'          => $request->get('nom', ''),
            'numero_acte'  => $request->get('numero_acte', ''),
            'annee'        => $request->get('annee', ''),
            'type_mariage' => $request->get('type_mariage', ''),
        ];
        $sort      = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $page      = max(1, (int) $request->get('page', '1'));
        $resultats = Mariage::search($filters, $this->arrondissementId(), 20, $page, $sort, $direction);

        $this->render('actes/mariages/index', [
            'title'     => 'Actes de mariage',
            'resultats' => $resultats,
            'filters'   => $filters,
            'sort'      => $sort,
            'direction' => $direction,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->render('actes/mariages/form', [
            'title'           => 'Nouveau acte de mariage',
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
            $this->render('actes/mariages/form', [
                'title'           => 'Nouveau acte de mariage',
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
        $data['numero_acte'] = Mariage::prochainNumeroActe((int) $data['arrondissement_id'], $annee);
        $data['enregistre_par']         = $this->user()['id'];
        $data['officier_etat_civil_id'] = $this->user()['id'];
        $data['statut']                 = 'ACTIF';

        $id = Mariage::insert($data);
        AuditLog::log('CREATE', 'MARIAGE', $id, null, $data);

        $this->flash('success', "Acte de mariage n°{$data['numero_acte']}/{$annee} enregistré avec succès.");
        $this->redirect('/mariages/' . $id);
    }

    public function show(Request $request): void
    {
        $acte    = $this->findAndAuthorize($request->param('id'));
        $temoins = $this->getTemoins($acte['id']);

        $this->render('actes/mariages/show', [
            'title'   => 'Acte de mariage n°' . $acte['numero_acte'] . '/' . $acte['annee'],
            'acte'    => $acte,
            'temoins' => $temoins,
            'flash'   => $this->getFlash(),
        ]);
    }

    public function edit(Request $request): void
    {
        $acte = $this->findAndAuthorize($request->param('id'));

        $this->render('actes/mariages/form', [
            'title'           => 'Modifier acte de mariage',
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
            $this->render('actes/mariages/form', [
                'title'           => 'Modifier acte de mariage',
                'arrondissements' => $this->getArrondissements(),
                'acte'            => array_merge($acte, $data),
                'errors'          => $errors,
            ]);
            return;
        }

        $data['modifie_par'] = $this->user()['id'];
        AuditLog::log('UPDATE', 'MARIAGE', $acte['id'], $acte, $data);

        Mariage::update($acte['id'], $data);
        $this->flash('success', 'Acte de mariage modifié avec succès.');
        $this->redirect('/mariages/' . $acte['id']);
    }

    private function findAndAuthorize(string $id): array
    {
        $acte = Mariage::findWithDetails($id);

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
            'date_mariage'                => $request->post('date_mariage'),
            'heure_mariage'               => $request->post('heure_mariage') ?: null,
            'lieu_celebration'            => trim($request->post('lieu_celebration', '')),
            'type_mariage'                => $request->post('type_mariage', 'MONOGAMIQUE'),
            'regime_matrimonial'          => trim($request->post('regime_matrimonial', '')),
            'epoux_nom'                   => strtoupper(trim($request->post('epoux_nom', ''))),
            'epoux_prenom'                => trim($request->post('epoux_prenom', '')),
            'epoux_date_naissance'        => $request->post('epoux_date_naissance'),
            'epoux_lieu_naissance'        => trim($request->post('epoux_lieu_naissance', '')),
            'epoux_nationalite'           => trim($request->post('epoux_nationalite', 'Béninoise')),
            'epoux_profession'            => trim($request->post('epoux_profession', '')),
            'epoux_domicile'              => trim($request->post('epoux_domicile', '')),
            'epoux_statut_anterieur'      => $request->post('epoux_statut_anterieur', 'CÉLIBATAIRE'),
            'epoux_pere_nom_prenom'       => trim($request->post('epoux_pere_nom_prenom', '')),
            'epoux_mere_nom_prenom'       => trim($request->post('epoux_mere_nom_prenom', '')),
            'epouse_nom'                  => strtoupper(trim($request->post('epouse_nom', ''))),
            'epouse_prenom'               => trim($request->post('epouse_prenom', '')),
            'epouse_date_naissance'       => $request->post('epouse_date_naissance'),
            'epouse_lieu_naissance'       => trim($request->post('epouse_lieu_naissance', '')),
            'epouse_nationalite'          => trim($request->post('epouse_nationalite', 'Béninoise')),
            'epouse_profession'           => trim($request->post('epouse_profession', '')),
            'epouse_domicile'             => trim($request->post('epouse_domicile', '')),
            'epouse_statut_anterieur'     => $request->post('epouse_statut_anterieur', 'CÉLIBATAIRE'),
            'epouse_pere_nom_prenom'      => trim($request->post('epouse_pere_nom_prenom', '')),
            'epouse_mere_nom_prenom'      => trim($request->post('epouse_mere_nom_prenom', '')),
            'date_publication_bans'       => $request->post('date_publication_bans') ?: null,
            'lieu_publication_bans'       => trim($request->post('lieu_publication_bans', '')),
            'opposition_recue'            => $request->post('opposition_recue') ? 1 : 0,
            'detail_opposition'           => trim($request->post('detail_opposition', '')),
            'consentement_parents_epoux'  => $request->post('consentement_parents_epoux') ? 1 : 0,
            'consentement_parents_epouse' => $request->post('consentement_parents_epouse') ? 1 : 0,
            'autorisation_judiciaire'     => $request->post('autorisation_judiciaire') ? 1 : 0,
            'observations'                => trim($request->post('observations', '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['epoux_nom']))             $errors['epoux_nom']             = "Le nom de l'époux est requis.";
        if (empty($data['epoux_prenom']))          $errors['epoux_prenom']          = "Le prénom de l'époux est requis.";
        if (empty($data['epoux_date_naissance']))  $errors['epoux_date_naissance']  = "La date de naissance de l'époux est requise.";
        if (empty($data['epouse_nom']))            $errors['epouse_nom']            = "Le nom de l'épouse est requis.";
        if (empty($data['epouse_prenom']))         $errors['epouse_prenom']         = "Le prénom de l'épouse est requis.";
        if (empty($data['epouse_date_naissance'])) $errors['epouse_date_naissance'] = "La date de naissance de l'épouse est requise.";
        if (empty($data['date_mariage']))          $errors['date_mariage']          = "La date du mariage est requise.";
        if (empty($data['lieu_celebration']))      $errors['lieu_celebration']      = "Le lieu de célébration est requis.";
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
            "SELECT * FROM temoins WHERE type_acte = 'MARIAGE' AND acte_id = ? ORDER BY ordre ASC"
        );
        $stmt->execute([$acteId]);
        return $stmt->fetchAll();
    }
}
