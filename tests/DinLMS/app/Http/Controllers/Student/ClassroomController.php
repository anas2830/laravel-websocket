<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use DB;
use Auth;
use Helper;
use Validator;
use App\Models\EduAssignBatchClasses_User;
use App\Models\EduAssignBatchStudent_User;
use App\Models\EduCourseClassMaterials_User;
use App\Models\EduAssignBatchSchedule_User;
use App\Models\EduStudentAttendence_User;
use App\Models\EduCourseAssignClass_User;
use App\Models\EduStudentVideoWatchInfo_User;
use App\Models\EduClassAssignments_User;
use App\Models\EduTeacher_User;
use App\Models\EduAssignmentSubmission_User;
use App\Models\EduAssignmentSubmissionAttachment_User;
use App\Models\EduClassAssignmentAttachments_User;
use App\Models\EduAssignmentComment_User;
use App\Models\EduStudentPracticeTime_User;
use App\Models\EduStudentNotification_User;
use App\Models\EduExamConfig_User;
use App\Models\EduStudentExam_User;
use App\Models\EduStudentExamQuestion_User;
use App\Models\EduAnswer_User;
use App\Models\EduArchiveQuestion_User;
use App\Models\EduStudentProgress_User;

class ClassroomController extends Controller
{
    /// start class menu method
    public function classIndex(Request $request)
    {
        $data['upcomming_class_id'] = !empty($request->class_id) ? $request->class_id : '';
        
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();

        if (isset($student_course_info)) {
            $data['course_classes'] = $course_classes = EduAssignBatchClasses_User::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name', 'edu_course_assign_classes.class_overview')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.batch_id', $student_course_info->batch_id)
                ->where('edu_assign_batch_classes.course_id', $student_course_info->course_id)
                ->orderBy('edu_assign_batch_classes.id', 'asc')
                ->get();
            foreach ($course_classes as $key => $class) {
                $class->materials = EduCourseClassMaterials_User::valid()
                    ->where('course_id', $class->course_id)
                    ->where('class_id', $class->class_id)
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } else {
            $data['course_classes'] = [];
        }

        // echo "<pre>";
        // print_r($data['course_classes']->toArray()); exit();

        return view('student.classroom.class.classIndex', $data);
    }
    public function classDetails(Request $request)
    {
        $assign_batch_class_id = $request->batch_class_id;
        $assign_batch_info = EduAssignBatchClasses_User::valid()->find($assign_batch_class_id);
        if($assign_batch_class_id){
            $data['class_overview'] = EduAssignBatchClasses_User::join('edu_course_assign_classes','edu_course_assign_classes.id','=','edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*','edu_course_assign_classes.class_overview','edu_course_assign_classes.class_name')
                ->where('edu_course_assign_classes.course_id', '=', $assign_batch_info->course_id)
                ->where('edu_assign_batch_classes.id',$assign_batch_class_id)
                ->where('edu_assign_batch_classes.valid',1)
                ->where('edu_course_assign_classes.valid',1)
                ->first();
        }else{
            $data['class_overview'] = '';
        }

        // echo "<pre>";
        // print_r($data['class_overview']->toArray()); exit();

        return view('student.classroom.class.classDetails',$data);
    }

