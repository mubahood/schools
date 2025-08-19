<?php

namespace App\Admin\Controllers;

use App\Models\ReportComment;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReportCommentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report Comments';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $range = 20;
        //need to generate dummy of comments like 5 comments for each range of 20. make use of keywords and comments should sound very well like human
        // Generate dummy comments for ranges of 20 (0-19, 20-39, 40-59, 60-79, 80-100)
        if (ReportComment::where('enterprise_id', auth()->user()->enterprise_id)->count() == 0) {
            $dummyComments = [
                // Range 0-12 (Excellent Performance - DIV 1)
                [
                    ['min_score' => 0, 'max_score' => 12, 'comment' => '[NAME] deserves to be rewarded by the parent for the excellent work done. [HE_OR_SHE] has reflected shining colors in [HIS_OR_HER] results, thank you, keep it up.', 'hm_comment' => 'Outstanding achievement by [NAME]. [HE_OR_SHE] continues to excel and set high standards for others.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => 'Very encouraging results displayed by [NAME], do not relax, continue excelling. The grades reflected are excellent, keep it up.', 'hm_comment' => 'Exceptional performance demonstrated. [NAME] should maintain this excellent momentum throughout.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => 'Thank you [NAME] for this excellent work reflected, work even harder to stay shining. This excellent performance deserves a parents\' reward.', 'hm_comment' => '[NAME] has shown remarkable dedication. We commend [HIM_OR_HER] for this outstanding academic achievement.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => '[NAME] has reflected the shining colors in [HIS_OR_HER] results, thank you, keep it up. Very good results reflected, keep the spirit up.', 'hm_comment' => 'Brilliant work by [NAME]. [HE_OR_SHE] consistently demonstrates excellence in all areas.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => 'Very encouraging results reflected by [NAME], do not relax. Thank you for the excellent work, keep it up.', 'hm_comment' => '[NAME] deserves special recognition for [HIS_OR_HER] outstanding performance this term.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => 'Excellent performance by [NAME], continue with this spirit. The work done is commendable, keep shining.', 'hm_comment' => 'Superb results achieved by [NAME]. [HE_OR_SHE] is truly an inspiration to fellow students.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => '[NAME] has done exceptionally well, maintain this excellent standard. The results are truly outstanding.', 'hm_comment' => 'Congratulations to [NAME] for this excellent achievement. [HIS_OR_HER] hard work has paid off.'],
                    ['min_score' => 0, 'max_score' => 12, 'comment' => 'Outstanding work by [NAME], continue excelling in all subjects. This performance is worth celebrating.', 'hm_comment' => '[NAME] has exceeded expectations. We are proud of [HIS_OR_HER] consistent excellent performance.'],
                ],
                // Range 13-24 (Good Performance - DIV 2)
                [
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Very good work [NAME], thank you so much, however, keep working hard. A steady progress has been shown by [HIM_OR_HER].', 'hm_comment' => 'Good improvement shown by [NAME]. With more effort, [HE_OR_SHE] can achieve even better results.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Thank you [NAME] for improving, but more effort is required to do even better. There is good progress displayed, keep reading hard.', 'hm_comment' => '[NAME] demonstrates steady progress. We encourage [HIM_OR_HER] to aim for excellence next term.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Well done [NAME] with your continuous improvement, however, read even harder. A gradual improvement has been reflected, keep working even harder.', 'hm_comment' => 'Commendable effort by [NAME]. [HE_OR_SHE] should continue working diligently for better outcomes.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Thank you [NAME] for the improvement, however, double your effort for better results. Good progress reflected, continue reading hard.', 'hm_comment' => '[NAME] shows promising development. With sustained effort, [HE_OR_SHE] will achieve greater success.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'There is an improvement shown by [NAME], however, more effort is needed. Aim higher so as to attain better results next term.', 'hm_comment' => 'Well done [NAME] for the positive changes. Keep pushing forward for excellent results.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Good work [NAME], but there is room for improvement. Continue with this positive trend in your studies.', 'hm_comment' => '[NAME] is on the right track. We believe [HE_OR_SHE] can achieve much more with dedication.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => '[NAME] has shown good progress, however, strive for excellence. The improvement is encouraging, keep it up.', 'hm_comment' => 'Positive development noted in [NAME]. [HIS_OR_HER] consistent effort will lead to greater achievements.'],
                    ['min_score' => 13, 'max_score' => 24, 'comment' => 'Encouraging results by [NAME], but aim higher next term. There is potential for even better performance.', 'hm_comment' => '[NAME] shows good academic growth. We look forward to seeing [HIM_OR_HER] reach new heights.'],
                ],
                // Range 25-28 (Fair Performance - DIV 3)
                [
                    ['min_score' => 25, 'max_score' => 28, 'comment' => '[NAME] is continuously improving, however, parental involvement will help [HIM_OR_HER] concentrate more. There is some progress displayed.', 'hm_comment' => '[NAME] needs additional encouragement to reach [HIS_OR_HER] potential. Close monitoring will help.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => 'There is some progress displayed by [NAME], but continue motivating and encouraging [HIM_OR_HER]. A slight improvement has been shown.', 'hm_comment' => 'Fair progress by [NAME]. With proper guidance, [HE_OR_SHE] can improve significantly.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => 'A slight improvement has been shown by [NAME], however, concentrate more next term. There is still need for more effort.', 'hm_comment' => '[NAME] requires consistent support to enhance [HIS_OR_HER] academic performance.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => 'A slight gradual progress is shown by [NAME], but double the effort for better results. More concentration is needed.', 'hm_comment' => 'Some improvement noted in [NAME]. Continued encouragement will boost [HIS_OR_HER] confidence.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => '[NAME] shows some improvement, but needs to work much harder. Parental support will make a difference.', 'hm_comment' => '[NAME] has potential but needs focused attention to unlock [HIS_OR_HER] abilities.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => 'Fair performance by [NAME], however, more effort is required. [HE_OR_SHE] can do better with dedication.', 'hm_comment' => 'We see gradual progress in [NAME]. Strategic support will help [HIM_OR_HER] improve further.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => 'Some progress noted by [NAME], but [HE_OR_SHE] needs to concentrate more in class. Keep encouraging [HIM_OR_HER].', 'hm_comment' => '[NAME] shows signs of improvement. Consistent motivation will enhance [HIS_OR_HER] performance.'],
                    ['min_score' => 25, 'max_score' => 28, 'comment' => '[NAME] is slowly improving, however, parental guidance is crucial. There is still room for growth.', 'hm_comment' => 'Modest progress by [NAME]. We recommend additional support to help [HIM_OR_HER] succeed.'],
                ],
                // Range 29-32 (Weak Performance - DIV 4)
                [
                    ['min_score' => 29, 'max_score' => 32, 'comment' => '[NAME] needs a lot of parental help so as to concentrate more in class. [HE_OR_SHE] has a long way to go.', 'hm_comment' => 'Serious concern about [NAME]\'s progress. Immediate parental intervention is needed.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => 'There is still a long way to go for [NAME], please parent, help the child through motivation. More effort is required.', 'hm_comment' => '[NAME] requires intensive support. A collaborative approach will help [HIM_OR_HER] improve.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => '[NAME] will do better with close continuous parental supervision. [HE_OR_SHE] needs guidance and encouragement.', 'hm_comment' => 'We are concerned about [NAME]\'s academic development. Direct involvement is essential.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => 'There is need for direct parental involvement to help [NAME] concentrate more. [HIS_OR_HER] performance needs improvement.', 'hm_comment' => '[NAME] struggles academically. Sustained support and encouragement are crucial for progress.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => '[NAME] requires extra attention and support at home. [HE_OR_SHE] can improve with proper guidance.', 'hm_comment' => 'Poor performance by [NAME]. We recommend immediate intervention and close monitoring.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => 'Weak performance shown by [NAME], parental assistance is urgently needed. [HIS_OR_HER] concentration needs improvement.', 'hm_comment' => '[NAME] faces academic challenges. Joint efforts between home and school are needed.'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => '[NAME] is struggling academically, please provide extra support at home. More supervision is required.', 'hm_comment' => 'Significant concern about [NAME]\'s progress. We need to work together to help [HIM_OR_HER].'],
                    ['min_score' => 29, 'max_score' => 32, 'comment' => 'Poor results by [NAME], [HE_OR_SHE] needs constant motivation and guidance. Parental help is crucial.', 'hm_comment' => '[NAME] requires special attention. We will develop strategies to support [HIS_OR_HER] learning.'],
                ],
                // Range 33-36 (Ungraded/Failure - U/F)
                [
                    ['min_score' => 33, 'max_score' => 36, 'comment' => '[NAME] still has a lot to do, so a parent-teacher joint assistance is needed. [HE_OR_SHE] needs motivation and encouragement.', 'hm_comment' => 'Very poor performance by [NAME]. Urgent intervention and support plan required immediately.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => '[NAME] needs a lot of parental motivation and encouragement so as to change [HIS_OR_HER] performance. A lot of effort is needed.', 'hm_comment' => 'Critical concern about [NAME]\'s academic status. Comprehensive support strategy needed.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => 'Direct parent supervision is needed for [NAME] both at home and school. A lot more parental involvement is needed.', 'hm_comment' => '[NAME] requires immediate academic rescue intervention. All stakeholders must get involved.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => 'There is still more parental help needed to help [NAME] change performance. [HE_OR_SHE] requires guidance and motivation.', 'hm_comment' => 'Emergency academic support needed for [NAME]. We must act quickly to help [HIM_OR_HER].'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => 'Parental involvement and encouragement will help to motivate [NAME]. A parent-teacher joint assistance is needed.', 'hm_comment' => '[NAME] faces serious academic difficulties. Intensive remedial support is essential.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => 'A lot of parental guidance and one-on-one assistance is needed for [NAME]. [HE_OR_SHE] must work much harder.', 'hm_comment' => 'Unacceptable performance by [NAME]. Immediate action plan required for academic recovery.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => '[NAME] requires intensive support through guidance and motivation. There is urgent need for improvement.', 'hm_comment' => 'Grave concern about [NAME]\'s academic future. Emergency intervention measures needed.'],
                    ['min_score' => 33, 'max_score' => 36, 'comment' => 'Very poor performance by [NAME], [HE_OR_SHE] needs constant supervision and encouragement. Joint effort is required.', 'hm_comment' => '[NAME] is at risk academically. All hands must be on deck to support [HIS_OR_HER] learning.'],
                ],
            ];

            foreach ($dummyComments as $commentGroup) {
                foreach ($commentGroup as $commentData) {
                    $reportComment = new ReportComment();
                    $reportComment->enterprise_id = auth()->user()->enterprise_id;
                    $reportComment->min_score = $commentData['min_score'];
                    $reportComment->max_score = $commentData['max_score'];
                    $reportComment->comment = $commentData['comment'];
                    $reportComment->hm_comment = $commentData['hm_comment'];
                    $reportComment->save();
                }
            }
        }

        $grid = new Grid(new ReportComment());
        $grid->model()->where('enterprise_id', auth()->user()->enterprise_id)->orderBy('min_score', 'asc');

        $grid->column('id', __('Id'));
        $grid->column('min_score', __('Min Score'))
            ->sortable()
            ->editable();

        $grid->column('max_score', __('Max Score'))
            ->sortable()
            ->editable();
        $grid->column('comment', __('Comment'))
            ->sortable()
            ->editable('textarea');
        $grid->column('hm_comment', __('Head Teacher Comment'))
            ->sortable()
            ->editable('textarea');

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
        $show = new Show(ReportComment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('enterprise_id', __('Enterprise id'));
        $show->field('min_score', __('Min score'));
        $show->field('max_score', __('Max score'));
        $show->field('comment', __('Comment'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportComment());

        $u = Admin::user();
        $form->hidden('enterprise_id', __('Enterprise id'))->default($u->enterprise_id);
        /*     $form->radio('type', 'Commenting Type')->options([
            'MARKS' => 'Use marks score',
            'AGGREGATE' => 'Use aggregates (Grading)',
        ])->default('MARKS')->rules('required')->required();  */

        $form->decimal('min_score', __('Min Score (Aggregates)'))->rules('required|numeric|min:0|max:100')->required();
        $form->decimal('max_score', __('Max Score (Aggregates)'))->rules('required|numeric|min:0|max:100|gte:min_score')->required();

        $key_words = [
            '[NAME]',
            '[HE_OR_SHE]',
            '[HIS_OR_HER]',
            '[HIM_OR_HER]',
        ];


        $form->textarea('comment', __('Class Teacher Comment'))
            ->rules('required|string|max:500|min:5')->required();

        //hm_comment
        $form->textarea('hm_comment', __('Head Teacher Comment'))
            ->rules('required|string|max:500|min:5')->required();

        $form->html('You can use the following keywords in your comment: ' . implode(', ', array_map(function ($word) {
            return '<code>' . $word . '</code>';
        }, $key_words)) . " to be replaced with the corresponding values.");

        return $form;
    }
}
