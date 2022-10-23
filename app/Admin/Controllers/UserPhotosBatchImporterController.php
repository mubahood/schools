<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
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

        $class = "tc";

        $ids = ['1002281104',
        '1003964104',
        '1003964658',
        '1003438138',
        '1003453337',
        '1003479160',
        '1003469169',
        '1003273295',
        '1003577176',
        '1003586600',
        '1003587495',
        '1003778083',
        '1003801894',
        '1003808789',
        '1003840984',
        '1003863660',
        '1003866733',
        '1003868195',
        '1003877966',
        '1003884431',
        '1003888102',
        '1003900945',
        '1003901128',
        '1004083405',
        '1004273230',
        '1004284239',
        '1004287767',
        '1004295213',
        '1004302634',
        '1004316105',
        '1004323045',
        '1002281059',
        '1002281052',
        '1002281045',
        '1004367131',
        '1003314097',
        '1003286754',
        '1004367138',
        '1004367165',
        '1003312743',
        '1003778140',
        '1002281079',
        '1002281086',
        '1004367169',
        '1004367170',
        '1003445829',
        '1004367172',
        '1004367177',
        '1002489613',
        '1004367481',
        '1004367482',
        '1004367486',
        '1004367496',
        '1004367499',
        '1004372767',
        '1002281061',
        '1002281077',
        '1004387642',
        '1004387645',
        '1004387648',
        '1004387650',
        '1004387655',
        '1004387658',
        '1004387663',
        '1002281043',
        '1004411670',
        ];


        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $task = 'compress';

        if ($task != 'compress') {
            $path = Utils::docs_root() . "temp/{$class}_thumb";
            $path2 = Utils::docs_root() . "temp/{$class}";
            $files = scandir($path, 0);
            dd(count($files));
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
            $path = Utils::docs_root() . "temp/{$class}";
            $files = scandir($path, 0);
            $x = 0;
            foreach ($files as $f) {
                $ext = pathinfo($f, PATHINFO_EXTENSION);
                if ($ext != 'jpg') {
                    continue;
                }
                if (isset($ids[$x])) {

                    $image = new Zebra_Image();
                    $image->handle_exif_orientation_tag = false;
                    $image->preserve_aspect_ratio = true;
                    $image->enlarge_smaller_images = true;
                    $image->preserve_time = true;
                    $image->jpeg_quality = 80;
                    $id = ((String)(str_replace('.jpg','',$f)));
                  
                    $image->auto_handle_exif_orientation = true;
                    $image->source_path =  $path . "/" . $f;
                    $image->target_path =  Utils::docs_root() .'storage/images/' . $f;
                    if (!$image->resize(413, 531, ZEBRA_IMAGE_CROP_CENTER)) {
                        // if no errors
                        dd("failed");
                    }
        
                    $s = Administrator::where([
                        'school_pay_payment_code' => $id
                     ])->first();
                     if($s == null){
                        echo "<hr>{$id}<hr>NOT FOUND<hr>";
                        continue;
                     }
                     $s->avatar = $f;
                     $s->save();
        
                    echo '<img src="' . url('temp/'.$class."/".$f) . '" width="300" />';
                    echo '<img src="' . url($s->avatar) . '" width="300"/><hr>';

 
                }
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
