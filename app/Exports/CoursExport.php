<?php

namespace App\Exports;

use App\Models\Cours;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoursExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Cours::with(['formateur', 'categorie', 'niveau'])
            ->get()
            ->map(function ($cours) {
                return [
                    $cours->id,
                    $cours->titre,
                    $cours->formateur?->prenom . ' ' . $cours->formateur?->nom ?? '-',
                    $cours->categorie?->nom ?? '-',
                    $cours->niveau?->libelle ?? '-',
                    number_format($cours->prix, 0, ',', ' ') . ' FCFA',
                    $cours->est_gratuit ? 'Oui' : 'Non',
                    $cours->est_certifiant ? 'Oui' : 'Non',
                    ucfirst($cours->statut),
                    $cours->nb_apprenants,
                    $cours->created_at->format('d/m/Y'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID', 'Titre', 'Formateur', 'Catégorie', 'Niveau',
            'Prix', 'Gratuit', 'Certifiant', 'Statut', 'Apprenants', 'Créé le'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}