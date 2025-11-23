<?php

namespace App\Livewire\Frontend\Assistance;

use App\Models\Helpdesk\KbArticle;
use App\Models\Helpdesk\KbCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.frontend')]
class Index extends Component
{
    public $search = '';
    public function render()
    {
        return view('livewire.frontend.assistance.index', [
            'categories' => KbCategory::withCount('articles')->orderBy('order')->get(),
            'popularArticles' => KbArticle::orderByDesc('views_count')->take(5)->get(),
            'results' => !empty($this->search) ? KbArticle::search($this->search)->take(5)->get() : [],
        ]);
    }
}
