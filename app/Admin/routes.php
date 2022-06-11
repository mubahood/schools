<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->resource('employees', EmployeesController::class);

    $router->get('/', 'HomeController@index')->name('home');
    $router->resources([
        'enterprises' => EnterpriseController::class
    ]);
});
