<?php

namespace App\Admin\Extensions\Nav;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class Dropdown implements Renderable
{
  public function render()
  {
    $u = Auth::user();
    $links = [];

    if ($u->isRole('super-admin')) {
      $links =  [
        [
          'icon' => 'building',
          'url' => admin_url('enterprises'),
          'title' => 'Enterprises',
        ],
        [
          'icon' => 'users',
          'url' => admin_url('auth/users'),
          'title' => 'Users',
        ],
      ];
    }


    if ($u->isRole('dos')) {
      $links = [
 
        [
          'url' => admin_url('students'),
          'icon' => 'users',
          'title' => 'Students',
        ],
        [
          'url' => admin_url('teachers'),
          'icon' => 'graduation-cap',
          'title' => 'Teachers',
        ],
        [
          'url' => admin_url('classes'),
          'icon' => 'building-o',
          'title' => 'Classes',
        ],
        [
          'url' => admin_url('marks'),
          'icon' => 'check',
          'title' => 'Marks',
        ],
        [
          'url' => admin_url('marks'),
          'icon' => 'line-chart',
          'title' => 'Marks',
        ],
      ];
    }


    if ($u->isRole('admin')) {
      $links = [
 
  
        [
          'url' => admin_url('teachers'),
          'icon' => 'graduation-cap',
          'title' => 'Human resource',
        ],
 
  
      ];
    }

    if ($u->isRole('bursar')) {
      $links = [
        [
          'icon' => 'money',
          'url' => admin_url('fees'),
          'title' => 'Fees',
        ],
        [
          'url' => admin_url('transactions'),
          'icon' => 'balance-scale',
          'title' => 'Transactions',
        ],
        [
          'url' => admin_url('accounts'),
          'icon' => 'calculator',
          'title' => 'Accounts',
        ],
   
      ];
    }

    return view('widgets/dropdown', [
      'links' => $links
    ]);
  }
}
