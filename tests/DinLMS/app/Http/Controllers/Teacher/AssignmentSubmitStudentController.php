<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use File;
use Helper;
use Validator;
use DB;
use Auth;
use Str;

use App\Models\EduStudent_Provider;
use App\Models\EduAssignBatch_Teacher;
use App\Models\EduAssignBatchClasses_Teacher;
use App\Models\EduCourses_Teacher;
use App\Models\EduAssignBatchStudent_Teacher;
use App\Models\EduStudentAttendence_Teacher;
use App\Models\EduCourseAssignClass_Teacher;
use App\Models\EduClassAssignments_Teacher;
use App\Models\EduAssignmentSubmission_Teacher;
use App\Models\EduAssignmentSubmissionAttachment_Teacher;
use App\Models\EduAssignmentComment_Teacher;
use App\Models\EduActivityNotify_Teacher;

class AssignmentSubmitStudentController extends Controller
{
    public function index(Request $request)
    {
        $data['assignment_id'] = $assignment_id = $request->assignment_id;
        $data['type'] = $type = $request->type; // 1 = done list, 2 = not done list
        $data['assignment_info'] = $assignment_info = EduClassAssignments_Teacher::valid()->find($assignment_id);

        $submission_std_array = EduAssignmentSubmission_Teacher::valid()->where('assignment_id',$assignment_id)->pluck('created_by');

        if($type == 1){

            $data['batch_students'] = $batch_students = EduAssignBatchStudent_Teacher::join('users', 'users.id', '=', 'edu_assign_batch_students.student_id')
                ->Join('edu_assignment_submissions', 'edu_assignment_submissions.created_by', '=', 'edu_assign_batch_students.student_id')
                ->select('edu_assign_batch_students.*', 'users.name', 'users.email', 'users.phone', 'edu_assignment_submissions.late_submit', 'edu_assignment_submissions.submission_date', 'edu_assignment_submissions.submission_time', 'edu_assignment_submissions.id as submission_id', 'edu_assignment_submissions.created_by as created_by_student')
                ->where('edu_assign_batch_students.batch_id', $assignment_info->batch_id) 
                ->where('edu_assign_batch_students.course_id', $assignment_info->course_id) 
                ->where('edu_assign_batch_students.is_running', 1) //1=Running Batch
                ->where('edu_assign_batch_students.active_status', 1) //1=Not Suspend
                ->where('edu_assignment_submissions.assignment_id', $assignment_id)
                ->where('edu_assign_batch_students.valid', 1)
                ->where('users.valid', 1)
                ->orderBy('edu_assign_batch_students.id', 'desc')
                ->get();
    
            foreach ($batch_students as $key => $student) {
                if (!empty($student->submission_id)) {
                    $student->attachment = EduAssignmentSubmissionAttachment_Teacher::valid()
                        ->where('assignment_submission_id', $student->submission_id)
                        ->where('created_by', $student->created_by_student)
                        ->first();

                        $student->submit_assignment = EduAssignmentComment_Teacher::valid()
                        ->where('assignment_submission_id',$student->submission_id)
                        ->where('class_assignments_id',$assignment_id)
                        ->where('student_id', $student->created_by_student)
                        ->first();
                        
                } else {
                    $student->attachment = null;
                }
            }

            // $assignment_comment_exits = EduAssignmentComment_Teacher::valid()
            //     ->where('assignment_submission_id',$assignment_info->)
            //     ->where('class_assignments_id',$assignment_id)
            //     ->where('student_id',$submission_info->created_by)
            //     ->first();

        } else{
            $data['batch_students'] = $batch_students = EduAssignBatchStudent_Teacher::join('users', 'users.id', '=', 'edu_assign_batch_students.student_id')
                ->select('edu_assign_batch_students.*', 'users.name', 'users.email', 'users.phone')
                ->where('edu_assign_batch_students.batch_id', $assignment_info->batch_id) 
                ->where('edu_assign_batch_students.course_id', $assignment_info->course_id) 
                ->where('edu_assign_batch_students.is_running', 1) //1=Running Batch
                ->where('edu_assign_batch_students.active_status', 1) //1=Not Suspend
                ->whereNotIn('edu_assign_batch_students.student_id', $submission_std_array)
                ->where('edu_assign_batch_students.valid', 1)
                ->where('users.valid', 1)
                ->orderBy('edu_assign_batch_students.id', 'desc')
                ->get();

        }

        // echo "<pre>";
        // print_r($data['batch_students']->toArray());
        // die();

        return view('teacher.studentList.listData', $data);
    }
    
