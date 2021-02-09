<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use Helper;
use Validator;
use Illuminate\Support\Str;
use App\Models\EduAssignBatchClasses_Teacher;
use App\Models\EduAssignBatch_Teacher;
use App\Models\EduCourses_Teacher;
use App\Models\EduAssignBatchSchedule_Teacher;
use App\Models\EduStudentAttendence_Teacher;
use App\Models\EduZoomAccount_Teacher;
use App\Models\EduStudentLiveClassSchedule_Teacher;

class AssignedClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['batch_id'] = $batch_id = $request->batch_id;
        $data['assigned_classes'] = EduAssignBatchClasses_Teacher::join('edu_course_assign_classes', 'edu_course_assign_classes.id', '=', 'edu_assign_batch_classes.class_id')
            ->select('edu_assign_batch_classes.*','edu_course_assign_classes.class_name')
            ->where('edu_assign_batch_classes.batch_id',$batch_id)
            ->where('edu_assign_batch_classes.valid',1)
            ->where('edu_course_assign_classes.valid',1)
            ->get();

        $data['batcn_info'] = $assignBatchInfo  = EduAssignBatch_Teacher::valid()->find($batch_id);
        $data['course_name']= EduCourses_Teacher::valid()->find($assignBatchInfo->course_id)->course_name;

        // echo "<pre>";
        // print_r($data['assigned_class_info']); exit();

        return view('teacher.assignBatch.class.listData', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // this method use for live class schedule
    public function show($id)
    {
        $data['assign_batch_class_id'] = $id;
        $data['liveClass_Schedule'] = EduStudentLiveClassSchedule_Teacher::valid()->where('assign_batch_classes_id',$id)->first();
        $data['zoom_account_info'] =  EduZoomAccount_Teacher::valid()->first(); 
        return view('teacher.assignBatch.class.updateLiveSchedule', $data);
    }

    // this method use for update  live class schedule
    public function store(Request $request)
    {
        $liveScheduleid = $request->live_schedule_id;
        $assign_batch_classs_id = $request->assign_batch_classs_id;
        $course_class_id = EduAssignBatchClasses_Teacher::valid()->find($assign_batch_classs_id)->class_id;
        $className = Helper::className($course_class_id);

        $start_date =  Helper::dateYMD($request->start_date);
        // $start_time = $request->start_time;
        $start_time = Helper::zoomTimeGia($request->start_time);
        $duration_hours = $request->d_hour;
        $duration_minutes = $request->d_min;
        $duration_of_sec = (int)$request->d_hour*60*60+(int)$request->d_min*60; // Calculate by seconds
        $duration_of_min = (int)$request->d_hour*60+(int)$request->d_min; // Calculate by seconds
        $end_time = strftime('%X', strtotime($start_time) + $duration_of_sec);

        //TIME CALCULATION
        $get_hour = substr($start_time, 0, 2);
        $get_time_format = substr($start_time, -2);

        $get_min = substr($start_time, -5);
        $real_min = substr($get_min, 0, 2);
        $real_hour = Helper::time($get_hour,$real_min,$get_time_format);
        $day_dt  = date('w', strtotime($start_date));

        $zoom_acc_id = $request->zoom_acc_id;
        $zoom_account_info = EduZoomAccount_Teacher::valid()->find($zoom_acc_id); 

        if(!empty($zoom_account_info)){
            $zoom_acc_id = $zoom_account_info->id;
            $email = $zoom_account_info->email;
            $token = $zoom_account_info->token;
        }else{
            $output['messege'] = "Zoom account is not valid !";
            $output['msgType'] = 'danger';
            return redirect()->back()->with($output);
        }
        
        $validator = Validator::make($request->all(), [
            'start_date'  => 'required',
            'start_time'  => 'required',
            'd_hour'      => 'required|numeric',
            'd_min'       => 'required|numeric'
        ]);

        if ($validator->passes()) {

            DB::beginTransaction();
            //FOR ZOOM SCHEDULE CREATE/UPDATE INFO---
            $liveClassData = array(
                'topic'                  => $className,
                'agenda'                 => 'Description',  //description was too much long. which is not accepted in zoom
                'start_time'             => $start_date."T".$real_hour."Z",
                'duration'               => $duration_of_min,
                'timezone'               => 'Asia/Dhaka',
                'password'               => self::zoomPaaword(),
                'settings'               => array(
                    'join_before_host'       => false, 
                    'mute_upon_entry'        => true, 
                    'waiting_room'           => true, 
                    'meeting_authentication' => true
                ),
                
            );

            if (isset($liveScheduleid)) {
                $meeting_id = EduStudentLiveClassSchedule_Teacher::valid()->find($liveScheduleid)->meeting_id;
                //ZOOM INFO
                $curl_url = "https://api.zoom.us/v2/meetings/".$meeting_id;
                $curl_method = "PATCH";
                $message = "updated";
            } else {
                //ZOOM INFO
                $curl_url = "https://api.zoom.us/v2/users/".$email."/meetings";
                $curl_method = "POST";
                $message = "created";
            }

            $postFields = json_encode($liveClassData);

            $zoomInfo = Helper::zoomIntegrationFunction($curl_url, $curl_method, $postFields, $token);

            if (isset($liveScheduleid)) {
                $curl_method = "GET";
                $zoomInfo = Helper::zoomGetDelete($token, $curl_method, $meeting_id);
            }

            if (property_exists($zoomInfo["info"], 'code')) {
                $msgStatus = 0;
            } else {
                $msgStatus = 1;
            }  

            if ($msgStatus==1) {
                
                $liveClassZoomData = [
                    'zoom_acc_id'             => $zoom_acc_id,
                    'assign_batch_classes_id' => $assign_batch_classs_id, 
                    'day_dt'                  => $day_dt, 
                    'start_date'              => $start_date, 
                    'start_time'              => strftime('%X', strtotime($start_time)),
                    'end_time'                => $end_time, 
                    'hour'                    => $duration_hours, 
                    'min'                     => $duration_minutes, 
                    'duration'                => $duration_of_min, 
                    'type'                    => $zoomInfo['info']->type,
                    'meeting_id'              => $zoomInfo['info']->id,
                    'host_id'                 => $zoomInfo['info']->host_id,
                    'start_url'               => $zoomInfo['info']->start_url,
                    'join_url'                => $zoomInfo['info']->join_url,
                    'timezone'                => $zoomInfo['info']->timezone
                ];

                if (isset($liveScheduleid)) {
                    EduStudentLiveClassSchedule_Teacher::find($liveScheduleid)->update($liveClassZoomData); // UPDATE
                } else {
                    EduStudentLiveClassSchedule_Teacher::create($liveClassZoomData); //CREATE
                }
                //END LIVE CLASS ZOOM INFO

                $output['messege'] = 'Live class schedule has been '.$message;
                $output['msgType'] = 'success';
            } else {
                $output['messege'] = "Access token is expired!";
                $output['msgType'] = 'danger';
            }

            DB::commit();

            return redirect()->back()->with($output);

        }else{
            return redirect()->back()->withErrors($validator);
        }
        

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
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function classStatus(Request $request,$class_id,$batch_id)
    {

        $data['class_id'] = $class_id = $class_id;
        $data['batch_id'] = $batch_id = $batch_id;

        $data['status'] = EduAssignBatchClasses_Teacher::valid()->find($class_id)->complete_status;

        return view('teacher.assignBatch.class.updateClassStatus', $data);
    }

    public function updateClassStatus(Request $request,$class_id,$batch_id)
    {

        $status = $request->status;

        $doneAttendence = EduStudentAttendence_Teacher::valid()->where('batch_id',$batch_id)->where('class_id',$class_id)->count();

        $validator = Validator::make($request->all(), [
            'status'         => 'required',
        ]);

        $class = EduAssignBatchClasses_Teacher::where('id',$class_id)->where('batch_id',$batch_id)->first();
        $next_class = EduAssignBatchClasses_Teacher::where('id', '>',$class->id)->where('class_id','>',$class->class_id)->where('batch_id',$batch_id)->first();
        
        $completed_date = date('Y-m-d');
        $completed_day_dt = date('w');
        $day_dt = EduAssignBatchSchedule_Teacher::valid()->where('batch_id',$batch_id)->pluck('day_dt')->toArray();

        if(count($day_dt) > 0)
        {

            $first_class = $day_dt[0];
            $get_day = '';
            foreach ($day_dt as $key => $day) { 
                if($completed_day_dt >= $day)
                {
                    $get_day = $first_class;
                } 
                else 
                {
                    $higher_day = $completed_day_dt-$day;
                    if($higher_day < 0){
                        $get_day = $day;
                    break;
                    }
                }
            }

            $get_day_name = Helper::dayName($get_day);
            $nextDay = strtotime("next"." ".$get_day_name);
            $next_class_date = date('Y-m-d', $nextDay);
    
            if($get_day != '')
            {
                 $get_day_name = Helper::dayName($get_day);
                 $next_class_time = EduAssignBatchSchedule_Teacher::where('batch_id',$batch_id)->where('day_dt',$get_day)->first()->start_time;
            }
            else
            {
                $get_day_name = '';
                $next_class_time = '';
            }
    
            if(empty($get_day_name) || empty($next_class_time)){
                $nextDay = '';
                $next_class_date = '';
                $next_class_time =  '';
            }else{
                $nextDay = strtotime("next"." ".$get_day_name);
                $next_class_date = date('Y-m-d', $nextDay);
                $next_class_time = $next_class_time;
            }
        }else{
            $next_class_date = '';
            $next_class_time =  '';
        }

        // echo $next_class_date; echo "<br>";
        // echo $next_class_time; echo "<br>";
        // echo $next_class; echo "<br>";
        // exit();

        if ($validator->passes()) {

            DB::beginTransaction();
           

            if($completed_date >= $class->start_date){

                if($doneAttendence > 0){

                    EduAssignBatchClasses_Teacher::find($class->id)->update([
                        'end_date'       => $completed_date,
                        'complete_status'=> $status
                    ]);

                    if(!empty($next_class_date) && !empty($next_class_time)){
                        if(!empty($next_class)){
                            EduAssignBatchClasses_Teacher::find($next_class->id)->update([
                                'start_date'     => $next_class_date,
                                'start_time'     => $next_class_time,
                                'complete_status'=> 2, // 2 = running
                            ]);
                        }
                    }

                    $output['messege'] = 'Class Status has been Updated';
                    $output['msgType'] = 'success';

                }else{
                    $output['messege'] = 'Please At first take attendence';
                    $output['msgType'] = 'danger';
                }
                
            }else{

                $output['messege'] = 'Class date is not past yet !!';
                $output['msgType'] = 'danger';
            }

            DB::commit();

            return redirect()->back()->with($output);
        }else{
            return redirect()->back()->withErrors($validator);
        }
        
    }

    public static function zoomPaaword()
    {
        $password = Str::random(8);
        return $password;
    }


}
