<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\TimetableEntry;
use App\Models\TimetableRoom;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class TimetableEntryController extends AdminController
{
    protected $title = 'Timetable Entries';

    // Override index with full custom JS-powered page
    public function index(Content $content)
    {
        $u = Admin::user();

        $classes  = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->orderBy('name')->get();
        $teachers = User::where(['enterprise_id' => $u->enterprise_id, 'user_type' => 'employee'])
            ->orderBy('name')->get();
        $rooms    = TimetableRoom::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)->orderBy('name')->get();

        return $content
            ->title('Timetable Entries')
            ->breadcrumb(['text' => 'Timetable', 'url' => '#'], ['text' => 'Manage Entries'])
            ->body(view('admin.timetable.entries', compact('classes', 'teachers', 'rooms')));
    }

    // All CRUD is handled by TimetableController API — block old form routes
    protected function grid()      { return null; }
    protected function detail($id) { return redirect(admin_url('timetable-entries')); }
    protected function form()      { return null; }
}
