# Laravel Admin - Comprehensive Documentation

## Table of Contents
1. [Introduction & Overview](#introduction--overview)
2. [Core Architecture](#core-architecture)
3. [Installation & Setup](#installation--setup)
4. [Configuration](#configuration)
5. [Authentication & Authorization](#authentication--authorization)
6. [Model Grid (Data Tables)](#model-grid-data-tables)
7. [Forms](#forms)
8. [Show Pages](#show-pages)
9. [Menu & Navigation](#menu--navigation)
10. [Widgets & Components](#widgets--components)
11. [Extensions](#extensions)
12. [Advanced Features](#advanced-features)
13. [Customization](#customization)
14. [Best Practices](#best-practices)
15. [Real-World Implementation Examples](#real-world-implementation-examples)

---

## Introduction & Overview

Laravel Admin (Encore/Admin) is a comprehensive administrative interface package for Laravel applications. It provides a rapid development framework for building admin panels with minimal code, featuring automatic CRUD operations, data grids, forms, charts, and extensive customization options.

### Key Features
- **Rapid Development**: Generate admin interfaces with minimal code
- **Data Grid**: Advanced table displays with filtering, sorting, and pagination
- **Form Builder**: Dynamic form generation with various field types
- **Authentication**: Built-in user management and permissions
- **Responsive Design**: Mobile-friendly interface based on AdminLTE
- **Extensible**: Plugin architecture for custom functionality
- **Multi-language Support**: Internationalization capabilities
- **Rich UI Components**: Charts, widgets, file uploads, and more

### Philosophy
Laravel Admin follows the principle of "configuration over coding", allowing developers to build complex admin interfaces by configuring components rather than writing extensive code.

---

## Core Architecture

### MVC Pattern in Laravel Admin

Laravel Admin follows a specialized MVC pattern:

#### Controllers
All admin controllers extend `Encore\Admin\Controllers\AdminController` and provide three main methods:

```php
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Show;

class UserController extends AdminController
{
    protected $title = 'Users';
    
    protected function grid()
    {
        $grid = new Grid(new User());
        // Configure grid columns and features
        return $grid;
    }
    
    protected function form()
    {
        $form = new Form(new User());
        // Configure form fields
        return $form;
    }
    
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));
        // Configure detail view
        return $show;
    }
}
```

#### Models
Standard Eloquent models work seamlessly with Laravel Admin. The package automatically handles relationships and provides advanced querying capabilities.

#### Views
Laravel Admin uses AdminLTE-based templates with Blade templating engine. Views are automatically generated based on controller configurations.

### Directory Structure
```
├── app/Admin/
│   ├── Controllers/        # Admin controllers
│   ├── Actions/           # Custom batch actions
│   ├── Extensions/        # Custom extensions
│   ├── bootstrap.php      # Admin bootstrapping
│   └── routes.php         # Admin routes
├── config/admin.php       # Main configuration
├── database/migrations/   # Admin-related migrations
└── resources/
    └── views/admin/       # Custom admin views
```

### Core Components

#### 1. Grid System
- **Purpose**: Display data in tabular format
- **Features**: Sorting, filtering, pagination, bulk actions, export
- **Customization**: Custom columns, renderers, actions

#### 2. Form System
- **Purpose**: Create and edit records
- **Features**: Field validation, file uploads, relationship handling
- **Field Types**: Text, select, date, image, rich text, etc.

#### 3. Show System
- **Purpose**: Display detailed record information
- **Features**: Relationship display, custom formatting
- **Layout**: Flexible panel-based layout

---

## Installation & Setup

### 1. Prerequisites
- PHP >= 7.0.0
- Laravel >= 5.5.0
- Fileinfo PHP Extension

### 2. Installation via Composer
```bash
composer require encore/laravel-admin
```

### 3. Publish Assets and Configuration
```bash
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
```

### 4. Run Installation Command
```bash
php artisan admin:install
```

This command will:
- Create admin database tables
- Create a default admin user (admin/admin)
- Publish configuration files
- Create admin directory structure

### 5. Access Admin Panel
Navigate to `http://localhost/admin/` and login with:
- Username: `admin`
- Password: `admin`

---

## Configuration

### Main Configuration File (`config/admin.php`)

#### Basic Settings
```php
return [
    // Application name displayed in admin panel
    'name' => 'Laravel Admin',
    
    // Logo displayed in header
    'logo' => '<b>Laravel</b> Admin',
    
    // Mini logo for collapsed sidebar
    'logo-mini' => '<b>LA</b>',
    
    // Bootstrap file path
    'bootstrap' => app_path('Admin/bootstrap.php'),
    
    // Route configuration
    'route' => [
        'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),
        'namespace' => 'App\\Admin\\Controllers',
        'middleware' => ['web', 'admin'],
    ],
];
```

#### Database Settings
```php
'database' => [
    'users_table' => 'admin_users',
    'users_model' => Encore\Admin\Auth\Database\Administrator::class,
    'roles_table' => 'admin_roles',
    'permissions_table' => 'admin_permissions',
    'menu_table' => 'admin_menu',
    'operation_log_table' => 'admin_operation_log',
],
```

#### Upload Settings
```php
'upload' => [
    'disk' => 'admin',
    'directory' => [
        'image' => 'images',
        'file' => 'files',
    ],
],
```

#### Extensions Configuration
```php
'extensions' => [
    'media-manager' => [
        // Extension configurations
    ],
],
```

---

## Authentication & Authorization

### User Management System

Laravel Admin provides its own authentication system separate from Laravel's default authentication.

#### Admin Users
```php
// Get current admin user
$user = Admin::user();

// Check if user is logged in
if (Admin::guard()->check()) {
    // User is authenticated
}

// Get admin guard
$guard = Admin::guard();
```

#### Roles and Permissions System

Laravel Admin implements a role-based access control (RBAC) system:

```php
// Check user permissions
if (Admin::user()->can('users.create')) {
    // User has permission to create users
}

// Check user roles
if (Admin::user()->isRole('admin')) {
    // User has admin role
}

// Multiple role check
if (Admin::user()->isRole('admin', 'editor')) {
    // User has either admin or editor role
}
```

#### Creating Custom Permissions
```php
// In AdminServiceProvider or bootstrap.php
Admin::extend('permission', function ($extension) {
    $extension->add('users.create', 'Create Users');
    $extension->add('users.edit', 'Edit Users');
    $extension->add('users.delete', 'Delete Users');
    $extension->add('posts.publish', 'Publish Posts');
});
```

#### Middleware Protection
```php
// Protect routes with admin middleware
Route::group([
    'prefix' => 'admin',
    'middleware' => ['admin'],
], function () {
    // Admin routes
});

// Protect specific actions with permissions
$grid->actions(function ($actions) {
    if (!Admin::user()->can('users.delete')) {
        $actions->disableDelete();
    }
});
```

---

## Model Grid (Data Tables)

The Grid component is the most powerful feature of Laravel Admin, providing rich data display capabilities.

### Basic Grid Setup
```php
protected function grid()
{
    $grid = new Grid(new User());
    
    // Basic columns
    $grid->column('id', 'ID')->sortable();
    $grid->column('name', 'Name')->sortable();
    $grid->column('email', 'Email');
    $grid->column('created_at', 'Created At');
    
    return $grid;
}
```

### Advanced Column Types

#### Display Modifiers
```php
// Custom display logic
$grid->column('status')->display(function ($status) {
    return $status ? 'Active' : 'Inactive';
});

// Image column
$grid->column('avatar', 'Avatar')->image('', 50, 50);

// Link column
$grid->column('email')->link('mailto:');

// Badge/Label column
$grid->column('status')->label([
    1 => 'success',
    0 => 'danger',
], [
    1 => 'Active',
    0 => 'Inactive',
]);

// Progress bar
$grid->column('progress')->progressBar('primary', 'sm');

// JSON column
$grid->column('options')->json();

// Date formatting
$grid->column('created_at')->display(function ($date) {
    return date('Y-m-d H:i:s', strtotime($date));
});
```

#### Relationship Columns
```php
// HasOne/BelongsTo
$grid->column('profile.phone', 'Phone');
$grid->column('profile.address', 'Address');

// HasMany count
$grid->column('posts')->display(function ($posts) {
    return count($posts);
});

// Many-to-Many
$grid->column('roles')->pluck('name')->label();
```

### Grid Filters

#### Basic Filters
```php
$grid->filter(function($filter) {
    // Remove default ID filter
    $filter->disableIdFilter();
    
    // Text filter
    $filter->like('name', 'Name');
    
    // Exact match
    $filter->equal('status', 'Status')->select([
        1 => 'Active',
        0 => 'Inactive',
    ]);
    
    // Date range
    $filter->whereBetween('created_at', function ($query) {
        $query->whereBetween('created_at', [$start, $end]);
    })->datetime();
    
    // Select filter with model data
    $filter->equal('category_id', 'Category')
        ->select(Category::all()->pluck('name', 'id'));
});
```

#### Advanced Filters
```php
// Group filters
$filter->group('amount', function ($group) {
    $group->gt('Greater than');
    $group->lt('Less than');
    $group->equal('Equal to');
});

// Scope filters
$filter->scope('published', 'Published only')->where('status', 1);
$filter->scope('draft', 'Draft only')->where('status', 0);

// Custom where conditions
$filter->where(function ($query) {
    $query->where('name', 'like', "%{$this->input}%")
          ->orWhere('email', 'like', "%{$this->input}%");
}, 'Search name or email');
```

### Grid Actions

#### Row Actions
```php
$grid->actions(function ($actions) {
    // Disable default actions
    $actions->disableDelete();
    $actions->disableEdit();
    $actions->disableView();
    
    // Add custom action
    $actions->add(new ReplicateAction());
});
```

#### Batch Actions
```php
$grid->batchActions(function ($batch) {
    // Add custom batch action
    $batch->add(new BatchReplicate());
    $batch->add(new BatchDelete());
});
```

#### Custom Actions Example
```php
// Custom row action
class ReplicateAction extends RowAction
{
    public $name = 'Replicate';
    
    public function handle(Model $model)
    {
        $model->replicate()->save();
        return $this->response()->success('Replicated!')->refresh();
    }
}

// Custom batch action
class BatchReplicate extends BatchAction
{
    public $name = 'Batch Replicate';
    
    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            $model->replicate()->save();
        }
        return $this->response()->success('Replicated!')->refresh();
    }
}
```

### Grid Tools

#### Built-in Tools
```php
$grid->tools(function ($tools) {
    // Disable refresh button
    $tools->disableRefreshButton();
    
    // Add custom tool
    $tools->add(new CustomTool());
    
    // Batch selector
    $tools->batch(function ($batch) {
        $batch->add('Delete selected', new BatchDelete());
    });
});
```

### Grid Features

#### Export Functionality
```php
// Enable export
$grid->export();

// Custom export
$grid->exporter(new CustomExporter());

// Configure export
$grid->export(function ($export) {
    $export->filename('users_export');
    $export->except(['id', 'created_at']);
});
```

#### Pagination
```php
// Set items per page
$grid->perPages([10, 25, 50, 100]);

// Default page size
$grid->paginate(15);
```

#### Quick Search
```php
// Single field search
$grid->quickSearch('name');

// Multiple fields search
$grid->quickSearch('name', 'email', 'phone');

// Custom search logic
$grid->quickSearch(function ($model, $query) {
    $model->where('name', 'like', "%{$query}%")
          ->orWhere('email', 'like', "%{$query}%");
});
```

---

## Forms

Laravel Admin forms provide powerful form building capabilities with validation, file uploads, and relationship handling.

### Basic Form Structure
```php
protected function form()
{
    $form = new Form(new User());
    
    // Basic fields
    $form->text('name', 'Name')->rules('required');
    $form->email('email', 'Email')->rules('required|email');
    $form->password('password', 'Password')->rules('required|min:6');
    
    return $form;
}
```

### Field Types

#### Basic Input Fields
```php
// Text input
$form->text('name', 'Name');

// Email input
$form->email('email', 'Email');

// Password input
$form->password('password', 'Password');

// Hidden input
$form->hidden('user_id')->default(Auth::id());

// Number input
$form->number('age', 'Age');

// URL input
$form->url('website', 'Website');

// Tel input
$form->tel('phone', 'Phone');
```

#### Text Areas and Rich Text
```php
// Textarea
$form->textarea('description', 'Description');

// Rich text editor
$form->editor('content', 'Content');

// Markdown editor
$form->markdown('content', 'Content');
```

#### Select Fields
```php
// Simple select
$form->select('status', 'Status')->options([
    1 => 'Active',
    0 => 'Inactive',
]);

// Select with model data
$form->select('category_id', 'Category')
    ->options(Category::all()->pluck('name', 'id'));

// Multiple select
$form->multipleSelect('tags', 'Tags')
    ->options(Tag::all()->pluck('name', 'id'));

// Cascading select
$form->select('province_id', 'Province')
    ->options(Province::all()->pluck('name', 'id'))
    ->load('city_id', '/api/cities');
    
$form->select('city_id', 'City');
```

#### Date and Time Fields
```php
// Date picker
$form->date('birth_date', 'Birth Date');

// Time picker
$form->time('start_time', 'Start Time');

// DateTime picker
$form->datetime('published_at', 'Published At');

// Date range
$form->dateRange('start_date', 'end_date', 'Date Range');
```

#### File and Image Uploads
```php
// Image upload
$form->image('avatar', 'Avatar');

// File upload
$form->file('document', 'Document');

// Multiple file upload
$form->multipleFile('attachments', 'Attachments');

// Multiple image upload
$form->multipleImage('gallery', 'Gallery');
```

#### Advanced Fields
```php
// Checkbox
$form->checkbox('features', 'Features')->options([
    'feature1' => 'Feature 1',
    'feature2' => 'Feature 2',
]);

// Radio buttons
$form->radio('gender', 'Gender')->options([
    'male' => 'Male',
    'female' => 'Female',
]);

// Switch/Toggle
$form->switch('is_active', 'Active');

// Slider
$form->slider('rating', 'Rating')->options([
    'max' => 100,
    'min' => 0,
    'step' => 1,
]);

// Color picker
$form->color('theme_color', 'Theme Color');

// Rate/Star rating
$form->rate('rating', 'Rating');
```

### Form Relationships

#### One-to-Many (HasMany)
```php
$form->hasMany('posts', 'Posts', function (Form\NestedForm $form) {
    $form->text('title', 'Title');
    $form->textarea('content', 'Content');
});
```

#### Many-to-Many (BelongsToMany)
```php
$form->belongsToMany('roles', 'Roles', function (Form\NestedForm $form) {
    $form->select('role_id', 'Role')->options(Role::all()->pluck('name', 'id'));
});
```

### Form Validation

#### Basic Validation Rules
```php
$form->text('name', 'Name')->rules('required|min:3|max:255');
$form->email('email', 'Email')->rules('required|email|unique:users,email');
$form->number('age', 'Age')->rules('required|integer|min:1|max:120');
```

#### Custom Validation
```php
$form->text('username', 'Username')->rules(function ($attribute, $value, $fail) {
    if (User::where('username', $value)->exists()) {
        $fail('Username already exists.');
    }
});
```

### Form Events and Hooks

#### Saving Events
```php
$form->saving(function (Form $form) {
    // Before saving
    $form->model()->created_by = Auth::id();
});

$form->saved(function (Form $form) {
    // After saving
    Log::info('User saved: ' . $form->model()->id);
});
```

#### Deleting Events
```php
$form->deleting(function (Form $form) {
    // Before deleting
    if ($form->model()->posts->count() > 0) {
        return response()->json([
            'status' => false,
            'message' => 'Cannot delete user with posts.',
        ]);
    }
});
```

### Form Layout

#### Tabs
```php
$form->tab('Basic Info', function ($form) {
    $form->text('name', 'Name');
    $form->email('email', 'Email');
});

$form->tab('Profile', function ($form) {
    $form->image('avatar', 'Avatar');
    $form->textarea('bio', 'Bio');
});
```

#### Columns
```php
$form->column(6, function ($form) {
    $form->text('name', 'Name');
});

$form->column(6, function ($form) {
    $form->email('email', 'Email');
});
```

#### Fieldsets
```php
$form->fieldset('Basic Information', function ($form) {
    $form->text('name', 'Name');
    $form->email('email', 'Email');
});
```

---

## Show Pages

Show pages display detailed information about a single record.

### Basic Show Page
```php
protected function detail($id)
{
    $show = new Show(User::findOrFail($id));
    
    $show->field('id', 'ID');
    $show->field('name', 'Name');
    $show->field('email', 'Email');
    $show->field('created_at', 'Created At');
    
    return $show;
}
```

### Field Display Types

#### Basic Fields
```php
// Text field
$show->field('name', 'Name');

// Image field
$show->field('avatar', 'Avatar')->image();

// File field
$show->field('document', 'Document')->file();

// JSON field
$show->field('metadata', 'Metadata')->json();

// URL field
$show->field('website', 'Website')->link();
```

#### Custom Display
```php
$show->field('status', 'Status')->using([
    1 => 'Active',
    0 => 'Inactive',
]);

$show->field('created_at', 'Created At')->as(function ($date) {
    return date('Y-m-d H:i:s', strtotime($date));
});
```

### Relationship Display
```php
// HasOne relationship
$show->field('profile.phone', 'Phone');

// HasMany relationship
$show->posts('Posts', function ($posts) {
    $posts->setResource('/admin/posts');
    $posts->id();
    $posts->title();
    $posts->created_at();
});
```

### Show Panels
```php
$show->panel()
    ->style('primary')
    ->title('User Information')
    ->body('Detailed user information panel.');
```

---

## Menu & Navigation

### Menu Configuration

Laravel Admin uses database-driven menu system stored in the `admin_menu` table.

#### Programmatic Menu Creation
```php
// In bootstrap.php or AdminServiceProvider
Admin::menu(function ($menu) {
    $menu->add([
        'title' => 'Dashboard',
        'path' => '/',
        'icon' => 'fa-dashboard',
    ]);
    
    $menu->add([
        'title' => 'Users',
        'path' => '/users',
        'icon' => 'fa-users',
    ]);
    
    // Submenu
    $menu->add([
        'title' => 'Content',
        'icon' => 'fa-edit',
        'children' => [
            [
                'title' => 'Posts',
                'path' => '/posts',
                'icon' => 'fa-file-text',
            ],
            [
                'title' => 'Categories',
                'path' => '/categories',
                'icon' => 'fa-tags',
            ],
        ],
    ]);
});
```

### Navigation Extensions

#### Custom Navigation Components
```php
// In bootstrap.php
Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
    // Left side navigation
    $navbar->left(view('admin.navbar.search'));
    
    // Right side navigation
    $navbar->right(view('admin.navbar.user-menu'));
});
```

#### Shortcuts Extension
```php
use App\Admin\Extensions\Nav\Shortcut;

$navbar->left(Shortcut::make([
    'Create User' => 'users/create',
    'Create Post' => 'posts/create',
], 'fa-plus')->title('Quick Actions'));
```

---

## Widgets & Components

### Dashboard Widgets

#### Info Box
```php
use Encore\Admin\Widgets\InfoBox;

$infoBox = new InfoBox('Total Users', 'users', 'aqua', '/admin/users', '150');
```

#### Chart Widgets
```php
use Encore\Admin\Widgets\Chart\Line;

$chart = new Line();
$chart->title('Monthly Sales');
$chart->data([10, 20, 30, 40, 50]);
$chart->labels(['Jan', 'Feb', 'Mar', 'Apr', 'May']);
```

### Tab Widget
```php
use Encore\Admin\Widgets\Tab;

$tab = new Tab();
$tab->add('Tab 1', 'Content for tab 1');
$tab->add('Tab 2', 'Content for tab 2');

return $tab;
```

### Box Widget
```php
use Encore\Admin\Widgets\Box;

$box = new Box('Box Title', 'Box content here');
$box->solid();
$box->style('primary');
```

---

## Extensions

Laravel Admin has a rich ecosystem of extensions that extend its functionality.

### Official Extensions

#### Media Manager
```bash
composer require encore/laravel-admin-ext/media-manager
```

```php
// Enable in config/admin.php
'extensions' => [
    'media-manager' => [
        // Media manager configurations
    ],
],
```

#### Backup Extension
```bash
composer require encore/laravel-admin-ext/backup
```

### Custom Extensions

#### Creating Custom Extension
```php
// app/Admin/Extensions/CustomWidget.php
<?php

namespace App\Admin\Extensions;

use Encore\Admin\Extension;

class CustomWidget extends Extension
{
    public $name = 'custom-widget';
    
    public static function boot()
    {
        Admin::css('/css/custom-widget.css');
        Admin::js('/js/custom-widget.js');
    }
}
```

#### Grid Column Extension
```php
// app/Admin/Extensions/Column/StatusBadge.php
<?php

namespace App\Admin\Extensions\Column;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class StatusBadge extends AbstractDisplayer
{
    public function display($badges = [])
    {
        $status = $this->value;
        $badge = $badges[$status] ?? 'default';
        
        return "<span class='label label-{$badge}'>{$status}</span>";
    }
}
```

Usage:
```php
$grid->column('status')->statusBadge([
    'active' => 'success',
    'inactive' => 'danger',
]);
```

---

## Advanced Features

### Data Export

#### Custom Exporter
```php
use Encore\Admin\Grid\Exporters\AbstractExporter;

class CustomExporter extends AbstractExporter
{
    protected $fileName = 'custom_export.xlsx';
    
    public function export()
    {
        $data = $this->getData();
        
        // Custom export logic
        Excel::create($this->fileName, function ($excel) use ($data) {
            $excel->sheet('Data', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('xlsx');
    }
}
```

### File Uploads

#### Custom Upload Handler
```php
$form->image('avatar', 'Avatar')->uniqueName()->move('avatars');

// Custom upload path
$form->file('document', 'Document')->move('documents/' . date('Y/m'));

// Upload validation
$form->image('photo', 'Photo')->rules('required|image|max:2048');
```

### Multi-Language Support

#### Language Files
Create language files in `resources/lang/{locale}/admin.php`:

```php
// resources/lang/en/admin.php
return [
    'users' => 'Users',
    'create_user' => 'Create User',
    'edit_user' => 'Edit User',
];

// resources/lang/es/admin.php
return [
    'users' => 'Usuarios',
    'create_user' => 'Crear Usuario',
    'edit_user' => 'Editar Usuario',
];
```

#### Usage in Controllers
```php
$grid->column('name', trans('admin.name'));
$form->text('name', trans('admin.name'));
```

---

## Customization

### Custom Themes

#### AdminLTE Customization
```php
// In bootstrap.php
Admin::css('/css/custom-admin.css');
Admin::js('/js/custom-admin.js');
```

#### Custom CSS
```css
/* public/css/custom-admin.css */
.skin-blue .main-header .navbar {
    background-color: #3c8dbc;
}

.skin-blue .main-header .logo {
    background-color: #367fa9;
}
```

### Custom Views

#### Override Default Views
Create custom views in `resources/views/admin/`:

```blade
{{-- resources/views/admin/grid/table.blade.php --}}
<div class="box-body table-responsive no-padding">
    <table class="table table-hover">
        <!-- Custom table structure -->
    </table>
</div>
```

### Custom Form Fields

#### Creating Custom Field
```php
// app/Admin/Extensions/Form/CustomField.php
<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class CustomField extends Field
{
    protected static $css = ['/css/custom-field.css'];
    protected static $js = ['/js/custom-field.js'];
    
    protected $view = 'admin.form.custom-field';
    
    public function render()
    {
        return parent::render()->with('options', $this->options);
    }
}
```

Register the field:
```php
// In bootstrap.php
use App\Admin\Extensions\Form\CustomField;

Form::extend('customField', CustomField::class);
```

Usage:
```php
$form->customField('field_name', 'Field Label');
```

---

## Best Practices

### Performance Optimization

#### Query Optimization
```php
// Use eager loading for relationships
$grid->model()->with(['category', 'tags']);

// Limit columns in queries
$grid->model()->select(['id', 'name', 'email', 'created_at']);

// Use pagination
$grid->paginate(50); // Don't load too many records at once
```

#### Caching
```php
// Cache expensive queries
$grid->column('stats', 'Stats')->display(function () {
    return Cache::remember("user_stats_{$this->id}", 60, function () {
        return $this->calculateStats();
    });
});
```

### Security Best Practices

#### Input Validation
```php
// Always validate form inputs
$form->text('name', 'Name')->rules('required|string|max:255');
$form->email('email', 'Email')->rules('required|email|unique:users,email');

// Sanitize input
$form->saving(function (Form $form) {
    $form->input('name', strip_tags($form->input('name')));
});
```

#### Permission Checks
```php
// Protect sensitive operations
$grid->actions(function ($actions) {
    if (!Admin::user()->can('users.delete')) {
        $actions->disableDelete();
    }
});

// Controller-level protection
public function __construct()
{
    $this->middleware('admin.permission:users.manage');
}
```

### Code Organization

#### Keep Controllers Clean
```php
// Good: Separate complex logic into services
class UserController extends AdminController
{
    protected function grid()
    {
        return (new UserGridBuilder())->build();
    }
    
    protected function form()
    {
        return (new UserFormBuilder())->build();
    }
}

// UserGridBuilder.php
class UserGridBuilder
{
    public function build()
    {
        $grid = new Grid(new User());
        $this->configureColumns($grid);
        $this->configureFilters($grid);
        return $grid;
    }
    
    private function configureColumns($grid) { /* ... */ }
    private function configureFilters($grid) { /* ... */ }
}
```

#### Use Form Requests
```php
// Create form request classes for complex validation
class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];
    }
}

// Use in controller
$form->saving(function (Form $form) {
    $request = new UserRequest();
    $validator = Validator::make($form->input(), $request->rules());
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
});
```

---

## Real-World Implementation Examples

Based on the school management system codebase examined, here are practical examples:

### Student Management System

#### Students Grid
```php
class StudentsController extends AdminController
{
    protected $title = 'Students';
    
    protected function grid()
    {
        $grid = new Grid(new User());
        
        // Filter by enterprise and user type
        $grid->model()->where([
            'enterprise_id' => Admin::user()->enterprise_id,
            'user_type' => 'student'
        ])->orderBy('id', 'desc');
        
        // Batch actions for student management
        $grid->batchActions(function ($batch) {
            $batch->add(new ChangeStudentsStatus());
            $batch->add(new PromoteStudentsClass());
            $batch->add(new UpdateStudentsStream());
        });
        
        // Custom filters
        $grid->filter(function($filter) {
            $filter->equal('current_class_id', 'Class')
                ->select(AcademicClass::where('enterprise_id', Admin::user()->enterprise_id)
                    ->pluck('name', 'id'));
            
            $filter->equal('status', 'Status')->select([
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });
        
        // Columns with relationships
        $grid->column('name', 'Name')->sortable();
        $grid->column('current_class.name', 'Class');
        $grid->column('sex', 'Gender');
        $grid->column('phone_number_1', 'Phone');
        
        return $grid;
    }
}
```

#### Student Form with Complex Relationships
```php
protected function form()
{
    $form = new Form(new User());
    
    // Auto-fill enterprise
    $form->hidden('enterprise_id')->default(Admin::user()->enterprise_id);
    $form->hidden('user_type')->default('student');
    
    // Personal information
    $form->divider('Personal Information');
    $form->text('first_name', 'First Name')->rules('required');
    $form->text('last_name', 'Last Name')->rules('required');
    $form->radio('sex', 'Gender')->options(['Male' => 'Male', 'Female' => 'Female']);
    $form->date('date_of_birth', 'Date of Birth');
    
    // Academic information
    $form->divider('Academic Information');
    $form->select('current_class_id', 'Class')
        ->options(AcademicClass::where('enterprise_id', Admin::user()->enterprise_id)
            ->pluck('name', 'id'))
        ->rules('required');
    
    // File uploads
    $form->divider('Documents');
    $form->image('avatar', 'Photo');
    $form->file('birth_certificate', 'Birth Certificate');
    
    return $form;
}
```

### Financial Management

#### Transaction Grid with Advanced Filtering
```php
class TransactionController extends AdminController
{
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        
        // Complex filters for financial data
        $grid->filter(function ($filter) {
            $filter->equal('account_id', 'Student')
                ->select(function ($id) {
                    $account = Account::find($id);
                    return $account ? [$account->id => $account->name] : [];
                });
            
            $filter->whereBetween('amount', function ($query) {
                $query->whereBetween('amount', [$this->input['start'], $this->input['end']]);
            })->range();
            
            $filter->equal('type', 'Type')->select([
                'FEES_PAYMENT' => 'School Fees',
                'OTHER_PAYMENT' => 'Other Payment',
            ]);
        });
        
        // Financial calculations
        $grid->column('amount', 'Amount')->display(function ($amount) {
            return 'UGX ' . number_format($amount);
        })->totalRow(function ($amount) {
            return 'Total: UGX ' . number_format($amount);
        });
        
        return $grid;
    }
}
```

### Multi-Tenant System

#### Enterprise-Scoped Controllers
```php
abstract class EnterpriseController extends AdminController
{
    protected function applyEnterpriseScope($model)
    {
        return $model->where('enterprise_id', Admin::user()->enterprise_id);
    }
    
    protected function grid()
    {
        $grid = new Grid($this->getModel());
        $this->applyEnterpriseScope($grid->model());
        return $this->configureGrid($grid);
    }
    
    abstract protected function getModel();
    abstract protected function configureGrid($grid);
}

class BooksController extends EnterpriseController
{
    protected function getModel()
    {
        return new Book();
    }
    
    protected function configureGrid($grid)
    {
        $grid->column('title', 'Title');
        $grid->column('author.name', 'Author');
        return $grid;
    }
}
```

### Custom Dashboard with Widgets

#### Dashboard Controller
```php
class DashboardController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Dashboard')
            ->description('School Management Dashboard')
            ->row($this->statsRow())
            ->row($this->chartsRow());
    }
    
    protected function statsRow()
    {
        $enterprise = Admin::user()->ent;
        
        return [
            $this->infoBox('Total Students', 'students', 'aqua', 
                $enterprise->students()->count()),
            $this->infoBox('Total Staff', 'users', 'green', 
                $enterprise->staff()->count()),
            $this->infoBox('This Month Revenue', 'money', 'yellow', 
                'UGX ' . number_format($enterprise->thisMonthRevenue())),
        ];
    }
    
    protected function infoBox($title, $icon, $color, $value)
    {
        return new InfoBox($title, $icon, $color, '/admin/dashboard', $value);
    }
}
```

---

## Conclusion

Laravel Admin is a powerful package that significantly reduces development time for admin interfaces. Its configuration-driven approach, combined with extensive customization options, makes it suitable for both simple and complex applications.

Key takeaways:
1. **Start Simple**: Begin with basic grid and form configurations
2. **Leverage Relationships**: Use Eloquent relationships for complex data display
3. **Extend Thoughtfully**: Create custom extensions only when built-in features are insufficient
4. **Secure by Default**: Always implement proper authentication and authorization
5. **Performance Matters**: Optimize queries and use caching for better performance

The package's flexibility allows it to adapt to various business requirements while maintaining a consistent and professional interface. Whether building a simple CRUD application or a complex multi-tenant system like the school management platform examined, Laravel Admin provides the tools necessary for rapid development and easy maintenance.
