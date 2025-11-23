<?php

namespace App\Observers\Helpdesk;

use Illuminate\Database\Eloquent\Model;
use Str;

class SlugObserver
{
    public function saving(Model $model): void
    {
        // Génération automatique du SLUG pour le SEO
        if (empty($model->slug) && !empty($model->title)) {
            $baseSlug = Str::slug($model->title);
            $slug = $baseSlug;
            $count = 1;

            // Gestion basique des doublons (ex: mon-article-1)
            // Note: Pour une très grosse base, on ferait une requête plus optimisée
            while ($model::where('slug', $slug)->where('id', '!=', $model->id ?? 0)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }

            $model->slug = $slug;
        }

        // Pour le Blog : Calcul du temps de lecture estimé
        if (isset($model->content) && in_array('reading_time', $model->getFillable())) {
            $wordCount = str_word_count(strip_tags($model->content));
            // Moyenne de lecture : 200 mots / minute
            $minutes = ceil($wordCount / 200);
            $model->reading_time = max(1, $minutes);
        }
    }
}
