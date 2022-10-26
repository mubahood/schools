<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\StudentHasClass;
use App\Models\StudentHasTheologyClass;
use App\Models\TheologyMark;
use App\Models\UserBatchImporter;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Maatwebsite\Excel\Facades\Excel;
use Zebra_Image;

use function PHPUnit\Framework\fileExists;

class UserPhotosBatchImporterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User photos importer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */



    protected function grid()
    {
        $x = 0;
        $y = 0;
        foreach (StudentHasTheologyClass::all() as  $s) {

            foreach ($s->class->subjects as  $sub) {
                $marks = TheologyMark::where([
                    'student_id' => $s->administrator_id,
                    'theology_exam_id' => 1,
                    'theology_subject_id' => $sub->id,
                    'theology_class_id' => $s->theology_class_id,
                ])->get();
                $x++;
                if ((count($marks)) ==  1) {
                    continue;
                }
                if ((count($marks)) >  1) {
                    $done_first = false;
                    foreach ($marks as $mark) {
                        if (!$done_first) {
                            $done_first = true;
                            $mark->is_submitted = 0;
                            echo (" deleted ==> $mark->score <hr>");
                            $mark->save();
                            continue;
                        }
                        echo (" deleted ==> $mark->score <hr>");
                        $mark->delete();
                    }
                }

                if ((count($marks)) == 1) {
                    $new_mark = new TheologyMark();
                    $new_mark->theology_exam_id     = 1;
                    $new_mark->enterprise_id     = 7;
                    $new_mark->theology_class_id     = $s->theology_class_id;
                    $new_mark->theology_subject_id     = $sub->id;
                    $new_mark->teacher_id     = $sub->subject_teacher;
                    $new_mark->student_id     = $s->administrator_id;
                    $new_mark->score     = 0;
                    $new_mark->remarks     = '';
                    $new_mark->is_submitted     = false;
                    $new_mark->is_missed     = true;
                    $new_mark->save();

                }
            }
        }

        die(" done =>$x<= ==>$y<==");

        $class = "baby";
        
        foreach (StudentHasClass::where([
            'academic_class_id' => 15
        ])->get() as $key => $v) {
            $path_1 = Utils::docs_root() . "storage/images/".$v->student->avatar;
            $path_2 = Utils::docs_root() . "temp/{$class}/".$v->student->avatar;
            if(file_exists($path_1)){
                copy($path_1,$path_2); 
            }
 
        }

        die("done");




        /* 
        $users = Administrator::all();

        $X = 1;
        foreach ($users as $u) {
            $u->phone_number_1 = Utils::prepare_phone_number($u->phone_number_1);
            $u->phone_number_2 = Utils::prepare_phone_number($u->phone_number_2);
            echo $u->phone_number_1."<hr>";
            $u->save();
            $X++;
        }
        die("DONE ===> $X <===");
 */

        /*  $path = Utils::docs_root() . "temp";
        $path_2 = $Utils::docs_root() . "storage/images";
        $files = scandir($path, 0);
        $x = 0;
        foreach ($files as $f) {
            $ext = pathinfo($f, PATHINFO_EXTENSION);
            if ($ext != 'jpg') {
                continue;
            }
            $base_name = str_replace("." . $ext, "", $f);


            $u = Administrator::where([
                'user_id' => $base_name
            ])->first();
            if ($u != null) {
                $new_file = $path_2 . "/" . $f;
                $old_file = $path . "/" . $f;
                $u->avatar = $base_name.".jpg"; 
                $u->save();
                echo $x."<hr>";
                rename($old_file, $new_file);
            } 
            $x++;
        }
 */


        // $x = UserBatchImporter::find(11);
        // $x = UserBatchImporter::user_photos_batch_import($x);
        // dd("done");

        $class = "p7";

        $ids = [
            '1003626755',
            '1003636283',
            '1003654385',
            '1003935921',
            '1003661979',
            '1003282976',
            '1003587270',
            '1003587279',
            '1003587650',
            '1004289069',
            '1002281402',
            '1002281366',
            '1002281373',
            '1002281372',
            '1002281419',
            '1002281392',
            '1002281395',
            '1002281418',
            '1002281414',
            '1002281400',
            '1002281376',
            '1002281379',
            '1002281417',
            '1002281408',
            '1002281396',
            '1002281391',
            '1002281374',
            '1004372487',
            '1002281411',
            '1004372499',
            '1002281394',
            '1002281403',
            '1002281409',
            '1004385898',
            '1002281420',
            '1002281390',
            '1002281387',
            '1002281398',
            '1003937120'
        ];


        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $task = 'compress';
        $class= 'p6';
        if ($task != 'compress') {
            $path = Utils::docs_root() . "temp/{$class}_thumb";
            $path2 = Utils::docs_root() . "temp/{$class}";
            $files = scandir($path, 0);

            $x = 0;
            foreach ($files as $f) {
                $ext = pathinfo($f, PATHINFO_EXTENSION);
                if ($ext != 'jpg') {
                    continue;
                }
                if (isset($ids[$x])) {
                    $new_file = $path2 . "/" . $ids[$x] . ".jpg";
                    $old_file = $path . "/" . $f;
                    copy($old_file, $new_file);
                    print($x . " === " . $ids[$x] . "<hr>");
                }
                $x++;
            }
            die("done");
        } else {
            $path = Utils::docs_root() . "temp/{$class}_thumb";
            $files = scandir($path, 0);
            $x = 0;
            foreach ($files as $f) {
                $ext = pathinfo($f, PATHINFO_EXTENSION);
                if ($ext != 'jpg') {
                    continue;
                } 
 

                    $image = new Zebra_Image();
                    $image->handle_exif_orientation_tag = false;
                    $image->preserve_aspect_ratio = true;
                    $image->enlarge_smaller_images = true;
                    $image->preserve_time = true;
                    $image->jpeg_quality = 80;
                    $id = ((string)(str_replace('.jpg', '', $f)));

                    $image->auto_handle_exif_orientation = true;
                    $image->source_path =  $path . "/" . $f;
                    $image->target_path =  Utils::docs_root() .  "temp/{$class}/" . $f;
                    if (!$image->resize(413, 531, ZEBRA_IMAGE_CROP_CENTER)) {
                        // if no errors
                        dd("failed");
                    }
  

                    echo $x.'<img src="' . url('temp/' . $class . "_thumb/" . $f) . '" width="300" />';
                    echo '<img src="' . url(url("temp/{$class}/" . $f)) . '" width="300"/><hr>';
              
                $x++;
            }

            dd("compressing...");
        }



        dd("romina " . count($ids));


        /*
        die("time to rename_images");
        $x = UserBatchImporter::find(35);
        $x->academic_class_id = rand(100000000, 1000000000000);
        $x->save();
        die("romina");*/


        /* $url = Utils::docs_root() . "pics/1.zip";
        $dest = Utils::docs_root() . "pics/1";
        if (!file_exists($url)) {
            dd("FILE DNE => $url");
        }

        if (UserBatchImporterController::unzip($url, $dest)) {
            die('GOOOOOD');
        } else {
            die('BAAD   ');
        }
        dd("romina");
        dd($url);
 */

        /*  $x = new UserBatchImporter();
        $x->enterprise_id = 6;
        $x->academic_class_id = 1;
        $x->type = 'students';
        $x->file_path = 'files/students.xlsx';
        $x->imported = 0;

        $x->save(); */
        $grid = new Grid(new UserBatchImporter());

        $grid->header(function ($query) {
            $link = url('assets/files/students-template.xlsx');
            return "Download Students <b>batch importation excel template</b> 
            
            <a target=\"_blank\" href=\"$link\" download>here.</a>
            <br>
            <b>NOTE</b> Only feed in data of students in a particular class. Don't temper with the structure of the file.
            ";
        });

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->model()->where(
            [
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'photos'
            ]
        )
            ->orderBy('id', 'Desc');

        $grid->column('id', __('Id'))->sortable();

        /*         $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at')); 
        $grid->column('enterprise_id', __('Enterprise id'));*/
        $grid->column('description', __('Description'));
        /*         $grid->column('academic_class_id', __('Description'))
            ->display(function ($academic_class_id) {
                $class = AcademicClass::find($academic_class_id);
                $count  = count($this->users);
                $class_name = "-";
                if ($class != null) {
                    $class_name = $class->name;
                }
                return "Imported $count students to $class_name ";
            }); */
        /*  $grid->column('type', __('Type')); */

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
        $show = new Show(UserBatchImporter::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('academic_class_id', __('Academic class id'));
        $show->field('type', __('Type'));
        $show->field('file_path', __('File path'));
        $show->field('imported', __('Imported'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserBatchImporter());

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();


        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
        $form->hidden('type', __('type'))->default('photos')->value('photos');
        $form->hidden('imported', __('imported'))->default(0)->rules('required');
        $form->hidden('academic_class_id', __('academic_class_id'))->default(1)->rules('required');


        $form->file('file_path', __('File'))
            ->attribute('accept', '.zip')
            ->rules('required');

        return $form;
    }
}
