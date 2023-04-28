<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchStudentFinancialAccountChangeBalance;
use App\Admin\Actions\Post\BatchStudentFinancialAccountSetNotVerified;
use App\Admin\Actions\Post\BatchStudentFinancialAccountSetVerified;
use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\AccountParent;
use App\Models\Enterprise;
use App\Models\Utils;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class StudentFinancialAccountController extends AdminController
{

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Students Accounts';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        /*     set_time_limit(-1);
        $accs = Account::where(['enterprise_id' => 7, 'status' => 0])->get();
        $arr = [113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 259, 260, 261, 262, 263, 264, 267, 268, 269, 270, 271, 272, 273, 274, 275, 276, 277, 278, 279, 280, 281, 282, 283, 284, 285, 286, 287, 288, 289, 290, 291, 292, 293, 294, 295, 296, 297, 298, 299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 315, 316, 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 331, 332, 333, 334, 335, 336, 337, 338, 339, 340, 341, 342, 343, 344, 345, 346, 347, 348, 349, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 360, 361, 362, 363, 364, 365, 366, 367, 368, 369, 370, 371, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 384, 385, 386, 387, 388, 389, 390, 391, 392, 393, 394, 395, 396, 397, 398, 399, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 420, 421, 422, 423, 424, 425, 426, 427, 428, 429, 430, 432, 433, 434, 435, 436, 437, 438, 439, 440, 441, 442, 443, 444, 445, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 457, 458, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 521, 522, 523, 524, 525, 526, 527, 528, 529, 530, 531, 532, 533, 534, 535, 536, 537, 538, 539, 540, 541, 542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 552, 553, 554, 555, 556, 557, 558, 559, 560, 561, 562, 563, 564, 565, 566, 567, 568, 569, 570, 571, 572, 573, 574, 575, 576, 577, 578, 579, 580, 581, 582, 583, 584, 585, 587, 589, 590, 591, 592, 593, 594, 595, 596, 597, 598, 599, 600, 601, 602, 603, 604, 605, 606, 607, 608, 609, 610, 611, 612, 613, 614, 615, 616, 617, 618, 619, 620, 621, 622, 623, 624, 625, 626, 627, 628, 629, 630, 631, 632, 633, 634, 635, 636, 637, 638, 639, 640, 641, 642, 643, 644, 645, 646, 647, 648, 649, 650, 651, 652, 653, 654, 655, 656, 657, 658, 659, 660, 661, 662, 663, 664, 665, 666, 667, 668, 669, 670, 671, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683, 684, 685, 686, 687, 688, 689, 691, 692, 693, 694, 695, 696, 697, 698, 699, 700, 701, 702, 703, 704, 705, 706, 707, 708, 709, 710, 711, 712, 713, 714, 715, 716, 717, 718, 719, 720, 721, 722, 723, 724, 725, 726, 727, 728, 729, 730, 731, 732, 733, 734, 735, 736, 737, 738, 739, 740, 741, 742, 743, 744, 745, 746, 747, 748, 749, 750, 751, 752, 753, 754, 755, 756, 757, 758, 759, 760, 761, 762, 764, 822, 825, 826, 827, 857, 860, 865, 866, 867, 868, 869, 870, 871, 873, 874, 875, 876, 877, 878, 881, 882, 883, 884, 885, 886, 887, 888, 889, 890, 891, 892, 893, 894, 895, 896, 897, 898, 899, 900, 901, 902, 903, 904, 905, 906, 907, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 930, 931, 932, 933, 934, 935, 936, 937, 938, 940, 941, 942, 943, 944, 945, 946, 947, 948, 949, 950, 952, 953, 954, 955, 956, 957, 958, 959, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 972, 973, 974, 975, 976, 978, 979, 980, 981, 982, 983, 984, 985, 987, 988, 989, 990, 991, 992, 993, 1000, 1005, 1007, 1008, 1010, 1011, 1014, 1016, 1019, 1021, 1022, 1023, 1024, 1025, 1026, 1027, 1028, 1030, 1034, 1035, 1036, 1037, 1038, 1039, 1040, 1043, 1044, 1046, 1047, 1048, 1049, 1050, 1051, 1052, 1053, 1056, 1057, 1059, 1065, 1066, 1074, 1075, 1087, 1088, 1089, 1090, 1091, 1094, 1095, 1096, 1097, 1105, 1106, 1107, 1108, 1111, 1113, 1115, 1117, 1118, 1119, 1120, 1125, 1126, 1127, 1128, 1129, 1130, 1132, 1137, 1139, 1141, 1142, 1143, 1144, 1147, 1148, 1149, 1150, 1151, 1152, 1153, 1154, 1155, 1157, 1158, 1159, 1160, 1161, 1162, 1164, 1165, 1166, 1167, 1168, 1169, 1170, 1172, 1173, 1174, 1177, 1693, 1694, 1695, 1696, 1697, 1699, 1701, 1733, 1776, 1777, 1780, 1781, 1782, 1784, 1786, 1787, 1793];
        foreach ($accs as $key => $acc) {
            if (in_array($acc->id, $arr)) {
                $acc->status = 1;
                $acc->save();
                echo $acc->id . " approved<hr>";
            }
        }

        die("done aproving..."); */
        /*  $ac = Account::find(881);
        $ac->name .= "1";
        $ac->want_to_transfer = 'Soap';
        $ac->transfer_keyword = 1;
        $ac->save();
        die("done"); */
        $grid = new Grid(new Account());


        //'academic_class_id'
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'type' => 'STUDENT_ACCOUNT'
            ]);

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new BatchStudentFinancialAccountSetNotVerified());
            $batch->add(new BatchStudentFinancialAccountSetVerified());
            $batch->add(new BatchStudentFinancialAccountChangeBalance());
        });


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();

            $u = Admin::user();
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
                    . "&model=Account"
            );
            $filter->equal('id', 'Student')
                ->select(function ($id) {
                    $a = Account::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);

            $ent = Admin::user()->ent;
            $year = $ent->dpYear();
            //academic_class_id
            $classes = [];
            foreach (AcademicClass::where([
                'enterprise_id' => $u->enterprise_id,
                /*                 'academic_year_id' => $year->id */
            ])->orderBy('id', 'desc')->get() as $key => $v) {
                $classes[$v->id] =  $v->name_text;
            }

            $filter->equal('owner.current_class_id', 'Class')
                ->select($classes);

            $filter->equal('owner.status', 'Student\'s status')
                ->select([
                    1 => 'Active',
                    2 => 'Pending',
                    0 => 'Not Active',
                ]);




            /* $filter->equal('type', 'Account type')->select(
                [
                    'STUDENT_ACCOUNT' => 'Students\' accounts',
                    'EMPLOYEE_ACCOUNT' => 'Employees\' accounts',
                    'BANK_ACCOUNT' => 'Bank accounts',
                    'CASH_ACCOUNT' => 'Cash accounts',
                ]
            );*/

            $filter->group('balance', function ($group) {
                $group->gt('greater than');
                $group->lt('less than');
                $group->equal('equal to');
            });
        });


        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });


        $grid->model()->where('enterprise_id', Admin::user()->enterprise_id)
            ->orderBy('id', 'Desc');

        $grid->column('id', __('#ID'));

        $grid->column('owner.avatar', __('Photo'))
            ->width(80)
            ->lightbox(['width' => 60, 'height' => 60]);



        $grid->column('created_at', __('Created'))->hide()->sortable();
        $grid->column('type', __('Account type'))->hide()->sortable();

        $grid->column('name', __('Account owner'))

            ->display(function () {
                return
                    '<a class="text-dark" href="' . admin_url('students/' . $this->administrator_id) . '">' . $this->name . "</a>";;
            });
        $grid->column('owner.status', __('Student\'s Status'))
            ->using([0 => 'Not active', 1 => 'Active', 2 => 'Pending'])
            ->width(100)
            ->dot([
                0 => 'danger',
                2 => 'danger',
                1 => 'success',
            ])
            ->sortable();
        $grid->column('account_parent_id', __('Account category'))
            ->hide()
            ->display(function () {
                $acc =  Utils::getObject(AccountParent::class, $this->account_parent_id);
                if ($acc == null) {
                    return "None";
                }
                return $acc->name;
            })
            ->sortable();

        /*  $grid->column('name', __('Account Name'))
            ->link()
            ->sortable(); */

        $grid->quickSearch('name')->placeholder('Search by account name');


        $grid->column('owner.current_class_id', __('Class'))
            ->sortable()
            ->display(function ($x) {

                if ($this->owner->current_class == null) {
                    return "-";
                }
                return $this->owner->current_class->short_name;
 
                return "-";
            });

        $grid->column('school', __('School pay'))
            ->hide()
            ->display(function () {
                if ($this->owner->school_pay_payment_code == null) {
                    return "-";
                }
                if (strlen($this->owner->school_pay_payment_code) < 2) {
                    return "-";
                }
                return $this->owner->school_pay_payment_code;
            });

        $grid->column('balance', __('Account balance'))->display(function () {
            return "UGX " . number_format($this->balance);
        })
            ->totalRow(function ($amount) {
                return  "UGX " . number_format($amount);
            })
            ->sortable();
        $grid->column('status', __('Verification'))
            ->filter([0 => 'Pending', 1 => 'Verified'])
            ->using([0 => 'Pending', 1 => 'Verified'])
            ->label([
                0 => 'danger',
                1 => 'success',
            ])
            ->sortable();

        //anjane

        $grid->export(function ($export) {

            $export->filename('Accounts');

            $export->except(['enterprise_id', 'type', 'owner.avatar', 'id']);

            //$export->only(['column3', 'column4']);
            $export->originalValue(['name', 'balance']);
            $export->column('balance', function ($value, $original) {
                return $original;
            });
            $export->column('Student Status', function ($value, $original) {
                if ($original) {
                    return "Verified";
                } else {
                    return "Pending";
                }
            });
            /*
            $export->column('balance', function ($value, $original) {
                return $original;
            }); */
        });

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
        $show = new Show(Account::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('name', __('Name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Account());

        $payable = 0;
        $paid = 0;
        $balance = 0;
        $id = 0;

        if ($form->isEditing()) {

            $params = request()->segments();
            if (isset($params[1])) {
                $id =  $params[1];
            }

            $u = $form->model()->find($id);



            if ($u == null) {
                die("Model not found.");
            }

            if ($u->type == 'STUDENT_ACCOUNT') {

                foreach ($u->transactions as $key => $v) {
                    if ($v->amount < 0) {
                        $payable += $v->amount;
                    } else {
                        $paid += $v->amount;
                    }
                }
                $balance = $payable + $paid;

                $form->display('name', __('Account name'));
                $form->display('payable', __('Total payable fees'))
                    ->default("UGX " . number_format($payable));

                $form->display('paid', __('Total paid fees'))
                    ->default("UGX " . number_format($paid));

                $form->display('paid', __('FEES BALANCE'))
                    ->default("UGX " . number_format($balance));
                $form->divider();
            }

            if ($form->isEditing()) {
                $form->radio('status', "Account verification")
                    ->options([
                        0 => 'Not verified',
                        1 => 'Account verified',
                    ])->rules('required');
            }

            if ($form->isEditing()) {
                $form->radio('new_balance', "Change balance")
                    ->options([
                        0 => 'Don\'t change account balance',
                        1 => 'Change account balance',
                    ])
                    ->when(1, function ($f) {
                        $f->decimal('new_balance_amount', __('New Account Balance'))
                            ->rules('int')->attribute('type', 'number')
                            ->rules('required');
                    })
                    ->default(0)
                    ->rules('required');
            }


            if (!$form->isEditing()) {
                $form->saving(function ($f) {
                    $type = $f->type;
                    $u = Admin::user();
                    $enterprise_id = $u->enterprise_id;
                    $administrator_id = 0;
                    $ent =  Enterprise::find($enterprise_id);
                    if ($ent == null) {
                        die("Enterprise not found.");
                    }
                    $enterprise_owner_id = $ent->administrator_id;
                    $administrator_id = $ent->administrator_id;

                    if ($administrator_id < 1) {
                        $error = new MessageBag([
                            'title'   => 'Error',
                            'message' => "Account ower ID was not found.",
                        ]);
                        return back()->with(compact('error'));
                    }



                    $f->administrator_id = $administrator_id;
                    return $f;
                    /*  $success = new MessageBag([
                'title'   => 'title...',
                'message' => "Good to go!",
            ]);
            return back()->with(compact('success')); */
                });
            }
        } else {




            $u = Admin::user();
            $ent = Enterprise::find($u->enterprise_id);
            $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id)->rules('required');
            $form->hidden('administrator_id', __('Enterprise id'))->default($ent->administrator_id)->rules('required');


            $form->text('name', __('Account name'))
                ->rules('required');


            $form->select('account_parent_id', "Account category")
                ->options(
                    AccountParent::where([
                        'enterprise_id' => Admin::user()->enterprise_id
                    ])->orderBy('name', 'Asc')->get()->pluck('name', 'id')
                )
                ->rules('required');



            if ($form->isEditing()) {
                $form->radio('category', "Account type")
                    ->options(Utils::account_categories())
                    ->readonly()
                    ->rules('required');
            } else {
                $form->hidden('type', "Account type")
                    ->default('OTHER_ACCOUNT')
                    ->rules('required');

                $form->radio('category', "Account type")
                    ->options(Utils::account_categories())
                    ->readonly()
                    ->rules('required');
            }


            $form->radio('transfer_keyword', "Do you want to transfer trannsactions to this account?")
                ->options([
                    1 => 'Yes',
                    0 => 'No',
                ])->when(1, function ($f) {
                    $f->text('want_to_transfer', "Transfer keyword")
                        ->rules('required')
                        ->help("Any transaction containing mentioned keyword in its description should be transfered to this account.");
                })->rules('required');
        }

        $form->textarea('description', __('Account description'));


        /*
            ->when('OTHER_ACCOUNT', function ($f) {
                $u = Admin::user();
                $ajax_url = url(
                    '/api/ajax?'
                        . 'enterprise_id=' . $u->enterprise_id
                        . "&search_by_1=name"
                        . "&search_by_2=id"
                        . "&model=User"
                );
                $f->select('administrator_id', "Account owner")
                    ->options(function ($id) {
                        $a = Account::find($id);
                        if ($a) {
                            return [$a->id => "#" . $a->id . " - " . $a->name];
                        }
                    })
                    ->ajax($ajax_url)->rules('required');
            });*/



        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
