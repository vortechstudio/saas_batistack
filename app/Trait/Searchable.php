<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to search for a term in title and content.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");

            // Si le modèle a un champ 'excerpt' (résumé), on cherche dedans aussi
            if (in_array('excerpt', $this->fillable)) {
                $q->orWhere('excerpt', 'like', "%{$term}%");
            }
        });
    }
}
