<?php

namespace App\Console\Commands;

use App\Models\Lecon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Déplace les médias de leçon du disque public vers le disque privé et
 * normalise les chemins stockés en base.
 *
 * Avant cette commande, les PDF et vidéos vivaient dans storage/app/public et
 * étaient donc téléchargeables par simple devinette d'URL, y compris pour des
 * cours payants.
 *
 * La base mélange trois formats hérités :
 *   - `/storage/cours/x.pdf`   (données seedées, préfixe inclus)
 *   - `lecons/pdfs/x.pdf`      (téléversement Filament)
 *   - `https://youtube.com/...`(lien externe, laissé intact)
 *
 * Idempotente : relancer la commande ne fait rien de plus.
 *
 *   php artisan lecons:securiser-medias --dry-run
 *   php artisan lecons:securiser-medias
 */
class SecuriserMediasLecons extends Command
{
    protected $signature = 'lecons:securiser-medias
                            {--dry-run : Affiche ce qui serait fait sans rien modifier}';

    protected $description = 'Déplace les médias de leçon vers le disque privé et normalise les chemins';

    public function handle(): int
    {
        $simulation = (bool) $this->option('dry-run');

        if ($simulation) {
            $this->warn('MODE SIMULATION — aucun fichier ni enregistrement ne sera modifié.');
            $this->newLine();
        }

        $public = Storage::disk('public');
        $prive = Storage::disk(Lecon::DISQUE_PRIVE);

        $deplaces = 0;
        $normalises = 0;
        $externes = 0;
        $introuvables = [];

        foreach (Lecon::query()->cursor() as $lecon) {
            foreach (['pdf' => 'url_pdf', 'video' => 'url_video'] as $type => $colonne) {
                $brut = $lecon->valeurMediaBrute($type);

                if ($brut === null) {
                    continue;
                }

                if (! $lecon->estMediaHeberge($type)) {
                    $externes++;
                    continue;
                }

                $cible = $lecon->cheminMediaRelatif($type);

                if ($cible === null) {
                    continue;
                }

                // 1. Le fichier est déjà en place sur le disque privé.
                $dejaPrive = $prive->exists($cible);

                // 2. Sinon, on le récupère depuis le disque public.
                if (! $dejaPrive) {
                    if ($public->exists($cible)) {
                        if (! $simulation) {
                            $prive->put($cible, $public->get($cible));
                            $public->delete($cible);
                        }
                        $this->line("  déplacé   : {$cible}");
                        $deplaces++;
                    } else {
                        $introuvables[] = "leçon #{$lecon->id} ({$type}) : {$brut}";
                        continue;
                    }
                }

                // 3. Normalisation du chemin stocké (`/storage/...` → relatif).
                if ($brut !== $cible) {
                    if (! $simulation) {
                        $lecon->forceFill([$colonne => $cible])->save();
                    }
                    $this->line("  normalisé : {$brut}  →  {$cible}");
                    $normalises++;
                }
            }
        }

        $this->newLine();
        $this->table(
            ['Fichiers déplacés', 'Chemins normalisés', 'Liens externes ignorés', 'Fichiers introuvables'],
            [[$deplaces, $normalises, $externes, count($introuvables)]],
        );

        if ($introuvables !== []) {
            $this->newLine();
            $this->warn('Ces médias sont référencés en base mais absents des deux disques :');
            foreach ($introuvables as $manquant) {
                $this->line('  - ' . $manquant);
            }
            $this->line('Leur chemin a été laissé tel quel : à corriger depuis le back-office.');
        }

        if ($simulation) {
            $this->newLine();
            $this->info('Relancez sans --dry-run pour appliquer.');
        }

        return self::SUCCESS;
    }
}
