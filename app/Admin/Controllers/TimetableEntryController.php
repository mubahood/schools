<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\TimetableRoom;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class TimetableEntryController extends AdminController
{
    protected $title = 'Timetable Entries';

    public function index(Content $content)
    {
        $u   = Admin::user();
        $ent = $u->ent;

        // Only classes belonging to the enterprise's current academic year
        $currentYearId = $ent->academic_year_id;
        $currentYear   = AcademicYear::find($currentYearId);

        $classes = AcademicClass::where('enterprise_id', $u->enterprise_id)
            ->where('academic_year_id', $currentYearId)
            ->orderBy('name')->get();

        $teachers = User::where('enterprise_id', $u->enterprise_id)
            ->where('user_type', 'employee')
            ->orderBy('name')->get(['id', 'name']);

        $rooms = TimetableRoom::where('enterprise_id', $u->enterprise_id)
            ->where('is_active', 1)->orderBy('name')->get();

        return $content
            ->title('Timetable Entries')
            ->breadcrumb(['text' => 'Timetable', 'url' => '#'], ['text' => 'Manage Entries'])
            ->body(view('admin.timetable.entries', compact(
                'classes', 'teachers', 'rooms', 'currentYear'
            )));
    }

    protected function grid()      { return null; }
    protected function detail($id) { return redirect(admin_url('timetable-entries')); }
    protected function form()      { return null; }
}
