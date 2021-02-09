<?php

namespace App\Imports;

use App\Models\EduStudent_Provider;
use Illuminate\Support\Facades\Hash;
use Helper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    use Importable;

    public function model(array $row)
    {
        return new EduStudent_Provider([
            'student_id'   => Helper::generateAutoID('users','student_id'),
            'name'         => $row[0],
            'email'        => $row[1], 
            'phone'        => '0'.$row[2], 
            'address'      => $row[3], 
            'backup_phone' => $row[4], 
            'fb_profile'   => $row[5], 
            'password'     => Hash::make('123456789'),
        ]);

    }
    
    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'unique:App\Models\User,email',
            '2' => 'unique:App\Models\User,phone',
        ];
    }

}
