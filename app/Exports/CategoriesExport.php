<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    protected $sort;
    protected $sortDirection;

    public function __construct($sort = 'name', $sortDirection = 'asc')
    {
        $this->sort = $sort;
        $this->sortDirection = $sortDirection;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Category::withCount('products')
            ->orderBy($this->sort, $this->sortDirection)
            ->get()
            ->map(function ($category) {
                return [
                    'ID' => $category->id,
                    'Nombre' => $category->name,
                    'Código' => $category->code,
                    'Productos' => $category->products_count,
                    'Fecha creación' => $category->created_at ? $category->created_at->format('Y-m-d H:i') : '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Código',
            'Productos',
            'Fecha creación'
        ];
    }
}