    public function updateVideoWatchTime(Request $request){
        $materialId = $request->materialId;
        $curDuration = $request->curDuration;
        $assign_batch_class_id = $request->batch_class_id;
        $output['auth'] = $authId = Auth::id();
        $video = EduCourseClassMaterials_User::valid()->find($materialId);
        $curDuration = Helper::timeToSecond($curDuration);

        $percentage = ($curDuration<$video->video_duration) ? ($curDuration/$video->video_duration)*100 : 100;
        $full_watched = ($percentage>=95) ? 1 : 0;
      
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
        
        $video_assign_batch_class_id = EduAssignBatchClasses_User::valid()
        ->where('course_id',$video->course_id)
        ->where('class_id',$video->class_id)
        ->where('batch_id',$student_course_info->batch_id)
        ->first()->id;

        $exists_data = EduStudentVideoWatchInfo_User::valid()
            ->where('batch_id',$student_course_info->batch_id)
            ->where('course_id',$student_course_info->course_id)
            ->where('assign_batch_class_id',$video_assign_batch_class_id)
            ->where('material_id',$materialId)
            ->first();

        if(!empty($exists_data))
        {
            if($exists_data->is_complete != 1){
                EduStudentVideoWatchInfo_User::find($exists_data->id)->update([
                    "watch_time"            =>  $curDuration,
                    "is_complete"           =>  $full_watched
                ]);
            }
            $output['msgType'] = 'watch time update';
        }
        else
        {
            EduStudentVideoWatchInfo_User::create([
                "student_id"            =>  $authId,
                "batch_id"              =>  $student_course_info->batch_id,
                "course_id"             =>  $student_course_info->course_id,
                "assign_batch_class_id" =>  $assign_batch_class_id,
                "material_id"           =>  $materialId,
                "watch_time"            =>  $curDuration,
                "is_complete"           =>  $full_watched,
                "date"                  =>  date('Y-m-d'),
            ]);

            $output['msgType'] = 'watch time create';
        }

        return response($output);
    }

    public function assignments(Request $request)
    {
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
        $data['assign_batch_class_id'] = $assign_batch_class_id = $request->batch_class_id;
        $user_id = Auth::id();
        if (isset($student_course_info)) {

            $data['assignments'] = $assignments = EduClassAssignments_User::join('edu_teachers','edu_teachers.id','=','edu_class_assignments.created_by')
                ->select('edu_class_assignments.*','edu_teachers.name')
                ->where('edu_class_assignments.assign_batch_class_id',$assign_batch_class_id)
                ->where('edu_class_assignments.valid',1)
                ->get();
    
            foreach ($assignments as $key => $value) {
                $value->completeStatus = EduAssignBatchClasses_User::valid()->find($assign_batch_class_id)->complete_status;
                $value->attachment = EduClassAssignmentAttachments_User::valid()->where('class_assignment_id',$value->id)->first();
                $value->submitted = $submitted = EduAssignmentSubmission_User::valid()->where('assignment_id',$value->id)->where('created_by',$user_id)->first();
                if (!empty($value->submitted)) {
                    $value->submittedAttachment = EduAssignmentSubmissionAttachment_User::valid()->where('assignment_submission_id',$submitted->id)->first();
                }
                $value->teacherComment = EduAssignmentComment_User::join('edu_teachers', 'edu_teachers.id', '=', 'edu_assignment_comments.created_by')
                    ->select('edu_assignment_comments.*', 'edu_teachers.name as teacher_name')
                    ->where('edu_assignment_comments.class_assignments_id',$value->id)
                    ->where('edu_assignment_comments.student_id',$user_id)
                    ->first();
            }
        } else {
            $data['assignments'] = [];
        }
        
        return view('student.classroom.class.assignments', $data);
    }

