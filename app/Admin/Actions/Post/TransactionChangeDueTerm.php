<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use App\Models\Term;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class TransactionChangeDueTerm extends BatchAction
{
    public $name = 'Change Due Term';

    public function handle(Collection $collection, Request $r)
    {
        $are_you_sure_you_want = $r->get('are_you_sure_you_want');
        if ($are_you_sure_you_want != 'Yes') {
            return $this->response()->error("You must confirm that you want to change the due term.")->refresh();
        }
        $term = Term::find($r->get('destination_term_id'));
        if ($term == null) {
            return $this->response()->error("Due term not found.")->refresh();
        }
        $i = 0;
        foreach ($collection as $model) {
            $model->term_id = $term->id;
            $i++;
            $model->save();
        }
        return $this->response()->success("Changed $i Transactions Successfully.")->refresh();
    }


    public function form()
    {

        $this->select('destination_term_id', __('Destination Term (Term to be changed to)'))
            ->options(Term::getItemsToArray([
                'enterprise_id' => Admin::user()->enterprise_id
            ]));
        $this->radio('are_you_sure_you_want', __('Are you sure you want to change the due term?'))
            ->options([
                "Yes" => 'Yes',
                'No' => 'No'
            ]);
    }
}
