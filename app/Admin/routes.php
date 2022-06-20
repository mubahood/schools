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
    $router->resource('books-categories', BooksCategoryController::class);
    $router->resource('book-authors', BookAuthorController::class);
    $router->resource('books', BookController::class);
    $router->resource('students', StudentsController::class);
    $router->resource('book-borrows', BookBorrowController::class);

    $router->get('/', 'HomeController@index')->name('home');
    $router->resources([
        'enterprises' => EnterpriseController::class
    ]);
});
