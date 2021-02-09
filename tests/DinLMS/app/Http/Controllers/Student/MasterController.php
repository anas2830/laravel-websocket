<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use Auth;
use Helper;
use Validator;
use App\Models\User;
use App\Models\EduStudentWidgets_User;
use App\Models\EduAssignBatchStudent_User;
use App\Models\EduAssignBatchClasses_User;
use App\Models\EduStudentWidgetTeacher_User;
use App\Models\EduStudentPracticeTime_User;
use App\Models\EduStudentWidgetsProvider_User;
use App\Models\EduAssignBatch_User;
use App\Models\EduStudentAttendence_User;
use App\Models\EduCourseClassMaterials_User;
use App\Models\EduStudentVideoWatchInfo_User;
use App\Models\EduClassAssignments_User;
use App\Models\EduAssignmentSubmission_User;
use App\Models\EduStudentExam_User;
use App\Models\EduCourses_User;
use App\Models\EduActivityNotify_User;
use App\Models\EduNotifySeenHistory_User;

class MasterController extends Controller
{
    public function logout()
    {
        Auth::guard('student')->logout();
        return redirect()->route('student.login');

    }
    public function home(){

        $authId = Auth::id();
        $current_date = date('Y-m-d');
        $data['userInfo'] = User::where('valid', 1)->find($authId);
        $today_practice_time = EduStudentPracticeTime_User::valid()
            ->where('student_id', $authId)
            ->whereDate('date',$current_date)
            ->first();

        if(!empty($today_practice_time)){
            $today_practice_time = @Helper::secondsToTime($today_practice_time->total_time);
            $time_array = explode(':',$today_practice_time);
            $data['hour'] = $time_array[0];
            $data['minute'] = $time_array[1];
            $data['seconds'] = $time_array[2];
        }else{
            $data['today_practice_time'] = '';
            $data['hour'] = 00;
            $data['minute'] = 00;
            $data['seconds'] = 00;
        }

        $data['widgets'] = EduStudentWidgets_User::valid()->latest()->limit(3)->get();
        $data['student_course_info'] = $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
        $data['running_course_info'] = EduCourses_User::valid()->find($student_course_info->course_id);
        if(!empty($student_course_info)){
            $data['assigned_batch_info'] = EduAssignBatch_User::valid()->find($student_course_info->batch_id);

            $data['upcomming_class'] = EduAssignBatchClasses_User::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.complete_status', 2)  // 2 = running/upcomming
                ->where('edu_assign_batch_classes.batch_id', $student_course_info->batch_id)
                ->where('edu_assign_batch_classes.course_id', $student_course_info->course_id)
                ->first();

            $data['completed_class'] = EduAssignBatchClasses_User::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.complete_status', 1)  // 1 = complete
                ->where('edu_assign_batch_classes.batch_id', $student_course_info->batch_id)
                ->where('edu_assign_batch_classes.course_id', $student_course_info->course_id)
                ->orderBy('edu_assign_batch_classes.start_date','DESC')
                ->first();

            $teacher_personal_news = EduStudentWidgetTeacher_User::valid()
                ->where('batch_id',$student_course_info->batch_id)
                ->where('course_id',$student_course_info->course_id)
                ->where('student_id',$student_course_info->student_id)
                ->where('type',3) // 2 = student wise
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $provider_personal_news = EduStudentWidgetsProvider_User::valid()
                ->where('batch_id',$student_course_info->batch_id)
                ->where('course_id',$student_course_info->course_id)
                ->where('student_id',$student_course_info->student_id)
                ->where('type',3) // 2 = student wise
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $data['all_personal_news'] = array_merge($teacher_personal_news,$provider_personal_news);

            $all_teacher_batch_news = EduStudentWidgetTeacher_User::valid()
                ->where('batch_id',$student_course_info->batch_id)
                ->where('course_id',$student_course_info->course_id)
                ->where('type',2)
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $all_teacher_course_news = EduStudentWidgetTeacher_User::valid()
                ->where('course_id',$student_course_info->course_id)
                ->where('type',1)
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $data['all_teacher_news'] = array_merge($all_teacher_batch_news,$all_teacher_course_news);

            $all_provider_batch_news = EduStudentWidgetsProvider_User::valid()
                ->where('batch_id',$student_course_info->batch_id)
                ->where('course_id',$student_course_info->course_id)
                ->where('type',2)
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $all_provider_course_news = EduStudentWidgetsProvider_User::valid()
                ->where('course_id',$student_course_info->course_id)
                ->where('type',1)
                ->latest()
                ->limit(3)
                ->get()->toArray();

            $data['all_provider_news'] = array_merge($all_provider_batch_news,$all_provider_course_news);

            $cacheKey1 = 'dashboardPieChart'.$authId;
            Cache::remember($cacheKey1, 60*60*24, function () {
                // graph
                $authId = Auth::id();
                $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
                $std_batch_id = $student_course_info->batch_id;
                $std_course_id = $student_course_info->course_id;

                $data['all_assign_classes'] = $all_assign_classes = EduAssignBatchClasses_User::valid()
                    ->where('batch_id', $std_batch_id)
                    ->where('course_id', $std_course_id)
                    ->where('complete_status',1)
                    ->get();

                foreach($all_assign_classes as $key => $assign_class){

                    $assign_class->std_class_name = Helper::className($assign_class->class_id);

                    // class practice time
                    $practice_time = EduStudentPracticeTime_User::valid()
                        ->where('created_by', $authId)
                        ->where('course_id', $std_course_id)
                        ->where('batch_id', $std_batch_id)
                        ->whereDate('date', '>=', $assign_class->start_date)
                        ->whereDate('date', '<=', $assign_class->end_date)
                        ->get();
                    
                    $need_practice_time = count($practice_time) * 14400;
                    $do_practice_time = $practice_time->sum('total_time');

                    if($need_practice_time > 0){
                        $assign_class->std_class_practiceTime =  round((($do_practice_time*100) / $need_practice_time), 2);
                    }else{
                        $assign_class->std_class_practiceTime = 0;
                    }

                    // end class practice time

                    // attendence and class mark
                    $std_attend_class = EduStudentAttendence_User::valid()
                        ->where('class_id', $assign_class->id)
                        ->where('student_id', $authId)
                        ->where('is_attend', 1)
                        ->first();

                    if(!empty($std_attend_class)){
                        $assign_class->std_class_attend = 100;
                        $assign_class->std_class_mark = $std_attend_class->mark;
                    }else{
                        $assign_class->std_class_attend = 0;
                        $assign_class->std_class_mark = 0;
                    }
                    // end attendence and class mark

                    // class video watch time
                    $class_video_time = EduCourseClassMaterials_User::valid()
                        ->where('course_id',$assign_class->course_id)
                        ->where('class_id',$assign_class->class_id)
                        ->sum('video_duration');

                    $class_watch_video = EduStudentVideoWatchInfo_User::valid()
                        ->where('assign_batch_class_id', $assign_class->id)
                        ->where('student_id', $authId)
                        ->sum('watch_time');

                    if($class_video_time > 0){
                        $assign_class->std_class_video = round((($class_watch_video*100)/$class_video_time), 2);
                    }else{
                        $assign_class->std_class_video = 0;
                    }
                    // end class video watch time

                    // class assignment
                    $total_assignment  = EduClassAssignments_User::valid()
                        ->where('batch_id',$assign_class->batch_id)
                        ->where('assign_batch_class_id',$assign_class->id)
                        ->get()->pluck('id')->toArray();
                        

                    $assignment_mark = EduAssignmentSubmission_User::valid()
                        ->whereIn('assignment_id', $total_assignment)
                        ->where('created_by', $authId)
                        ->sum('mark');
                    
                        if(count($total_assignment) > 0) {
                            $total_assignment_mark = count($total_assignment) * 100;
                            $assign_class->std_class_assignment = round((($assignment_mark*100) / $total_assignment_mark), 2);
                        }else{
                            $assign_class->std_class_assignment = 0;
                        }
                    // end class assignment

                    //class exam
                    $std_exam = EduStudentExam_User::valid()
                        ->where('assign_batch_class_id',$assign_class->id)
                        ->where('student_id',$authId)
                        ->first();

                    if(!empty($std_exam)){
                        $class_exam_mark = $std_exam->total_questions * $std_exam->per_question_mark;
                        $taken_class_exam_mark = $std_exam->total_correct_answer * $std_exam->per_question_mark;
                        $assign_class->std_class_exam = round(($taken_class_exam_mark*100) / $class_exam_mark);
                    }else{
                        $assign_class->std_class_exam = 0;
                    }
                    //end class exam
                }
                return $all_assign_classes;
            });
            $data['all_assign_classes'] = Cache::get($cacheKey1);

            // end graph


        } else {
            $data['assigned_batch_info'] = '';
            $data['upcomming_class'] = [];
            $data['completed_class'] = [];
            $data['all_personal_news'] = [];
            $data['all_batch_news'] = [];
            $data['all_provider_news'] = [];
            $data['all_teacher_news'] = [];
            $data['all_assign_classes'] = [];
        }

        return view('student.home', $data);

    }

    public function notifySeen(Request $request){
        $authId = Auth::id();
        $notify_id = $request->notify_id;
        $notify_info = EduActivityNotify_User::valid()->find($notify_id);

        if($notify_info->assign_batch_class_id != null){
            $assign_batch_class_id = $notify_info->assign_batch_class_id;
        }else{
            $assign_batch_class_id = null;
        }

        $check_seen = EduNotifySeenHistory_User::valid()->where('notify_id',$notify_id)->where('created_by',$authId)->first();
        if(empty($check_seen)){
            EduNotifySeenHistory_User::create([
                'notify_id'    => $notify_id,
                'assign_batch_class_id' => $assign_batch_class_id
            ]);
            $output['message'] = "Seen status update successfully";
        }else{
            $output['message'] = 'alerady seen';
        }

        return response($output);
    }

}
