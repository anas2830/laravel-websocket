<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EduAssignBatchStudent_Provider;
use App\Models\EduStudent_Provider;
use App\Models\EduAssignBatch_Provider;
use App\Models\EduCourses_Provider;
use Validator;
use Helper;
use DB;

class AssignStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['batch_id'] = $batch_id = $request->batch_id;
        $data['batch_info'] = EduAssignBatch_Provider::valid()->find($batch_id);
        $data['assign_students'] = EduAssignBatchStudent_Provider::join('users', 'users.id', '=', 'edu_assign_batch_students.student_id')
            ->select('edu_assign_batch_students.*', 'users.name','users.email','users.phone')
            ->where('edu_assign_batch_students.valid', 1)
            ->where('users.valid', 1)
            ->where('edu_assign_batch_students.batch_id', $batch_id)
            ->orderBy('edu_assign_batch_students.id', 'desc')
            ->get();

        return view('provider.assignStudent.listData', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data['batch_id'] = $batch_id = $request->batch_id;
        $assign_batch_info = EduAssignBatch_Provider::valid()->find($batch_id);
        $data['course_id'] = $assign_batch_info->course_id;

        $exits_batch_student = EduAssignBatchStudent_Provider::valid()->where('is_running', 1)->pluck('student_id')->toArray();
        $all_students = EduStudent_Provider::valid()->pluck('id')->toArray();
        $filter_students = array_diff($all_students,$exits_batch_student);
        $data['sutdents'] =  EduStudent_Provider::valid()->whereIn('id',$filter_students)->get();
        return view('provider.assignStudent.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $students_arr = $request->students;
        $validator = Validator::make($request->all(), [
            'students'         => 'required',
        ]);

        if ($validator->passes()) {

            $filter_students_id = array_filter($students_arr);
            foreach($filter_students_id as $key => $student_id) 
            {
                EduAssignBatchStudent_Provider::create([
                    'batch_id'   => $request->batch_id,
                    'course_id'  => $request->course_id,
                    'student_id' => $student_id,
                ]);
            }

            $output['messege'] = 'Students has been Assigned';
            $output['msgType'] = 'success';

            return redirect()->back()->with($output);
        } else {
            return redirect()->back()->withErrors($validator);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EduAssignBatchStudent_Provider::valid()->find($id)->delete();
    }
}
