<?php

namespace App\Admin\Controllers;

use App\Models\FixedAsset;
use App\Models\FixedAssetRecord;
use App\Models\Utils;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FixedAssetRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Asset Records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FixedAssetRecord());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $fixed_assets = [];
            $u = auth('admin')->user();
            foreach (FixedAsset::where([
                'enterprise_id' => $u->enterprise_id,
                'status' => 'Active',
            ])->get() as $key => $asset) {
                $fixed_assets[$asset->id] = $asset->name . " - " . $asset->code . " - UGX " . number_format($asset->current_value);
            }
            $filter->equal('fixed_asset_id', __('Filter by Asset'))->select($fixed_assets);
            $filter->between('date', __('Filter by Date'))->date();
            $filter->equal('status', __('Status'))
                ->select([
                    'Active' => 'Active',
                    'Disposed' => 'Disposed',
                    'Damaged' => 'Damaged',
                    'Lost' => 'Lost',
                ]);
            $filter->equal('type', __('Type'))
                ->select([
                    'Appreciation' => 'Appreciation',
                    'Depreciation' => 'Depreciation',
                ]);
        });

        $grid->disableBatchActions();
        $u = auth('admin')->user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ])->orderBy('id', 'DESC');

        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return Utils::my_date($created_at);
            })->sortable();


        $grid->column('fixed_asset_id', __('Fixed Asset'))
            ->display(function ($fixed_asset_id) {
                $asset = $this->fixed_asset;
                if ($asset) {
                    return $asset->name . " - " . $asset->code;
                }
                return '-';
            })->sortable();
        $grid->column('description', __('Description'))->sortable();
        $grid->column('current_value', __('Current Value'))
            ->display(function ($current_value) {
                return "UGX " . number_format($current_value);
            })->sortable();
        $grid->column('amount', __('Amount'))
            ->display(function ($amount) {
                return "UGX " . number_format($amount);
            })->sortable()
            ->totalRow(function ($amount) {
                return "<strong>UGX " . number_format($amount) . "</strong>";
            });
        $grid->column('date', __('Date'))->display(function ($date) {
            return Utils::my_date($date);
        })->sortable();
        $grid->column('status', __('Aasset Status'))
            ->label([
                'Active' => 'success',
                'Disposed' => 'danger',
                'Damaged' => 'warning',
                'Lost' => 'info',
            ])->sortable();
        $grid->column('type', __('Type'))
            ->label([
                'Appreciation' => 'success',
                'Depreciation' => 'danger',
            ])->sortable()
            ->filter([
                'Appreciation' => 'Appreciation',
                'Depreciation' => 'Depreciation',
            ]);
        $grid->column('photo', __('Photo'))
            ->lightbox(['width' => 50, 'height' => 50])->width(100)->sortable()->hide();
        $grid->column('file', __('File'))
            ->downloadable()->sortable()->hide();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(FixedAssetRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('fixed_asset_id', __('Fixed asset id'));
        $show->field('description', __('Description'));
        $show->field('current_value', __('Current value'));
        $show->field('amount', __('Amount'));
        $show->field('date', __('Date'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('photo', __('Photo'));
        $show->field('file', __('File'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FixedAssetRecord());
        $u = auth('admin')->user();
        $form->hidden('enterprise_id')->value($u->enterprise_id);

        //active fixed assts 
        $fixed_assets = [];

        foreach (FixedAsset::where([
            'enterprise_id' => $u->enterprise_id,
            'status' => 'Active',
        ])->get() as $key => $asset) {
            $fixed_assets[$asset->id] = $asset->name . " - " . $asset->code . " - UGX " . number_format($asset->current_value);
        }

        $form->select('fixed_asset_id', __('Select Asset'))
            ->options($fixed_assets)
            ->rules('required');
        $form->text('description', __('Record Description'))->rules('required');
        $form->decimal('current_value', __('Current Asset Value'))->rules('required');
        $form->date('date', __('Date'))->default(date('Y-m-d'));

        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Disposed' => 'Disposed',
                'Damaged' => 'Damaged',
                'Lost' => 'Lost',
            ])->default('Active')
            ->rules('required');

        $form->image('photo', __('Photo'));
        $form->file('file', __('File'));

        return $form;
    }
}
