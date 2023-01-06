<?php

namespace App\Admin\Controllers;

use App\Models\Bursary;
use App\Models\BursaryBeneficiary;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class BursaryBeneficiaryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Bursary Beneficiaries';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BursaryBeneficiary());
     /*    $x = BursaryBeneficiary::find(1);
        $x->description .= 1;
        $x->save(); 
 */

        $grid->disableBatchActions();
        $grid->disableActions(); 
        $grid->model()
            ->where([
                'enterprise_id' => Auth::user()->enterprise_id
            ])->orderBy('id', 'desc');


        $grid->column('administrator_id', __('Beneficiary'));
        $grid->column('bursary_id', __('Bursary'));
        $grid->column('description', __('Description'));

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
        $show = new Show(BursaryBeneficiary::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('bursary_id', __('Bursary id'));
        $show->field('description', __('Description'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new BursaryBeneficiary());
        $form->hidden('enterprise_id', __('Enterprise id'))->default(Auth::user()->ent->id);

        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax?'
                . 'enterprise_id=' . $u->enterprise_id
                . "&search_by_1=name"
                . "&search_by_2=id"
                . "&model=User"
        );

        $form->select('administrator_id', "Beneficiary")
            ->options(function ($id) {
                $a = Administrator::find($id);
                if ($a) {
                    return [$a->id => "#" . $a->id . " - " . $a->name];
                }
            })
            ->ajax($ajax_url)->rules('required');

        $form->select('bursary_id', 'Select Service')->options(Bursary::where(
            'enterprise_id',
            Admin::user()->enterprise_id
        )->get()->pluck('name', 'id'))->rules('required');

        $form->textarea('description', __('Description'))->rules('required'); 

        return $form;
    }
}
