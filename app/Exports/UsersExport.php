<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    protected $sort;
    protected $sortDirection;

    public function __construct($sort = 'name', $sortDirection = 'asc')
    {
        $this->sort = $sort;
        $this->sortDirection = $sortDirection;
    }

    public function collection()
    {
        return User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'cliente');
            })
            ->orderBy($this->sort, $this->sortDirection)
            ->get()
            ->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'Nombre' => $user->name,
                    'Email' => $user->email,
                    'Teléfono' => $user->phone,
                    'Activo' => $user->is_active ? 'Sí' : 'No',
                    
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Email',
            'Teléfono',
            'Activo',
            
        ];
    }
}
