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
            ->title($ent->name)
            ->description('Dashboard')
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::students());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::teachers());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::parents());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::fees());
                });
            })
            ->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::income_vs_expenses());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::fees_collected());
                });
                $row->column(3, function (Column $column) {
                    $column->append(Dashboard::help_videos());
                });
            });
    }
}