    public function submitAssignment(Request $request){

        $assignment_id = $request->assignment_id;
        $comment       = $request->comment;
        $mainFile      = $request->attachment;
        $submit_type   = $request->submit_type;
        $authId        = Auth::id();

        $teacher_assignment_info = EduClassAssignments_User::valid()->find($assignment_id);

        $teacher_due_dateTime = strtotime($teacher_assignment_info->due_date." ".$teacher_assignment_info->due_time);
        $student_submit_dateTime = strtotime(date('Y-m-d H:i:s'));

        $validator = Validator::make($request->all(), [
            'comment'    => 'required',
        ]);

        if ($validator->passes()) {
            DB::beginTransaction();
            $isSubmitted = EduAssignmentSubmission_User::valid()->where('assignment_id', $assignment_id)->where('created_by', $authId)->first();

            $countSubmitted = EduAssignmentSubmission_User::valid()->where('assignment_id', $assignment_id)->where('created_by', $authId)->count();
            
            $student_batch_id = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first()->batch_id;
            if ($student_batch_id == $teacher_assignment_info->batch_id) {

                if ( empty($isSubmitted) && ($countSubmitted == 0) ) {
                    
                    if(isset($mainFile)){
                    
                        $validPath = 'uploads/assignment/studentAttachment';
                        $uploadResponse = Helper::getUploadedAttachmentName($mainFile, $validPath);
        
                        if($uploadResponse['status'] != 0){
                            $assignment = EduAssignmentSubmission_User::create([
                                'assignment_id'        => $assignment_id,
                                'comment'              => $comment,
                                'submission_date'      => date('Y-m-d'),
                                'submission_time'      => date('H:i:s'),
                                'late_submit'          =>  $teacher_due_dateTime>$student_submit_dateTime ? 0 : 1,
                            ]);
            
                            EduAssignmentSubmissionAttachment_User::create([
                                'assignment_submission_id'  => $assignment->id,
                                'file_name'                 => $uploadResponse['file_name'],
                                'file_original_name'        => $uploadResponse['file_original_name'],
                                'size'                      => $uploadResponse['file_size'],
                                'extention'                 => $uploadResponse['file_extention']
                            ]);
            
                            $output['messege'] = 'Assignment has been Submitted';
                            $output['msgType'] = 'success';
                            $output['status'] = '1';
                            $output['late_submit'] = $teacher_due_dateTime>$student_submit_dateTime ? 0 : 1;
            
                        }else{
                            $output['messege'] = $uploadResponse['errors'];
                            $output['msgType'] = 'danger';
                            $output['status'] = '0';
                        }
                        
                    }else{
                        EduAssignmentSubmission_User::create([
                            'assignment_id'        => $assignment_id,
                            'comment'              => $comment,
                            'submission_date'      => date('Y-m-d'),
                            'submission_time'      => date('H:i:s'),
                            'late_submit'          =>  $teacher_due_dateTime>$student_submit_dateTime ? 0 : 1,
                        ]);
        
                        $output['messege'] = 'Assignment has been Submitted';
                        $output['msgType'] = 'success';
                        $output['status'] = '1';
                    }
                    
                } else {

                    $isSubmitted->update([
                        'comment'              => $comment,
                        'submission_date'      => date('Y-m-d'),
                        'submission_time'      => date('H:i:s'),
                        'late_submit'          => $teacher_due_dateTime>$student_submit_dateTime ? 0 : 1,
                    ]);

                    $output['messege'] = 'Assignment has been Updated';
                    $output['msgType'] = 'success';
                    $output['status'] = '1';
                }
            } else {
                $output['messege'] = 'Ops!! You dont assigned this batch!!';
                $output['msgType'] = 'danger';
                $output['status'] = '0';
            }
            DB::commit();
            return response($output);

        } else {
            $output['messege'] = 'Comment Field Required!!';
            $output['msgType'] = 'danger';
            $output['status'] = '0';
            return response($output);
        }
    }

    public function activities(Request $request)
    {
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
        $data['assign_batch_class_id'] = $assign_batch_class_id = $request->batch_class_id;
        $assign_batch_info = EduAssignBatchClasses_User::valid()->find($assign_batch_class_id);
        $authId = Auth::id();
        // For This Class Assignments
        $data['class_assignments'] = $class_assignments  = EduClassAssignments_User::valid()
            ->where('batch_id', $student_course_info->batch_id)
            ->where('course_id', $student_course_info->course_id)
            ->where('assign_batch_class_id', $assign_batch_class_id)
            ->get();

        foreach ($class_assignments as $key => $assignment) {
            $assignment->submit = EduAssignmentSubmission_User::valid()
                ->where('assignment_id', $assignment->id)
                ->where('created_by', $authId)
                ->first();
        }

        // For This Class Video's Watch Time
        $data['class_videos'] = $class_videos = EduCourseClassMaterials_User::valid()
            ->where('course_id', $student_course_info->course_id)
            ->where('class_id', $assign_batch_info->class_id)
            ->get();
        foreach ($class_videos as $key => $video) {
            $videoWatchInfo = EduStudentVideoWatchInfo_User::valid()
                ->where('batch_id', $student_course_info->batch_id)
                ->where('course_id', $student_course_info->course_id)
                ->where('assign_batch_class_id', $assign_batch_class_id)
                ->where('material_id', $video->id)
                ->first();
            if (!empty($videoWatchInfo)) {
                $video->watch_time = $videoWatchInfo->watch_time;
            } else {
                $video->watch_time = 0;
            }
        }
        // For Class Attedence
        $data['attendence_info'] = EduStudentAttendence_User::valid()
                ->where('batch_id', $student_course_info->batch_id)
                ->where('course_id', $student_course_info->course_id)
                ->where('class_id', $assign_batch_class_id) //class_id = assign_batch_class_id
                ->where('student_id', $authId)
                ->first();
        return view('student.classroom.class.activities', $data);
    }

