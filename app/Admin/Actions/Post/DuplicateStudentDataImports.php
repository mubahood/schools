<?php

namespace App\Admin\Actions\Post;

use App\Models\StudentDataImport;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class DuplicateStudentDataImports extends BatchAction
{
    public $name = 'Duplicate';

    /**
     * Ask for confirmation before running.
     */
    public function dialog()
    {
        $this->confirm('Are you sure you want to duplicate the selected imports?');
    }

    /**
     * Handle the batch action.
     *
     * @param  Collection|StudentDataImport[]  $collection
     * @return \Encore\Admin\Actions\Response
     */
    public function handle(Collection $collection)
    {
        $count = 0;

        foreach ($collection as $import) {
            /** @var StudentDataImport $copy */
            $copy = $import->replicate();             // clone all fillable attributes
            $copy->title   = $import->title . ' - copy'; // append “– copy”
            $copy->status  = 'Pending';               // reset to Pending
            $copy->summary = null;                    // clear any summary
            $copy->save();
            $count++;
        }

        return $this
            ->response()
            ->success("✅ Duplicated {$count} import" . ($count > 1 ? 's' : '') . '.')
            ->refresh();
    }

    /**
     * Render a custom button.
     */
    public function html()
    {
        return <<<HTML
<a 
   class="duplicate-posts btn btn-sm btn-info"
   href="javascript:void(0)"
>
   <i class="fa fa-copy"></i> Duplicate
</a>
HTML;
    }
}
