<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchReplicate extends BatchAction
{
    /**
     * The text shown on the batch‐action button.
     */
    public $name = 'Copy';

    /**
     * Handle the batch action.
     *
     * @param  Collection|\App\Models\StudentDataImport[]  $collection
     * @return \Encore\Admin\Actions\Response
     */
    public function handle(Collection $collection)
    {
        $count = 0;

        foreach ($collection as $import) {
            // duplicate all fillable attributes
            $copy = $import->replicate();

            // modify as required
            $copy->title   = $import->title . ' - copy';
            $copy->status  = 'Pending';
            $copy->summary = null;

            $copy->save();
            $count++;
        }

        return $this
            ->response()
            ->success("✅ Copied {$count} import" . ($count > 1 ? 's' : '') . '.')
            ->refresh();
    }

    /**
     * Render a custom batch‐action button.
     */
    public function html()
    {
        return <<<HTML
<a 
    href="javascript:void(0)" 
    class="btn btn-sm btn-info batch-replicate"
>
    <i class="fa fa-copy"></i> Copy
</a>
HTML;
    }
}
