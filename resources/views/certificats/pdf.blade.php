{{--
    Certificat de réussite — A4 paysage, rendu par dompdf.

    dompdf ne gère ni flexbox ni grid : la mise en page repose sur des blocs en
    position absolue (millimètres) et sur une table pour les signatures. Toutes
    les images arrivent en data URI, préparées par CertificatService.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat {{ $code }}</title>
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1E293B; }

        .fond { position: absolute; top: 0; left: 0; width: 297mm; height: 210mm; background-color: #FFFDF6; }
        .cadre { position: absolute; top: 7mm; left: 7mm; width: 282mm; height: 195mm; border: 2.4pt solid #0F2A4A; }
        .filet { position: absolute; top: 10.5mm; left: 10.5mm; width: 275mm; height: 188mm; border: 0.8pt solid #C9A227; }

        .centre { position: absolute; left: 20mm; width: 257mm; text-align: center; }

        .logo { top: 17mm; }
        .logo img { height: 14mm; }

        .titre { top: 35mm; font-family: 'DejaVu Serif', serif; font-size: 34pt; font-weight: bold; letter-spacing: 5pt; color: #0F2A4A; }
        .sous-titre { top: 52mm; font-size: 12pt; letter-spacing: 6pt; color: #A8841A; }

        .mention { font-size: 11pt; color: #475569; }
        .decerne { top: 66mm; }
        .titulaire { top: 74mm; font-family: 'DejaVu Serif', serif; font-size: 26pt; font-weight: bold; color: #0F172A; }
        .trait { position: absolute; top: 90mm; left: 88.5mm; width: 120mm; border-top: 0.8pt solid #C9A227; }
        .motif { top: 95mm; }
        .formation { top: 103mm; font-size: 17pt; font-weight: bold; color: #0F2A4A; line-height: 1.25; }
        .details { top: 124mm; font-size: 10pt; color: #475569; }

        .signatures { position: absolute; top: 139mm; border-collapse: collapse; }
        .signatures td { vertical-align: bottom; text-align: center; padding: 0 4mm; }
        .signature-image { height: 19mm; }
        .signature-image img { height: 18mm; }
        .signature-ligne { border-top: 0.7pt solid #334155; margin: 1mm 3mm 1.5mm 3mm; }
        .signature-nom { font-size: 10pt; font-weight: bold; color: #0F172A; }
        .signature-fonction { font-size: 8.5pt; color: #64748B; }

        .pied { position: absolute; top: 185mm; left: 20mm; width: 225mm; font-size: 7.5pt; color: #64748B; line-height: 1.5; }
        .qr { position: absolute; top: 173mm; left: 257mm; width: 22mm; height: 22mm; }

        .revoque { position: absolute; top: 86mm; left: 30mm; width: 237mm; text-align: center; font-size: 64pt; font-weight: bold; letter-spacing: 6pt; color: #DC2626; opacity: 0.2; transform: rotate(-16deg); }
    </style>
</head>
<body>
    <div class="fond"></div>
    <div class="cadre"></div>
    <div class="filet"></div>

    @if ($logo)
        <div class="centre logo"><img src="{{ $logo }}" alt="DARASI"></div>
    @endif

    <div class="centre titre">CERTIFICAT</div>
    <div class="centre sous-titre">DE RÉUSSITE</div>

    <div class="centre mention decerne">Ce certificat est décerné à</div>
    <div class="centre titulaire">{{ $titulaire }}</div>
    <div class="trait"></div>

    <div class="centre mention motif">pour avoir suivi et validé avec succès la formation</div>
    <div class="centre formation">{{ $cours }}</div>

    <div class="centre details">
        @if ($note !== null)
            Note obtenue à l'examen final : {{ $note }}/20 &nbsp;·&nbsp;
        @endif
        Délivré le {{ $dateEmission }}
    </div>

    @php
        $nombre = count($signatures);
        // Largeur totale selon le nombre de signataires, table centrée. À trois,
        // elle s'arrête avant le QR code (x = 257 mm).
        $largeur = [1 => 80, 2 => 160, 3 => 210][$nombre] ?? 0;
        $gauche = (297 - $largeur) / 2;
    @endphp

    @if ($nombre > 0)
        <table class="signatures" style="left: {{ $gauche }}mm; width: {{ $largeur }}mm;">
            <tr>
                @foreach ($signatures as $signature)
                    <td style="width: {{ round(100 / $nombre, 2) }}%;">
                        <div class="signature-image">
                            @if ($signature['image'])
                                <img src="{{ $signature['image'] }}" alt="">
                            @endif
                        </div>
                        <div class="signature-ligne"></div>
                        <div class="signature-nom">{{ $signature['nom'] }}</div>
                        <div class="signature-fonction">{{ $signature['fonction'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <div class="pied">
        Certificat n° <strong>{{ $code }}</strong><br>
        Authenticité vérifiable sur {{ $urlVerification }}
    </div>

    @if ($qrCode)
        <img class="qr" src="{{ $qrCode }}" alt="">
    @endif

    @unless ($estValide)
        <div class="revoque">RÉVOQUÉ</div>
    @endunless
</body>
</html>
