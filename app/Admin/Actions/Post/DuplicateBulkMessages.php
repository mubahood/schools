<?php

namespace App\Admin\Actions\Post;

use App\Models\BulkMessage;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class DuplicateBulkMessages extends BatchAction
{
    public $name = 'Duplicate';

    public function dialog()
    {
        $this->confirm('Duplicate the selected bulk message(s)?', 'Copies will be saved as Draft with no messages generated. Edit each copy and change "Sending Action" to Send when ready.');
    }

    public function handle(Collection $collection)
    {
        $count = 0;

        foreach ($collection as $original) {
            /** @var BulkMessage $original */

            // Prevent the boot events from auto-generating DirectMessages for copies.
            BulkMessage::$skipAutoGenerate = true;

            $copy = $original->replicate();
            $copy->message_title = $original->message_title . ' (Copy)';
            $copy->send_action   = 'Draft';
            $copy->send_confirm  = 'No';
            $copy->clone_action  = null;
            $copy->clone_confirm = null;
            $copy->created_at    = now();
            $copy->updated_at    = now();
            $copy->save();

            BulkMessage::$skipAutoGenerate = false;

            $count++;
        }

        return $this->response()
            ->success("Duplicated {$count} bulk message" . ($count > 1 ? 's' : '') . ' as Draft.')
            ->refresh();
    }

    public function html()
    {
        return <<<HTML
<a class="btn btn-sm btn-info" href="javascript:void(0)">
    <i class="fa fa-copy"></i> Duplicate
</a>
HTML;
    }
}
