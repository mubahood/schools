<?php

namespace App\Admin\Controllers;

use App\Models\FixedAsset;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class FixedAssetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Fixed Assets';


    public function stats(Content $content)
    {
        die('stats');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FixedAsset());

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $fixed_assets = [];
            $u = Admin::user();

            $filter->between('purchase_date', __('Filter by Purchase Date'))->date();
            $filter->equal('status', __('Status'))
                ->select([
                    'Active' => 'Active',
                    'Disposed' => 'Disposed',
                    'Damaged' => 'Damaged',
                    'Lost' => 'Lost',
                ]);
            $filter->equal('assigned_to_id', __('Assigned to'))
                ->select(
                    Administrator::where([
                        'enterprise_id' => $u->enterprise_id,
                        'user_type' => 'employee',
                    ])->get()->pluck('name', 'id')
                );
            $filter->equal('due_term_id', __('Due term'))
                ->select(
                    \App\Models\Term::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])->get()->pluck('name_text', 'id')
                );
            $filter->equal('category', __('Category'))
                ->select(
                    \App\Models\FixedAssetCategory::where([
                        'enterprise_id' => $u->enterprise_id,
                    ])->get()->pluck('name', 'id')
                );
        });

        $u = Admin::user();
        $grid->model()->where([
            'enterprise_id' => $u->enterprise_id,
        ]);

        $grid->column('photo', __('Photo'))->lightbox(['width' => 50, 'height' => 50])->width(100)->sortable();

        $grid->column('name', __('Name'))->sortable()
            ->hide();
        $grid->column('code', __('Code'))->sortable()->filter('like');
        $grid->column('assigned_to_id', __('Assigned'))
            ->display(function ($assigned_to_id) {
                return $this->assigned_to->name_text;
            })->sortable();
        $grid->column('due_term_id', __('Due term'))
            ->display(function ($due_term_id) {
                return $this->due_term->name_text;
            })->sortable();
        $grid->column('category', __('Category'))
            ->display(function ($id) {
                if ($this->category_data == null) {
                    return 'N/A - ' . $id;
                }
                return $this->category_data->name;
            })->sortable();
        $grid->column('description', __('Description'))->hide();


        $grid->disableBatchActions();

        $grid->column('status', __('Status'))->sortable()
            ->label([
                'Active' => 'success',
                'Disposed' => 'danger',
                'Damaged' => 'warning',
                'Lost' => 'info',
            ])
            ->sortable()
            ->filter([
                'Active' => 'Active',
                'Disposed' => 'Disposed',
                'Damaged' => 'Damaged',
                'Lost' => 'Lost',
            ]);
        $grid->column('purchase_date', __('Purchase Date'))->hide();
        $grid->column('warranty_expiry_date', __('Warranty expiry date'))->hide();
        $grid->column('maintenance_due_date', __('Maintenance due date'))->hide();
        $grid->column('purchase_price', __('Purchase price'))->sortable()
            ->filter('range')
            ->display(function ($purchase_price) {
                return 'UGX ' . Utils::number_format($purchase_price, '');
            })
            ->totalRow(function ($amount) {
                return "<strong>UGX " . Utils::number_format($amount, '') . "</strong>";
            })->sortable();
        $grid->column('current_value', __('Current value'))
            ->sortable()
            ->filter('range')
            ->display(function ($current_value) {
                return 'UGX ' . Utils::number_format($current_value, '');
            })->totalRow(function ($amount) {
                return "<strong>UGX " . Utils::number_format($amount, '') . "</strong>";
            })->sortable();
        $grid->column('remarks', __('Remarks'))->hide();
        $grid->column('serial_number', __('Serial number'))->hide();
        $grid->column('qr_code', __('Qr code'))->hide();

        $grid->column('barcode', __('Barcode'))
            ->lightbox(['width' => 200, 'height' => 60])
            ->sortable()
            ->width(200);
        $grid->column('created_at', __('Created'))->sortable()->hide();
        $grid->column('updated_at', __('Updated'))->sortable()->hide();
        $grid->column('last_seen', __('Last Seen'))->sortable()
            ->display(function ($last_seen) {
                if ($last_seen == null || $last_seen == '') {
                    $last_seen = $this->updated_at;
                }
                return Carbon::parse($last_seen)->diffForHumans();
            })->sortable();

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
        $show = new Show(FixedAsset::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('assigned_to_id', __('Assigned to id'));
        $show->field('due_term_id', __('Due term id'));
        $show->field('category', __('Category'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('photo', __('Photo'));
        $show->field('status', __('Status'));
        $show->field('purchase_date', __('Purchase date'));
        $show->field('warranty_expiry_date', __('Warranty expiry date'));
        $show->field('maintenance_due_date', __('Maintenance due date'));
        $show->field('purchase_price', __('Purchase price'));
        $show->field('current_value', __('Current value'));
        $show->field('remarks', __('Remarks'));
        $show->field('serial_number', __('Serial number'));
        $show->field('code', __('Code'));
        $show->field('qr_code', __('Qr code'));
        $show->field('barcode', __('Barcode'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FixedAsset());
        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->value($u->enterprise_id);

        $teachers = [];
        foreach (Administrator::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type' => 'employee',
        ])->get() as $key => $a) {
            if ($a->isRole('teacher')) {
                $teachers[$a['id']] = $a['name'] . "  " . $a['id'];
            }
        }

        $cats = \App\Models\FixedAssetCategory::where([
            'enterprise_id' => $u->enterprise_id,
        ])->get()->pluck('name', 'id');

        $form->text('name', __('Name'))->rules('required');
        $form->select('category', __('Category'))
            ->options($cats)
            ->rules('required')
            ->required();
        $form->image('photo', __('Photo'));


        $form->date('purchase_date', __('Purchase date'))->default(date('Y-m-d'))->rules('required');


        $form->decimal('purchase_price', __('Purchase Price'))->rules('required');
        $form->decimal('current_value', __('Current value'));

        $form->text('serial_number', __('Serial number'));

 

        $active_term = Admin::user()->ent->active_term();
        $form->select('due_term_id', __('Due term'))
            ->options(
                \App\Models\Term::where([
                    'enterprise_id' => $u->enterprise_id,
                ])->get()->pluck('name_text', 'id')
            )
            ->default($active_term->id)
            ->rules('required');

        $form->select('assigned_to_id', __('Asset Assigned to'))
            ->options($teachers)
            ->rules('required');

        $form->date('warranty_expiry_date', __('Warranty expiry date'));
        $form->date('maintenance_due_date', __('Next Maintenance date'));
        $form->textarea('description', __('Asset Details'));
        $form->text('remarks', __('Remarks'));
        $form->disableReset();
        $form->disableViewCheck();
        $form->divider();
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Disposed' => 'Disposed',
                'Damaged' => 'Damaged',
                'Lost' => 'Lost',
            ])->default('Active')
            ->rules('required');

        $form->text('id', __('iD'));


        return $form;
    }
}