    public function batchstuStudentGiveMark(Request $request)
    {
        $data['submission_id'] = $submission_id = $request->submission_id;
        $data['submission_info'] = EduAssignmentSubmission_Teacher::valid()->find($submission_id);
        return view('teacher.studentList.giveMark', $data);
    }
    public function batchstuStudentGiveMarkSave(Request $request)
    {
        $submission_id = $request->submission_id;
        $submission_info = EduAssignmentSubmission_Teacher::valid()->find($submission_id);
        $data['assignment_info'] = $assignment_info = EduClassAssignments_Teacher::valid()->find($submission_info->assignment_id);

        $mainFile = $request->attachment;
        $mark = $request->mark;
        $comment = $request->comment;
        $authId = Auth::guard('teacher')->id();
        $today_date = date('Y-m-d');
        $current_time = date('H:i:s');

        $validator = Validator::make($request->all(), [
            'mark'    => 'required',
            'comment' => 'required',
        ]);

        $assignment_comment_exits = EduAssignmentComment_Teacher::valid()
            ->where('assignment_submission_id',$submission_id)
            ->where('class_assignments_id',$assignment_info->id)
            ->where('student_id',$submission_info->created_by)
            ->first();

        if ($validator->passes() && empty($assignment_comment_exits)) {

            DB::beginTransaction();

            if(isset($mainFile)){
                
                $validPath = 'uploads/assignment/teacherComment';
                $uploadResponse = Helper::getUploadedFileName($mainFile, $validPath);

                if($uploadResponse['status'] == 1){

                    EduAssignmentComment_Teacher::create([
                        'assignment_submission_id'  => $submission_info->id,
                        'class_assignments_id'      => $assignment_info->id,
                        'batch_id'                  => $assignment_info->batch_id,
                        'course_id'                 => $assignment_info->course_id,
                        'assign_batch_class_id'     => $assignment_info->assign_batch_class_id,
                        'student_id'                => $submission_info->created_by,
                        'comment'                   => $comment,
                        'file_name'                 => $uploadResponse['file_name'],
                        'file_original_name'        => $uploadResponse['file_original_name'],
                        'size'                      => $uploadResponse['file_size'],
                        'extention'                 => $uploadResponse['file_extention'],
                    ]);

    
                    $output['messege'] = 'Marking has been Submitted';
                    $output['msgType'] = 'success';
    
                }else{
                    $output['messege'] = $uploadResponse['errors'];
                    $output['msgType'] = 'danger';
                }
                
            }else{

                EduAssignmentComment_Teacher::create([
                    'assignment_submission_id'  => $submission_info->id,
                    'class_assignments_id'      => $assignment_info->id,
                    'batch_id'                  => $assignment_info->batch_id,
                    'course_id'                 => $assignment_info->course_id,
                    'assign_batch_class_id'     => $assignment_info->assign_batch_class_id,
                    'student_id'                => $submission_info->created_by,
                    'comment'                   => $comment,
                ]);

                $output['messege'] = 'Marking has been Submitted';
                $output['msgType'] = 'success';
            }

            EduAssignmentSubmission_Teacher::find($submission_id)->update([
                'mark'                 => $mark,
                'mark_by'              => $authId
            ]);

            EduActivityNotify_Teacher::create([
                'batch_id'   => $assignment_info->batch_id,
                'course_id'  => $assignment_info->course_id,
                'student_id' => $submission_info->created_by,
                'notify_date'=> $today_date,
                'notify_time'=> $current_time,
                'notify_type'=> 2,
                'notify_title'=> Str::words($comment, 20, '.....'),
                'notify_link'=> "class?class_id=$assignment_info->assign_batch_class_id&#assignments",
                'created_type'=> 2,
            ]);
            
            DB::commit();

            return redirect()->back()->with($output);

        } else {

            if(!empty($assignment_comment_exits)){
                $output['messege'] = 'Already submitted';
                $output['msgType'] = 'danger';

                return redirect()->back()->with($output);

            } else{
                return redirect()->back()->withErrors($validator);
            }

        }

    }

    public function viewSubmissionDetails(Request $request)
    {
        $data['submission_id'] = $submission_id = $request->submission_id;
        $data['submission_info'] = EduAssignmentSubmission_Teacher::valid()->find($submission_id);
        return view('teacher.studentList.submissionOverview', $data);
    }
}
