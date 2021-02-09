<?php

namespace App\Http\Controllers\Provider;
use DB;
use Helper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EduAssignBatch_Provider;
use App\Models\EduAssignBatchSchedule_Provider;
use App\Models\EduAssignBatchClasses_Provider;
use App\Models\EduStudent_Provider;
use App\Models\EduStudentAttendence_Provider;
use App\Models\EduClassAssignments_Provider;
use App\Models\EduAssignmentSubmission_Provider;
use App\Models\EduStudentPracticeTime_Provider;
use App\Models\EduStudentVideoWatchInfo_Provider;
use App\Models\EduStudentExam_Provider;
use App\Models\EduAssignBatchStudent_Provider;
use App\Models\EduExamConfig_Provider;

class AnalysisActivityController extends Controller
{
    public function index()
    {

        $data['all_batches'] = $all_batches = EduAssignBatch_Provider::join('edu_courses', 'edu_courses.id', '=', 'edu_assign_batches.course_id')
            ->select('edu_assign_batches.*', 'edu_courses.course_name')
            ->where('edu_assign_batches.valid', 1)
            ->get();
        
        foreach($all_batches as $key =>$batch){
            $batch->schedules = EduAssignBatchSchedule_Provider::valid()->where('batch_id',$batch->id)->get();
            $batch->total_class = EduAssignBatchClasses_Provider::valid()
                ->where('batch_id',$batch->id)
                ->where('course_id',$batch->course_id)
                ->count();
        }

        return view('provider.analysisActivity.listData', $data);

    }