    public function quiz(Request $request)
    {
        $data['assign_batch_class_id'] = $assign_batch_class_id = $request->batch_class_id;
        $assignBatchClassInfo = EduAssignBatchClasses_User::valid()->find($assign_batch_class_id);
        $authId = Auth::id();
        $data['examConfig'] = $examConfig = EduExamConfig_User::valid()
            ->where('batch_id', $assignBatchClassInfo->batch_id)
            ->where('assign_batch_class_id', $assignBatchClassInfo->id)
            ->first();
        if (!empty($examConfig)) {
            $examAlreadyGiven = EduStudentExam_User::valid()
                ->where('exam_config_id', $examConfig->id)
                ->where('batch_id', $assignBatchClassInfo->batch_id)
                ->where('assign_batch_class_id', $assignBatchClassInfo->id)
                ->where('course_id', $assignBatchClassInfo->course_id)
                ->where('course_class_id', $assignBatchClassInfo->class_id)
                ->where('student_id', $authId)
                ->first();

            if (!empty($examAlreadyGiven)) { //Exam Already Given
                $data['examQuestions'] = $examQuestions = EduStudentExamQuestion_User::join('edu_archive_questions', 'edu_archive_questions.id', '=', 'edu_student_exam_questions.question_id')
                    ->select('edu_student_exam_questions.*', 'edu_archive_questions.question', 'edu_archive_questions.answer_type')
                    ->whereIn('edu_student_exam_questions.question_id', json_decode($examConfig->questions))
                    ->where('edu_student_exam_questions.student_exam_id', $examAlreadyGiven->id)
                    ->get();
                foreach ($examQuestions as $key => $question) {
                    $question->answerSet = EduAnswer_User::valid()->where('question_id', $question->question_id)->get();
                }
                
                return view('student.classroom.class.examResult', $data);
            } else {
                return view('student.classroom.class.quiz', $data);
            }
        } else {
            return view('student.classroom.class.quiz', $data);
        }
    }
    /// end class menu method

