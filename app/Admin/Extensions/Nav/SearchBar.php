<?php

namespace App\Admin\Extensions\Nav;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class SearchBar implements Renderable
{
    public function render()
    {
        $u = Auth::user();
        return view('admin.search-bar', [
            'u' => $u
        ])->render();
    }
}
