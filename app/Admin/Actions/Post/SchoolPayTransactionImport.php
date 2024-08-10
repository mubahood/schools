<?php

namespace App\Admin\Actions\Post;

use App\Models\AcademicClass;
use App\Models\Term;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class SchoolPayTransactionImport extends BatchAction
{
    public $name = 'Import Transactions';

    public function handle(Collection $collection, Request $r)
    {
        $are_you_sure_you_want = $r->get('are_you_sure_you_want');
        if ($are_you_sure_you_want != 'Yes') {
            return $this->response()->error("You must confirm that you want to change the due term.")->refresh();
        }

        $i = 0;
        foreach ($collection as $model) {
            try {
                $model->doImport();
                $i++;
            } catch (\Exception $e) {
            }
        }
        return $this->response()->success("Imported $i Transactions Successfully.")->refresh();
    }


    public function form()
    {

        $this->radio('are_you_sure_you_want', __('Are you sure you want to import selected Transactions"?'))
            ->options([
                "Yes" => 'Yes',
                'No' => 'No'
            ]);
    }
}
