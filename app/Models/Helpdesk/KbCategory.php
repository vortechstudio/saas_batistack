<?php

namespace App\Models\Helpdesk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KbCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function articles(): HasMany
    {
        return $this->hasMany(KbArticle::class);
    }
}