    public function overview()
    {
        $authId = Auth::id();
        $today_date = date('Y-m-d');
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
        $student_progress = EduStudentProgress_User::valid()->where('type',1)->first();

        if(!empty($student_course_info)){

            // progress batch wise
            $std_batch_id = $student_course_info->batch_id;
            $std_course_id = $student_course_info->course_id;
            // $data['std_course_progress'] = self::getStudentCourseProgress($std_batch_id, $std_course_id, $today_date);
        
            // Practice Ratio on Daily/Weekly/Monthly
            $SeveDaysAgo = date('Y-m-d', strtotime('-7 days'));
            $ThirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

            // Last one Days Calculation
            $last_day = date('Y-m-d',strtotime("-1 days")); 

            // Start Attendence
                $data['all_attendence'] = $all_attendence =  EduStudentAttendence_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->get();

                foreach ($all_attendence as $key => $attendence) {
                    $attendence->course_class_id = EduAssignBatchClasses_User::find($attendence->class_id)->class_id;
                }
            // End Attendence

            //Done/Not Done Assignment List with Mark.
                $data['total_assignment'] = $total_assignment  = EduClassAssignments_User::join('edu_assign_batch_students', 'edu_assign_batch_students.batch_id','=','edu_class_assignments.batch_id')
                    ->select('edu_class_assignments.*')
                    ->where('edu_class_assignments.valid',1)
                    ->where('edu_class_assignments.course_id',$student_course_info->course_id)
                    ->where('edu_assign_batch_students.student_id',$authId)
                    ->get();

                foreach ($total_assignment as $key => $assignment) {
                    $assignment->submit = EduAssignmentSubmission_User::valid()
                        ->where('assignment_id', $assignment->id)
                        ->where('created_by', $authId)
                        ->first();
                }
            // end	Done/Not Done Assignment List with Mark.

            // start quiz
            $data['total_exams'] = $total_exams = EduExamConfig_User::join('edu_assign_batch_classes','edu_assign_batch_classes.id','=','edu_exam_configs.assign_batch_class_id')
                ->select('edu_exam_configs.*','edu_assign_batch_classes.class_id','edu_assign_batch_classes.course_id')
                ->where('edu_exam_configs.batch_id', $std_batch_id)
                ->where('edu_exam_configs.valid', 1)
                ->where('edu_assign_batch_classes.valid', 1)
                ->get();

            foreach ($total_exams as $key => $exam) {
                $exam->submit = EduStudentExam_User::valid()
                    ->where('exam_config_id', $exam->id)
                    ->where('batch_id', $exam->batch_id)
                    ->where('assign_batch_class_id', $exam->assign_batch_class_id)
                    ->where('course_id', $exam->course_id)
                    ->where('course_class_id', $exam->class_id)
                    ->first();
            }
            
            // START Practice Ratio on Daily/Weekly/Monthly
                $one_total_time_avg = EduStudentPracticeTime_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '=', $last_day)
                    ->first();
                
                if (!empty($one_total_time_avg)) {
                    $data['last_one_practice'] = round((($one_total_time_avg->total_time*100)/14400), 2);
                } else {
                    $data['last_one_practice'] = 0;
                }

                $seven_total_time = EduStudentPracticeTime_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '>=', $SeveDaysAgo)
                    ->whereDate('date', '!=', $today_date)
                    ->sum('total_time');
                
                // echo "<pre>";
                // print_r($seven_total_time_avg); exit();

                if ($seven_total_time > 0) {
                    $data['last_seven_practice'] = round(( ($seven_total_time*100) / (14400*7) ), 2);
                } else {
                    $data['last_seven_practice'] = 0;
                }

