<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Village;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public function index()
    {
        $qrCodes = QrCode::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.qrcodes.index', compact('qrCodes'));
    }

    public function create()
    {
        $villages = Village::where('is_active', true)->orderBy('name')->get();
        return view('admin.qrcodes.create', compact('villages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
        ]);

        // Générer un code unique (avec vérification d'unicité)
        do {
            $code = strtoupper(Str::random(10));
        } while (QrCode::where('code', $code)->exists());

        // URL de scan qui redirigera vers WhatsApp (permet de tracker les scans)
        $scanUrl = url("/qr/{$code}");

        // Générer le QR Code avec la nouvelle API Endroid v6.0
        try {
            $builder = new Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $scanUrl,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 500,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );

            $result = $builder->build();

            // Sauvegarder l'image
            $filename = 'qr-' . $code . '.png';
            $path = 'qrcodes/' . $filename;

            Storage::disk('public')->put($path, $result->getString());

            $validated['code']          = $code;
            $validated['qr_image_path'] = $path;
        } catch (\Exception $e) {
            Log::error('QR Code generation error: ' . $e->getMessage(), [
                'code' => $code,
                'source' => $validated['source']
            ]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la génération du QR Code : ' . $e->getMessage()]);
        }

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['scan_count'] = 0;

        QrCode::create($validated);

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code créé avec succès !');
    }

    public function show(QrCode $qrcode)
    {
        return view('admin.qrcodes.show', compact('qrcode'));
    }

    public function edit(QrCode $qrcode)
    {
        $villages = Village::where('is_active', true)->orderBy('name')->get();
        return view('admin.qrcodes.edit', compact('qrcode', 'villages'));
    }

    public function update(Request $request, QrCode $qrcode)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $qrcode->update($validated);

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code mis à jour avec succès !');
    }

    public function destroy(QrCode $qrcode)
    {
        // Supprimer l'image
        if ($qrcode->qr_image_path) {
            Storage::disk('public')->delete($qrcode->qr_image_path);
        }

        $qrcode->delete();

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code supprimé avec succès !');
    }

    public function download(QrCode $qrcode)
    {
        if (! $qrcode->qr_image_path || ! Storage::disk('public')->exists($qrcode->qr_image_path)) {
            return redirect()->back()->with('error', 'Image QR Code introuvable');
        }

        return Storage::disk('public')->download($qrcode->qr_image_path, 'qrcode-' . $qrcode->source . '.png');
    }

    /**
     * Endpoint public pour scanner un QR code (incrémente le compteur)
     * Route: GET /qr/{code}
     */
    public function scan($code)
    {
        $qrCode = QrCode::where('code', strtoupper($code))->with('village')->first();

        if (! $qrCode) {
            abort(404, 'QR Code invalide');
        }

        // Incrémenter le compteur uniquement si le QR code est actif
        if ($qrCode->is_active) {
            $qrCode->incrementScan();
        }

        // Numéro WhatsApp du bot (sans le +)
        $whatsappNumber = '243841622222';

        // Générer le message selon le village ou la source du QR code
        $message = $this->generateStartMessage($qrCode);

        // Rediriger vers WhatsApp avec le message pré-rempli
        // Format: https://wa.me/NUMERO?text=MESSAGE
        return redirect("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    /**
     * Générer le message de démarrage selon le QR code
     */
    protected function generateStartMessage(QrCode $qrCode)
    {
        // Si un village est sélectionné, utiliser le format START_AFF_{VILLAGE}
        if ($qrCode->village) {
            $villageName = strtoupper(str_replace(' ', '_', trim($qrCode->village->name)));
            // Limiter à 12 caractères pour matcher avec le flow Twilio
            $villageName = substr($villageName, 0, 12);
            return "START_AFF_{$villageName}";
        }

        // Sinon, utiliser le mapping basé sur la source
        $source = strtoupper(str_replace(' ', '_', trim($qrCode->source)));

        // Mapper les sources vers les commandes du Flow Twilio Studio
        $sourceMap = [
            // Affiches par village
            'AFFICHE_GOMBE' => 'START_AFF_GOMBE',
            'AFFICHE_MASINA' => 'START_AFF_MASINA',
            'AFFICHE_LEMBA' => 'START_AFF_LEMBA',
            'AFFICHE_BANDA' => 'START_AFF_BANDA',
            'AFFICHE_NGALIEMA' => 'START_AFF_NGALI',
            'GOMBE' => 'START_AFF_GOMBE',
            'MASINA' => 'START_AFF_MASINA',
            'LEMBA' => 'START_AFF_LEMBA',
            'BANDA' => 'START_AFF_BANDA',
            'NGALIEMA' => 'START_AFF_NGALI',

            // Points de vente partenaires
            'PDV_BRACONGO' => 'START_PDV_BRACONGO',
            'PDV_VODACOM' => 'START_PDV_VODACOM',
            'PDV_ORANGE' => 'START_PDV_ORANGE',
            'PDV_AIRTEL' => 'START_PDV_AIRTEL',
            'BRACONGO' => 'START_PDV_BRACONGO',
            'VODACOM' => 'START_PDV_VODACOM',
            'ORANGE' => 'START_PDV_ORANGE',
            'AIRTEL' => 'START_PDV_AIRTEL',

            // Digital
            'FACEBOOK' => 'START_FB',
            'INSTAGRAM' => 'START_IG',
            'TIKTOK' => 'START_TIKTOK',
            'WHATSAPP_STATUS' => 'START_WA_STATUS',
            'FB' => 'START_FB',
            'IG' => 'START_IG',

            // Flyers
            'FLYER_UNIVERSITE' => 'START_FLYER_UNI',
            'FLYER_RUE' => 'START_FLYER_RUE',
            'FLYER_EVENEMENT' => 'START_FLYER_EVENT',
            'UNIVERSITE' => 'START_FLYER_UNI',
            'RUE' => 'START_FLYER_RUE',
            'EVENEMENT' => 'START_FLYER_EVENT',
        ];

        // Retourner le message mappé ou un message par défaut
        return $sourceMap[$source] ?? 'START_AFF_GOMBE';
    }
}
