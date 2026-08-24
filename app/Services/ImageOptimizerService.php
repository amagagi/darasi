<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Redimensionnement et génération de miniature pour les images uploadées via
 * un FileUpload Filament (disque "public"). Point d'accroche prévu :
 * ->afterStateUpdated() sur le composant FileUpload, une fois le fichier
 * effectivement stocké.
 *
 * No-op sur les SVG : vectoriel, non concerné par un redimensionnement raster.
 */
class ImageOptimizerService
{
    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Redimensionne en place le fichier si sa largeur dépasse $maxWidth,
     * proportions conservées. N'agrandit jamais une image plus petite.
     *
     * Ne lève jamais d'exception : un échec de traitement d'image ne doit
     * jamais faire échouer l'upload lui-même (juste laisser l'original tel
     * quel), donc toute erreur est avalée et journalisée.
     */
    public function optimize(string $relativePath, int $maxWidth = 1920): void
    {
        if ($this->estSvg($relativePath)) {
            return;
        }

        try {
            $absolutePath = Storage::disk('public')->path($relativePath);

            $image = $this->manager->read($absolutePath);
            $image->scale(width: $maxWidth);
            $image->save($absolutePath);
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService::optimize a échoué', [
                'path' => $relativePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Génère une miniature "{nom}_thumb.{ext}" à côté de l'original, sur le
     * même disque, et renvoie son chemin relatif. Renvoie null sur SVG (rien
     * à générer, l'original vectoriel sert déjà de miniature) ou en cas
     * d'échec de traitement — jamais d'exception, mêmes raisons que optimize().
     */
    public function thumbnail(string $relativePath, int $width = 400): ?string
    {
        if ($this->estSvg($relativePath)) {
            return null;
        }

        try {
            $absolutePath = Storage::disk('public')->path($relativePath);
            $thumbRelativePath = preg_replace('/(\.[^.]+)$/', '_thumb$1', $relativePath);
            $thumbAbsolutePath = Storage::disk('public')->path($thumbRelativePath);

            $image = $this->manager->read($absolutePath);
            $image->scale(width: $width);
            $image->save($thumbAbsolutePath);

            return $thumbRelativePath;
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizerService::thumbnail a échoué', [
                'path' => $relativePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function estSvg(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg';
    }
}
