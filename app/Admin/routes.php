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
    $router->resource('academic-years', AcademicYearController::class);
    $router->resource('terms', TermController::class);
    $router->resource('courses', CourseController::class);
    $router->resource('classes', AcademicClassController::class);
    $router->resource('subjects', SubjectController::class);
    $router->resource('students-classes', StudentHasClassController::class);
    $router->resource('exams', ExamController::class);
    $router->resource('marks', MarkController::class);
    $router->resource('termly-report-cards', TermlyReportCardController::class);
    $router->resource('grading-scales', GradingScaleController::class);
    $router->resource('student-report-cards', StudentReportCardController::class);
    $router->resource('demo', DemoController::class);
    $router->resource('accounts', AccountController::class);
    $router->resource('fees', AcademicClassFeeController::class);
    $router->resource('transactions', TransactionController::class);
    $router->resource('school-fees-payment', SchoolFeesPaymentController::class);
    //$router->resource('fees', StudentHasFeeController::class);

    $router->get('/', 'HomeController@index')->name('home');
    $router->resources([
        'enterprises' => EnterpriseController::class
    ]);
});
