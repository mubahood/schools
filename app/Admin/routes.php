<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->resource('employees-batch-importers', EmployeesBatchImporterController::class);
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
    $router->resource('menu-items', MenuItemController::class);
    $router->resource('main-courses', MainCourseController::class);
    $router->resource('user-batch-importers', UserBatchImporterController::class);
    $router->resource('user-photos-batch-importers', UserPhotosBatchImporterController::class);
    $router->resource('fund-requisitions', FundRequisitionController::class);
    $router->resource('stock-item-categories', StockItemCategoryController::class);
    $router->resource('stock-batches', StockBatchController::class);
    $router->resource('suppliers', SuppliersController::class);
    $router->resource('stock-records', StockRecordController::class);
    $router->resource('services', ServiceController::class);
    $router->resource('service-subscriptions', ServiceSubscriptionController::class);
    $router->resource('theology-classes', TheologyClassController::class);
    $router->resource('theology-courses', TheologyCourseController::class);
    $router->resource('students-theology-classes', StudentHasTheologyClassController::class);
    $router->resource('theology-exams', TheologyExamCourseController::class);
    $router->resource('theology-marks', TheologyMarkController::class);
    $router->resource('theology-termly-report-cards', TheologyTermlyReportCardController::class);
    $router->resource('theologry-student-report-cards', TheologryStudentReportCardController::class);
    $router->resource('nursery-termly-report-cards', NurseryTermlyReportCardController::class);
    $router->resource('nursery-student-report-cards', NurseryStudentReportCardController::class);
    $router->resource('nursery-student-report-card-items', NurseryStudentReportCardItemController::class);
    $router->resource('students-financial-accounts', StudentFinancialAccountController::class);
    $router->get('/batch-print', 'StudentReportCardController@print')->name('print');
    $router->resource('account-parents', AccountParentController::class);
    $router->resource('service-categories', ServiceCategoryController::class);
    $router->resource('academic-class-levels', AcademicClassLevelController::class);
    $router->resource('pending-students', StudentsController::class);
    $router->resource('not-active-students', StudentsController::class);
    $router->resource('promotions', PromotionController::class);
    $router->resource('documents', DocumentController::class);
    $router->resource('configuration', ConfigurationController::class);
    $router->resource('bursaries', BursaryController::class);
    $router->resource('bursary-beneficiaries', BursaryBeneficiaryController::class);
    $router->resource('student-report-card-items', StudentReportCardItemController::class);
    $router->resource('sessions', SessionController::class);
    $router->resource('participants', ParticipantController::class);
    $router->resource('parent-courses', ParentCourseController::class);
    $router->resource('activities', ActivityController::class);
    $router->resource('secondary-subjects', SecondarySubjectController::class);
    $router->resource('secondary-competences', SecondaryCompetenceController::class);
    $router->resource('parents', ParentsController::class);
    $router->resource('generate-theology-classes', GenerateTheologyClassController::class);
    $router->resource('secondary-termly-report-cards', SecondaryTermlyReportCardController::class);
    $router->resource('secondary-report-cards', SecondaryReportCardController::class); 
    $router->resource('secondary-report-card-items', SecondaryReportCardItemController::class); 
    $router->resource('streams', AcademicClassSctreamController::class); 

    //$router->resource('fees', StudentHasFeeController::class);

    $router->get('/statistics', 'HomeController@stats')->name('statistics');
    $router->get('/dashboard', 'HomeController@index')->name('dashboard');

    $router->get('/', 'HomeController@stats')->name('home');
    $router->resources([
        'enterprises' => EnterpriseController::class
    ]);
});
