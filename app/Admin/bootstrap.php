<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Facades\Admin;
use App\Admin\Extensions\Nav\Shortcut;
use App\Admin\Extensions\Nav\Dropdown;

Encore\Admin\Form::forget(['map', 'editor']);

Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {

    $navbar->left(view('admin.search-bar'));

    $navbar->left(Shortcut::make([
        'Posts' => 'posts/create',
        'Users' => 'users/create',
        'Images' => 'images/create',
        'Videos' => 'videos/create',
        'Articles' => 'articles/create',
        'Tags' => 'tags/create',
        'Categories' => 'categories/create',
    ], 'fa-plus')->title('Create'));

    $navbar->left(new Dropdown());

    $navbar->right(new \App\Admin\Extensions\Nav\Links());
});

Admin::css(url('/assets/bootstrap.css')); 
Admin::css('/assets/styles.css');
