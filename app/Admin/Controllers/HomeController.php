<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class HomeController extends Controller
{
    public function index(Content $content)
    {

        $ent = Utils::ent();
        Utils::reconcile_in_background(Admin::user()->enterprise_id);

        return $content
            ->title('Dashboard')
            ->description('Description... ' . $ent->name)
            ->row(function (Row $row) {

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::environment());
                }); //new staff

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::environment());
                }); 

                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::environment());
                }); 
            });


        /*$menu_items = MenuItem::all();
         return $content
            ->view('admin.dashboard', [
                'menu_items' => $menu_items
            ])->title(' - Dashboard'); */


        return $content->title($ent->name)
            ->description(' - Dashboard coming soon...')
            /* ->row(Dashboard::title()) */
            ->row(function (Row $row) {

                /* $row->column(3, function (Column $column) {
                    $teachers_count = Administrator::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                        'user_type' => 'teacher',
                    ])->count();

                    $box  = new Box('Teachers', view('widgets.box-3', [
                        'icon' => 'teacher.png',
                        'count' => number_format($teachers_count),
                        'sub_title' => 'All teachers registered',
                    ]));
                    $box->style('success');
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    $students_count = Administrator::where([
                        'enterprise_id' => Admin::user()->enterprise_id,
                        'user_type' => 'student',
                    ])->count();

                    $box  = new Box('Students', view('widgets.box-3', [
                        'icon' => 'student.png',
                        'count' => number_format($students_count),
                        'sub_title' => 'All students registered',
                    ]));
                    $box->style('success');
                    $column->append($box);
                }); */
            });
    }
}
