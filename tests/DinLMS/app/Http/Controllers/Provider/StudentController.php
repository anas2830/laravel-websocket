<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Helper;
use File;
use Auth;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\EduStudent_Provider;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['all_students'] = EduStudent_Provider::valid()->orderBy('id', 'asc')->get();
        return view('provider.student.listData', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('provider.student.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input =  $request->all();
        $addStuType = $request->addStuType;
        if($addStuType == 1){ //Single Input
            $validator['name'] = 'required';
            $validator['email'] = 'unique:App\Models\User,email';
            $validator['phone'] = 'unique:App\Models\User,phone';
        } else{ 
            $validator['csvFile'] = 'required';
        }
        $validator = Validator::make($input, $validator);

        if ($validator->passes()) {
            if ($addStuType == 1) { //Single Input
                EduStudent_Provider::create([
                    'student_id'   => $request->student_id,
                    'sur_name'     => $request->sur_name,
                    'name'         => $request->name,
                    'address'      => $request->address,
                    'email'        => $request->email,
                    'password'     => Hash::make(123456789),
                    'phone'        => $request->phone,
                    'backup_phone' => $request->backup_phone,
                    'fb_profile'   => $request->fb_profile,
                ]);
                $output['messege'] = 'Student has been created';
                $output['msgType'] = 'success';
            } else {
                $file = $request->csvFile;
                $path = public_path('uploads/csv');
                $fileOriginalName = $file->getClientOriginalName();
                $file->move($path, $fileOriginalName);

                if(strtolower($file->guessClientExtension())=='xls' || strtolower($file->guessClientExtension())=='csv') {
                    Excel::import(new UsersImport, $path.'/'.$fileOriginalName);
                    $output['messege'] = 'Student has been created';
                    $output['msgType'] = 'success';

                } else {
                    if(strtolower($file->guessClientExtension())=='bin') {
                        $output['messege'] = 'Give maximum 8000 row at a file.';
                        $output['msgType'] = 'danger';
                    } else {
                        echo json_encode(['errorMsg' => 'File is not CSV. This file format is '.$file->guessClientExtension().'.']);
                        $output['messege'] = 'File is not CSV. This file format is '.$file->guessClientExtension();
                        $output['msgType'] = 'danger';
                    }
                }
            }
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
        $data['student'] = EduStudent_Provider::valid()->find($id);
        return view('provider.student.update', $data);
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

        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'email'      => 'unique:App\Models\User,email,'.$id,
            'phone'      => 'unique:App\Models\User,phone,'.$id,
        ]);

        if ($validator->passes()) {

            if(!empty($request->password)){

                EduStudent_Provider::find($id)->update([
                    'student_id'  => $request->student_id,
                    'sur_name'    => $request->sur_name,
                    'name'        => $request->name,
                    'address'     => $request->address,
                    'email'       => $request->email,
                    'password'    => Hash::make($request->password),
                    'phone'       => $request->phone,
               ]);

            }else{
                EduStudent_Provider::find($id)->update([
                    'student_id'  => $request->student_id,
                    'sur_name'    => $request->sur_name,
                    'name'        => $request->name,
                    'address'     => $request->address,
                    'email'       => $request->email,
                    'phone'       => $request->phone,
               ]);
            }
            
            $output['messege'] = 'Student has been updated';
            $output['msgType'] = 'success';

            return redirect()->back()->with($output);

        } else {
            return redirect()->back()->withErrors($validator);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EduStudent_Provider::valid()->find($id)->delete();
    }

    //TRAINEE USER LOGIN
    public function traineeUserLogin(Request $request)
    {
        $userId = $request->id;
        $data = array(
            'id'            => $userId,
            // 'active_status' => 1,
            'valid'         => 1
        );
        $user = EduStudent_Provider::where('valid', 1)->find($userId);
        
        $output = array();
        
        if (!empty($user)) {
            Auth::loginUsingId($userId);
            $output["result"] = true;
        } else {
            $output["result"] = false;
            $output["msg"] = "Id is not valid or verified.";
        }
        return json_encode($output);
    }
}
