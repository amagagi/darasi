<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithStyles
{
    protected $role;

    public function __construct($role = null)
    {
        $this->role = $role;
    }

    public function collection()
    {
        $query = User::select('id', 'prenom', 'nom', 'email', 'telephone', 'role', 'is_active', 'created_at');
        
        if ($this->role) {
            $query->where('role', $this->role);
        }
        
        return $query->get()->map(function ($user) {
            return [
                $user->id,
                $user->prenom,
                $user->nom,
                $user->email,
                $user->telephone ?? '-',
                ucfirst($user->role),
                $user->is_active ? 'Actif' : 'Inactif',
                $user->created_at->format('d/m/Y'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Rôle', 'Statut', 'Date création'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}