    public function batchStudents(Request $request)
    {   
        $batch_id =  $request->batch_id;
        $data['all_students'] = $all_students = EduStudent_Provider::join('edu_assign_batch_students','edu_assign_batch_students.student_id','=','users.id')
            ->select('users.*','edu_assign_batch_students.course_id')
            ->where('edu_assign_batch_students.batch_id',$batch_id)
            ->where('edu_assign_batch_students.is_running',1)
            ->where('edu_assign_batch_students.active_status',1)
            ->where('users.valid',1)
            ->get();

        $assign_batch_class_ids = EduAssignBatchClasses_Provider::valid()
            ->where('batch_id',$batch_id)
            ->where('complete_status',1)
            ->pluck('id')->toArray();

        $assign_batch_first_class_date = EduAssignBatchClasses_Provider::valid()
            ->where('batch_id',$batch_id)
            ->where('complete_status',1)
            ->orderBy('class_id', 'asc')
            ->first()->start_date;

        $today_date = date('Y-m-d');
        $start = strtotime($assign_batch_first_class_date);
        $end = strtotime($today_date);

        $total_practice_days = ceil(abs($end - $start) / 86400);

        $course_classes_ids = EduAssignBatchClasses_Provider::valid()
            ->where('batch_id',$batch_id)
            ->where('complete_status',1)
            ->pluck('class_id')->toArray();

        $class_assignment_ids = EduClassAssignments_Provider::valid()
            ->where('batch_id',$batch_id)
            ->whereIn('assign_batch_class_id',$assign_batch_class_ids)
            ->pluck('id')->toArray();

        foreach($all_students as $key => $student){

            //total attendence
            $student->total_attendence = $total_attendence = EduStudentAttendence_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->count();
            // end total attendence

            // total attend and teacher mark
            $student->attend = EduStudentAttendence_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->where('is_attend',1)
                ->count();

            $student_get_teacher_mark = EduStudentAttendence_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->where('is_attend',1)
                ->sum('mark');

            if ($total_attendence > 0) {
                $final_teacher_mark = $student_get_teacher_mark / $total_attendence;
                $student->teacher_mark = round($final_teacher_mark,2);
            } else {
                $student->teacher_mark = 0;
            }
            
            // end total attenda nd teacher mark

            // last 5 missing class
            $last_three_assign_batch_class_id = EduAssignBatchClasses_Provider::valid()
                ->where('batch_id',$batch_id)
                ->where('complete_status',1)
                ->orderBy('class_id', 'desc')
                ->limit(3)->pluck('id')->toArray();

            // $student->total_last_missing_class = $last_five_assign_batch_class_id;

            if(count($last_three_assign_batch_class_id) > 1){
                $exact_last_two_class = array_slice($last_three_assign_batch_class_id, 0, 2);
                $exact_last_three_class = [];

                if(count($last_three_assign_batch_class_id) > 2){
                    $exact_last_two_class = array_slice($last_three_assign_batch_class_id, 0, 2);
                    $exact_last_three_class = array_slice($last_three_assign_batch_class_id, 0, 3);
                }

                $count_last_two_missing_class = EduStudentAttendence_Provider::valid()
                    ->where('batch_id', $batch_id)
                    ->where('student_id', $student->id)
                    ->whereIn('class_id',$exact_last_two_class)
                    ->where('is_attend',0)
                    ->count();

                $count_last_three_missing_class = EduStudentAttendence_Provider::valid()
                    ->where('batch_id', $batch_id)
                    ->where('student_id', $student->id)
                    ->whereIn('class_id',$exact_last_three_class)
                    ->where('is_attend',0)
                    ->count();

                if($count_last_three_missing_class == 3){
                    $student->total_last_missing_class = 'Last 3 Missing Class';
                }else if($count_last_two_missing_class == 2){
                    $student->total_last_missing_class = 'Last 2 Missing Class';
                }else{
                    $student->total_last_missing_class = 'Regular';
                }

            }else{
                $student->total_last_missing_class = 'Regular';
            }
            
            // last class attend
            $last_assign_batch_class_id = EduAssignBatchClasses_Provider::valid()
                ->where('batch_id',$batch_id)
                ->where('complete_status',1)
                ->orderBy('class_id', 'desc')
                ->first()->id;

            $last_class_attend = EduStudentAttendence_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->where('class_id',$last_assign_batch_class_id)
                ->where('is_attend',1)
                ->count();
            if($last_class_attend > 0){
                $student->last_class_attend = 'Yes';
            }else{
                $student->last_class_attend = 'No';
            }
            // end last class attend

            //assignment mark
            $student_get_assignment_mark = EduAssignmentSubmission_Provider::valid()
                ->whereIn('assignment_id', $class_assignment_ids)
                ->where('created_by', $student->id)
                ->sum('mark');

            $final_assignment_mark = $student_get_assignment_mark / count($class_assignment_ids);
            $student->total_assignment_mark = round($final_assignment_mark,2);
            //end assignment mark

            //practice time
            $student_get_practice_time = EduStudentPracticeTime_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('course_id', $student->course_id)
                ->where('student_id', $student->id)
                ->sum('total_time');
            

            $final_practice_time = $student_get_practice_time / $total_practice_days;
            $student->total_practice_time = round($final_practice_time,2);
            // end practice time

            // video watch time
            $student_get_watch_time = EduStudentVideoWatchInfo_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->whereIn('assign_batch_class_id', $assign_batch_class_ids)
                ->sum('watch_time');

            $final_watch_time = $student_get_watch_time / count($assign_batch_class_ids);
            $student->total_watch_time  = round($final_watch_time,2);
            // end video watch time


            // quiz mark
            $total_quiz = EduExamConfig_Provider::valid()
                ->where('batch_id', $batch_id)
                ->whereIn('assign_batch_class_id',$assign_batch_class_ids)
                ->count();

            $student_get_quiz_mark = EduStudentExam_Provider::valid()
                ->where('batch_id', $batch_id)
                ->where('student_id', $student->id)
                ->whereIn('assign_batch_class_id',$assign_batch_class_ids)
                ->sum(DB::raw('total_correct_answer * per_question_mark'));

            $final_quiz_mark = $student_get_quiz_mark / $total_quiz;
            $student->total_quiz_mark = round($final_quiz_mark,2);
            // end quiz mark
        }

        // echo "<pre>";
        // print_r($all_students->toArray()); exit();

        return view('provider.analysisActivity.student.listData', $data);

    }
}
