<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Naissance;
use App\Models\Mariage;
use App\Models\Deces;
use App\Models\AuditLog;
use App\Core\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class DocumentController extends Controller
{
    private const TYPES_VALIDES = ['naissance', 'mariage', 'deces'];

    public function generate(Request $request): void
    {
        $type = strtolower($request->param('type'));
        $id   = $request->param('id');

        if (!in_array($type, self::TYPES_VALIDES, true)) {
            $this->abort(404);
        }

        $acte = $this->loadActe($type, $id);
        if (!$acte) {
            $this->abort(404);
        }

        // Vérification isolation arrondissement
        $arrondissementId = $this->arrondissementId();
        if ($arrondissementId !== null && (int) $acte['arrondissement_id'] !== $arrondissementId) {
            $this->abort(403);
        }

        $temoins = $this->getTemoins($type, $id);

        // Génération du QR code (data URI SVG)
        $qrContent = sprintf(
            'ÉTAT CIVIL COTONOU | %s | N°%s/%s | %s | ID:%s',
            strtoupper($type),
            $acte['numero_acte'],
            $acte['annee'],
            $acte['arrondissement_nom'] ?? '',
            substr($id, 0, 8)
        );
        $qrDataUri = $this->generateQrDataUri($qrContent);

        // Génération du HTML depuis un template
        ob_start();
        $config = [
            'mairie_nom'  => $_ENV['MAIRIE_NOM'] ?? 'Mairie de Cotonou',
            'mairie_logo' => BASE_PATH . '/' . ($_ENV['MAIRIE_LOGO'] ?? ''),
            'qr_data_uri' => $qrDataUri,
        ];
        include BASE_PATH . "/app/Views/actes/pdf/{$type}.php";
        $html = ob_get_clean();

        // Configuration Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Traçabilité
        $this->logDocument($type, $id);

        $filename = "acte_{$type}_{$acte['numero_acte']}_{$acte['annee']}.pdf";
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private function generateQrDataUri(string $content): string
    {
        $options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'   => QRCode::ECC_M,
            'scale'      => 4,
            'imageBase64'=> true,
        ]);

        $svg = (new QRCode($options))->render($content);
        // Dompdf supporte les data URI SVG base64
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function loadActe(string $type, string $id): ?array
    {
        return match ($type) {
            'naissance' => Naissance::findWithDetails($id),
            'mariage'   => Mariage::findWithDetails($id),
            'deces'     => Deces::findWithDetails($id),
            default     => null,
        };
    }

    private function getTemoins(string $type, string $id): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM temoins WHERE type_acte = ? AND acte_id = ? ORDER BY ordre ASC"
        );
        $stmt->execute([strtoupper($type), $id]);
        return $stmt->fetchAll();
    }

    private function logDocument(string $type, string $id): void
    {
        AuditLog::log('GENERATE_PDF', strtoupper($type), $id);

        $typeDoc = match ($type) {
            'naissance' => 'ACTE_NAISSANCE',
            'mariage'   => 'ACTE_MARIAGE',
            'deces'     => 'ACTE_DECES',
            default     => 'INCONNU',
        };

        $stmt = Database::getConnection()->prepare(
            "INSERT INTO documents_generes (id, type_acte, acte_id, type_document, genere_par, genere_le)
             VALUES (UUID(), ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([strtoupper($type), $id, $typeDoc, $this->user()['id']]);
    }
}
