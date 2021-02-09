<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use File;
use Helper;
use Validator;
use DB;
use Auth;
use App\Models\EduStudent_Provider;
use App\Models\EduAssignBatch_Teacher;
use App\Models\EduAssignBatchClasses_Teacher;
use App\Models\EduCourses_Teacher;
use App\Models\EduAssignBatchStudent_Teacher;
use App\Models\EduStudentAttendence_Teacher;
use App\Models\EduCourseAssignClass_Teacher;


class StudentAttendenceController extends Controller
{
    public function index()
    {
        $authId = Auth::guard('teacher')->id();
        $data['assign_batches'] = EduAssignBatch_Teacher::join('edu_courses', 'edu_courses.id', '=', 'edu_assign_batches.course_id')
            ->select('edu_assign_batches.*', 'edu_courses.course_name')
            ->where('edu_assign_batches.valid', 1)
            ->where('edu_assign_batches.teacher_id', $authId)
            ->where('edu_courses.valid', 1)
            ->orderBy('edu_assign_batches.id', 'desc')
            ->get();
        return view('teacher.studentAttendence.assignBatchListData', $data);
    }
    public function classList(Request $request, $batch_id)
    {
        $assignBatchInfo = EduAssignBatch_Teacher::valid()->find($batch_id);
        $data['batch_no']=  $assignBatchInfo->batch_no;
        $data['course_name']= EduCourses_Teacher::valid()->find($assignBatchInfo->course_id)->course_name;
        $data['assign_classes'] = $assign_classes = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
            ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name')
            ->where('edu_assign_batch_classes.batch_id', $batch_id)
            ->where('edu_assign_batch_classes.valid', 1)
            ->where('edu_course_assign_classes.valid', 1)
            ->orderBy('edu_assign_batch_classes.id', 'asc')
            ->get();
        foreach ($assign_classes as $key => $class) {
            $class->isAttendanceDone = EduStudentAttendence_Teacher::valid()->where('batch_id', $batch_id)->where('class_id', $class->id)->count();
        }
        return view('teacher.studentAttendence.classListData', $data);
    }
    public function giveAttendence(Request $request)
    {
        $data['assign_class_id'] = $assign_class_id = $request->batch_class_id;
        $data['assignBatchClassInfo'] = $assignBatchClassInfo = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
            ->join('edu_assign_batches', 'edu_assign_batches.id', '=', 'edu_assign_batch_classes.batch_id')
            ->select('edu_assign_batch_classes.*', 'edu_course_assign_classes.class_name', 'edu_assign_batches.batch_no', 'edu_assign_batches.course_id')
            ->where('edu_course_assign_classes.valid', 1)
            ->where('edu_assign_batch_classes.valid', 1)
            ->where('edu_assign_batch_classes.id', $assign_class_id)
            ->first();
        $data['batch_no'] =  $assignBatchClassInfo->batch_no;
        $data['course_info'] = EduCourses_Teacher::valid()->find($assignBatchClassInfo->course_id);


        $done_attendence = EduStudentAttendence_Teacher::valid()
            ->where('batch_id',$assignBatchClassInfo->batch_id)
            ->where('class_id',$assign_class_id)
            ->count(); 

        if($done_attendence > 0){
            $data['assign_students'] = [];
        } else{
            $data['assign_students'] = EduAssignBatchStudent_Teacher::join('users', 'users.id', '=', 'edu_assign_batch_students.student_id')
            ->select('edu_assign_batch_students.*', 'users.name', 'users.id as user_id', 'users.student_id as gen_student_id')
            ->where('edu_assign_batch_students.valid', 1)
            ->where('edu_assign_batch_students.active_status', 1)
            ->where('edu_assign_batch_students.batch_id', $assignBatchClassInfo->batch_id)
            ->get();
        }

        return view('teacher.studentAttendence.giveAttendence', $data);
    }
    
    public function saveAttendence(Request $request)
    {
        $mark_arr = $request->mark;
        $remark = $request->remark;
        $student_arr = $request->student_id;
        $class_id = $request->class_id;
        $class_info = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
                    ->select('edu_course_assign_classes.class_name')
                    ->where('edu_assign_batch_classes.id',$class_id)
                    ->where('edu_course_assign_classes.valid',1)
                    ->where('edu_assign_batch_classes.valid',1)
                    ->first();

        $assign_students = EduAssignBatchStudent_Teacher::valid()->where('active_status', 1)->where('batch_id', $request->batch_id)->pluck('student_id')->toArray();


        $validator = Validator::make($request->all(), [

        ]);
        
        if ($validator->passes()) {
            DB::beginTransaction();
            if ( isset($student_arr) && count($student_arr) > 0) {

                foreach($assign_students as $key => $student) 
                {   
                    EduStudentAttendence_Teacher::create([
                        'batch_id'   => $request->batch_id, 
                        'course_id'  => $request->course_id, 
                        'class_id'   => $class_id, 
                        'student_id' => $student, 
                        'is_attend'  => ( isset($student_arr[$student]) && $student == $student_arr[$student] ) ? 1 : 0,
                        'mark'       => ( isset($student_arr[$student]) ) ? $mark_arr[$student] : 0,
                        'remark'     => ( isset($remark[$student]) ) ? $remark[$student] : '',
                    ]);
                }

                // foreach($absent_student_arr as $key => $absent_student) 
                // {   
                //     EduStudentAttendence_Teacher::create([
                //         'batch_id'   => $request->batch_id, 
                //         'course_id'  => $request->course_id, 
                //         'class_id'   => $class_id, 
                //         'student_id' => $absent_student, 
                //         'is_attend'  => 0, 
                //         'mark'       => 0,
                //         'remark'     => isset($remark[$key]) ? $remark[$key] : '',
                //     ]);
                // }

                $output['messege'] =  $class_info->class_name.' '.'Attendence has been Submited';
                $output['msgType'] = 'success';
            } else{
                $output['messege'] =  $class_info->class_name.' '.'Attendence not taken !!';
                $output['msgType'] = 'danger';
            }
            DB::commit();
            
            
            // return redirect()->back()->with($output);
            return redirect()->route('teacher.batchstuClassList', ['batch_id' => $request->batch_id])->with($output);
        } else {
            return redirect()->back()->withErrors($validator);
        }
    }

    public function showAttendence(Request $request)
    {
        $batch_class_id = $request->batch_class_id;

        $data['attendenceLists'] = EduStudentAttendence_Teacher::join('users','users.id','=','edu_student_attendences.student_id')
            ->select('edu_student_attendences.*','users.name','users.phone','users.student_id')
            ->where('edu_student_attendences.class_id',$batch_class_id)
            ->where('edu_student_attendences.valid',1)
            ->get();

        // echo "<pre>";
        // print_r($assign_students->toArray()); exit();

        return view('teacher.studentAttendence.showAttendence', $data);
    }
}