                // Last Thirty Days Calculation
                $thirty_total_time = EduStudentPracticeTime_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '>=', $ThirtyDaysAgo)
                    ->whereDate('date', '!=', $today_date)
                    ->sum('total_time');

                if ($thirty_total_time > 0) {
                    $data['last_thirty_practice'] = round(( ($thirty_total_time*100) / (14400*30) ), 2);
                } else {
                    $data['last_thirty_practice'] = 0;
                }
            // end Practice Ratio on Daily/Weekly/Monthly

            //watch video on daily/weekly/montly
                $today_materials = EduStudentVideoWatchInfo_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '=', $last_day)
                    ->get();

                if(count($today_materials) > 0){
                    $today_material_ids = $today_materials->pluck('material_id')->unique('material_id')->toArray();
                    $today_total_watch_time =  $today_materials->sum('watch_time');
                    $today_total_video_time = EduCourseClassMaterials_User::valid()->whereIn('id',$today_material_ids)->sum('video_duration');
                    $data['today_avg_watch_time'] = round((($today_total_watch_time*100)/$today_total_video_time), 2);
                }else{
                    $data['today_avg_watch_time'] = 0;
                }

                //last seven days
                $seven_days_materials = EduStudentVideoWatchInfo_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '>=', $SeveDaysAgo)
                    ->whereDate('date', '!=', $today_date)
                    ->get();

                if(count($seven_days_materials) > 0){
                    $seven_days_material_ids = $seven_days_materials->pluck('material_id')->unique('material_id')->toArray();
                    $seven_days_total_watch_time = $seven_days_materials->sum('watch_time');
                    $seven_days_total_video_time = EduCourseClassMaterials_User::valid()->whereIn('id',$seven_days_material_ids)->sum('video_duration');
                    $data['sevenDays_avg_watch_time'] = round((($seven_days_total_watch_time*100)/$seven_days_total_video_time), 2);
                }else{
                    $data['sevenDays_avg_watch_time'] = 0;
                }


                // last thirty days
                $thirty_days_materials = EduStudentVideoWatchInfo_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('course_id', $student_course_info->course_id)
                    ->where('student_id', $authId)
                    ->whereDate('date', '>=', $ThirtyDaysAgo)
                    ->whereDate('date', '!=', $today_date)
                    ->get();

                if(count($thirty_days_materials) > 0){
                    $thirty_days_material_ids = $thirty_days_materials->pluck('material_id')->unique('material_id')->toArray();
                    $thirty_days_total_watch_time = $thirty_days_materials->sum('watch_time');
                    $thirty_days_total_video_time = EduCourseClassMaterials_User::valid()->whereIn('id',$thirty_days_material_ids)->sum('video_duration');
                    $data['thirtyDays_avg_watch_time'] = round((($thirty_days_total_watch_time*100)/$thirty_days_total_video_time), 2);
                }else{
                    $data['thirtyDays_avg_watch_time'] = 0;
                }

            // End Watch time

            //Auto Notification
            $data['notifications'] = EduStudentNotification_User::valid()->latest()->limit(10)->get();

            // graph
            if(!empty($student_course_info)){
                $cacheKey1 = 'overviewBarChart'.$authId;
                $cacheKey2 = 'overallProgress'.$authId;
                Cache::remember($cacheKey1, 60*60*24, function () {
                    $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
                    $authId = Auth::id();
                    $std_batch_id = $student_course_info->batch_id;
                    $std_course_id = $student_course_info->course_id;
                    
                    $all_assign_classes = EduAssignBatchClasses_User::valid()
                        ->where('batch_id', $std_batch_id)
                        ->where('course_id', $std_course_id)
                        ->get();
        
                    foreach($all_assign_classes as $key => $assign_class){
                        // attendence and class mark
                        $std_attend_class = EduStudentAttendence_User::valid()
                            ->where('class_id', $assign_class->id)
                            ->where('student_id', $authId)
                            ->where('is_attend', 1)
                            ->first();

                        $assign_class->std_class_name = Helper::className($assign_class->class_id);
        
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
                Cache::remember($cacheKey2, 60*60*24, function (){
                    $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();
                    $authId = Auth::id();
                    $today_date = date('Y-m-d');
                    $std_batch_id = $student_course_info->batch_id;
                    $std_course_id = $student_course_info->course_id;
                    $std_course_progress = self::getStudentCourseProgress($std_batch_id, $std_course_id, $today_date);
                    return $std_course_progress;
                });
                $data['all_assign_classes'] = Cache::get($cacheKey1);
                $data['std_course_progress'] = Cache::get($cacheKey2);
            } else {
                $data['all_assign_classes'] = [];
                $data['std_course_progress'] = '';
            }
        } 
        return view('student.classroom.overview', $data);
    }
    
    public function todayGoal(Request $request)
    {
        $authId = Auth::id();
        $today_date = date('Y-m-d');
        $student_course_info = EduAssignBatchStudent_User::valid()->where('is_running', 1)->where('active_status', 1)->first();

        // today practice time
        $today_avg_practice_time = EduStudentPracticeTime_User::valid()
                ->where('batch_id', $student_course_info->batch_id)
                ->where('course_id', $student_course_info->course_id)
                ->where('student_id', $authId)
                ->whereDate('date', '=', $today_date)
                ->first();
            
        if (!empty($today_avg_practice_time)) {
            $final_practice = round((($today_avg_practice_time->total_time*100)/14400), 2);
            $data['today_practice'] = $final_practice >= 100 ? 100 : $final_practice;
        } else {
            $data['today_practice'] = 0;
        }
        // end today practice time

        // upcomming class
        $upcomming_class =  EduAssignBatchClasses_User::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
                ->where('edu_assign_batch_classes.valid', 1)
                ->where('edu_assign_batch_classes.complete_status', 2)  // 2 = running/upcomming
                ->where('edu_assign_batch_classes.batch_id', $student_course_info->batch_id)
                ->where('edu_assign_batch_classes.course_id', $student_course_info->course_id)
                ->first();

        if(!empty($upcomming_class)){
            $data['upcomming_class'] = $upcomming_class;
            $data['running_assignments']  = EduClassAssignments_User::valid()->where('assign_batch_class_id',$upcomming_class->id)->get();
            $data['running_quiz']  = EduExamConfig_User::valid()
                ->where('assign_batch_class_id', $upcomming_class->id)
                ->where('batch_id', $upcomming_class->batch_id)
                ->first();
            $data['running_videos'] = EduCourseClassMaterials_User::valid()
                ->where('course_id',$upcomming_class->course_id)
                ->where('class_id',$upcomming_class->class_id)
                ->get();
            // Batch Weekly Schedules
            $total_days = DB::table('edu_schedule_days')->get();
            foreach($total_days as $day) {
                $schedule = EduAssignBatchSchedule_User::valid()
                    ->where('batch_id', $student_course_info->batch_id)
                    ->where('day_dt', $day->dt)
                    ->first();

                if(!empty($schedule)) {
                    $day->schedule = $schedule;
                }
            }
            $data['total_days'] = $total_days;
        }else{
            $data['upcomming_class'] = '';
            $data['running_assignments'] = [];
            $data['running_quiz']  = '';
            $data['running_videos'] = [];
            $data['total_days'] = [];
        }
        return view('student.classroom.todayGoal', $data);
    }

    public static function getStudentCourseProgress($std_batch_id, $std_course_id, $today_date)
    {
        $studentAuthId = Auth::id();
        $student_progress = EduStudentProgress_User::valid()->where('type',1)->first();

        // class mark and attendence //
        $total_complete_class = EduAssignBatchClasses_User::valid()
            ->where('batch_id', $std_batch_id)
            ->where('course_id', $std_course_id)
            ->where('start_date', '<=', $today_date)
            ->get();

        $complete_assign_batch_class_ids = $total_complete_class->pluck('id')->toArray();
        
        $total_base_class_mark = count($total_complete_class) * 100;

        $std_attend_class = EduStudentAttendence_User::valid()
            ->where('batch_id', $std_batch_id)
            ->where('course_id', $std_course_id)
            ->where('is_attend', 1)
            ->get();
        
        $std_attend_class_mark = $std_attend_class->sum('mark');
        // Attended Class Mark Percentage
        if($total_base_class_mark > 0){
            $class_mark_percentage = round(($std_attend_class_mark*100) / $total_base_class_mark);
        }else{
            $class_mark_percentage = 0;
        }
        // Class Attendance Percentage
        if(count($total_complete_class) > 0){
            $attendence_percentage = round((count($std_attend_class)*100) / count($total_complete_class));
        }else{
            $attendence_percentage = 0;
        }

        $final_class_mark_progress = ($class_mark_percentage * $student_progress->class_mark) / 100;
        $final_attendence_progress = ($attendence_percentage * $student_progress->attendence) / 100;
        // End class mark and attendence //

        // Exam Result
        $total_config_exam = EduExamConfig_User::valid()->whereIn('assign_batch_class_id', $complete_assign_batch_class_ids)->get();
        $exam_config_ids = $total_config_exam->pluck('id')->toArray();
        $student_given_exam = EduStudentExam_User::valid()
            ->where('batch_id', $std_batch_id)
            ->whereIn('exam_config_id', $exam_config_ids)
            ->get();
        
        $total_base_exam_mark = $total_config_exam->sum('total_question') * $total_config_exam->sum('per_question_mark');
        $std_exam_mark = $student_given_exam->sum('total_correct_answer') * $student_given_exam->sum('per_question_mark');

        if($total_base_exam_mark > 0){
            $std_exam_percentage = round(($std_exam_mark*100) / $total_base_exam_mark);
        }else{
            $std_exam_percentage = 0;
        }
        
        $final_exam_progress = ($std_exam_percentage * $student_progress->quiz) / 100;
        // End Exam Result


        // Assignment progress
        $total_assignments = EduClassAssignments_User::valid()
            ->where('batch_id', $std_batch_id)
            ->where('course_id', $std_course_id)
            ->whereIn('assign_batch_class_id', $complete_assign_batch_class_ids)
            ->get();

        $total_assignment_base_mark = count($total_assignments) * 100;

        $assignment_ids = $total_assignments->pluck('id')->toArray();

        $std_submitted_assignments = EduAssignmentSubmission_User::valid()
            ->whereIn('assignment_id', $assignment_ids)
            ->get();

        $std_assignment_mark = $std_submitted_assignments->sum('mark');

        if($total_assignment_base_mark > 0){
            $std_assignment_percentage = round(($std_assignment_mark*100) / $total_assignment_base_mark);
        }else{
            $std_assignment_percentage = 0;
        }

        $final_assignment_progress = ($std_assignment_percentage * $student_progress->assignment) / 100;
        // End Assignment progress

        // Practice time progress
        $first_assign_class_date = EduAssignBatchClasses_User::valid()
            ->where('batch_id', $std_batch_id)
            ->where('course_id', $std_course_id)
            ->orderBy('class_id', 'ASC')
            ->first()->start_date;

        if(!empty($first_assign_class_date)){
            $start_class_date = strtotime($first_assign_class_date); 
            $today_class_date = strtotime($today_date); 
            $total_practice_date = ($today_class_date - $start_class_date)/60/60/24; 

            $total_base_practice_time = $total_practice_date * 14400;

            $total_std_practice_time = EduStudentPracticeTime_User::valid()
                ->where('batch_id', $std_batch_id)
                ->where('course_id', $std_course_id)
                ->where('created_by', $studentAuthId)
                ->get();
            
            $std_practice_time = 0;
            foreach($total_std_practice_time as $key => $practice_time){
                if($practice_time->total_time >= 14400){
                    $std_done_practice_time = 14400;
                }else{
                    $std_done_practice_time = $practice_time->total_time;
                }
                $std_practice_time += $std_done_practice_time;
            }
        }

        if($total_base_practice_time > 0){
            $total_practice_percentage = round(($std_practice_time*100) / $total_base_practice_time);
        }else{
            $total_practice_percentage = 0;
        }

        $final_practice_progress = ($total_practice_percentage * $student_progress->practice_time) / 100;

        // end practice time progress

        // Video watch time progress
        $total_course_class_ids = $total_complete_class->pluck('class_id')->toArray();
        $total_course_class_materials_time = EduCourseClassMaterials_User::valid()
            ->where('course_id', $std_course_id)
            ->whereIn('class_id', $total_course_class_ids)
            ->sum('video_duration');

        $std_video_watch_time = EduStudentVideoWatchInfo_User::valid()
            ->where('batch_id', $std_batch_id)
            ->where('course_id', $std_course_id)
            ->where('created_by', $studentAuthId)
            ->sum('watch_time');

        if($total_course_class_materials_time > 0){
            $std_watch_time_percentage = round( ($std_video_watch_time*100) / $total_course_class_materials_time );
        }else{
            $std_watch_time_percentage = 0;
        }

        $final_watch_time_progress = ($std_watch_time_percentage * $student_progress->video_watch_time) / 100;
        // End video watch time progress

        $course_total_percentage = $final_class_mark_progress + $final_attendence_progress + $final_exam_progress + $final_assignment_progress + $final_practice_progress + $final_watch_time_progress;
        
        return $course_total_percentage;
        // End progress
    }

}
