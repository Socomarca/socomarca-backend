<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
    ];

    /**
     * @var array
     * Allowed filters for the filter scope.
     */
    protected $allowedFilters = [
        [
            'field' => 'question',
            'operators' => ['=', '!=', 'LIKE', 'ILIKE', 'NOT LIKE', 'fulltext'],
        ],
        [
            'field' => 'answer',
            'operators' => ['=', '!=', 'LIKE', 'ILIKE', 'NOT LIKE', 'fulltext'],
        ],
    ];

    /**
     * @var array
     * Allowed sorts for the filter scope.
     */
    protected $allowedSorts = [
        'question',
        'answer',
        'created_at',
        'updated_at',
    ];

    /**
     * Scope a query to filter FAQs based on given criteria
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $filter) {
            $field = array_find($this->allowedFilters, function ($item) use ($filter) {
                return $item['field'] === $filter['field'];
            });

            if ($field !== null) {
                $value = $filter['value'];
                $operator = array_find($field['operators'], function ($item) use ($filter) {
                    return $item === $filter['operator'];
                });

                if ($operator !== null && $operator !== 'fulltext') {
                    $query->where($field['field'], $operator, $value);
                } elseif ($operator === 'fulltext') {
                    // Búsqueda de texto completo en preguntas y respuestas
                    $query->where(function ($subQuery) use ($value) {
                        $subQuery->where('question', 'ILIKE', "%{$value}%")
                                 ->orWhere('answer', 'ILIKE', "%{$value}%");
                    });
                }

                if (
                    isset($filter['sort'])
                    && in_array($filter['field'], $this->allowedSorts)
                    && in_array($filter['sort'], ['ASC', 'DESC'])
                ) {
                    $query->orderBy($filter['field'], $filter['sort']);
                }
            }
        }

        return $query;
    }

    /**
     * Scope for natural search in both question and answer
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('question', 'ILIKE', "%{$search}%")
              ->orWhere('answer', 'ILIKE', "%{$search}%");
        });
    }
}
