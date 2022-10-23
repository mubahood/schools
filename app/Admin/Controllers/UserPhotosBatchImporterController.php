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

        $class = "mc";

        $ids = ['1003935997',
        '1003937850',
        '1003945162',
        '1003944108',
        '1002281068',
        '1003955330',
        '1003463658',
        '1003425513',
        '1003198612',
        '1003198673',
        '1003203427',
        '1003210375',
        '1003238146',
        '1003498846',
        '1003469193',
        '1003270322',
        '1003273321',
        '1003283187',
        '1003772153',
        '1003577222',
        '1003808802',
        '1003811834',
        '1003839918',
        '1003984758',
        '1003866751',
        '1003867224',
        '1003879671',
        '1003880984',
        '1003880986',
        '1003881878',
        '1003885394',
        '1003900952',
        '1003910425',
        '1004048189',
        '1004055570',
        '1004261838',
        '1004269964',
        '1004269996',
        '1004295426',
        '1004302925',
        '1004338869',
        '1003964115',
        '1003273172',
        '1003447071',
        '1003108098',
        '1004366766',
        '1004366769',
        '1004366776',
        '1004366781',
        '1004366784',
        '1004366789',
        '1004366822',
        '1003871162',
        '1003911400',
        '1003940225',
        '1004366833',
        '1004366837',
        '1004366845',
        '1004366863',
        '1004366868',
        '1004366877',
        '1004366978',
        '1004366850',
        '1002363034',
        '1004376794',
        '1004387676',
        '1004387678',
        '1004372526',
        '1004387684',
        '1003885183',
        '1004387690',
        '1004387692',
        '1004387694',
        '1004325079',
        '1004269975',
        '1004338428',
        '1002281065',
        '1004411615',
        '1004294728'
        ];


        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $task = 'compress';

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
