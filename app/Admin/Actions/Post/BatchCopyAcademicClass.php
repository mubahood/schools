<?php
// app/Admin/Actions/Post/BatchCopyAcademicClass.php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use App\Models\AcademicClass;

class BatchCopyAcademicClass extends BatchAction
{
    public $name = 'Copy Class';

    public function handle(Collection $collection)
    {
        $count = 0;

        foreach ($collection as $class) {
            $copy = new AcademicClass();

            // copy only the top-level fields
            $copy->enterprise_id        = $class->enterprise_id;
            $copy->academic_year_id     = $class->academic_year_id;
            $copy->class_teahcer_id     = $class->class_teahcer_id;
            $copy->name                 = $class->name . ' - copy';
            $copy->short_name           = $class->short_name . ' - copy';
            $copy->details              = $class->details;

            $copy->save();
            $count++;
        }

        return $this
            ->response()
            ->success("✅ Copied {$count} class" . ($count > 1 ? 'es' : '') . '.')
            ->refresh();
    }

    public function html()
    {
        return <<<HTML
        <a href="javascript:void(0)" class="btn btn-sm btn-info batch-copy-academic-class">
        <i class="fa fa-copy"></i> Copy Class
        </a>
        HTML;
    }
}
