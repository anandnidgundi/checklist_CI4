<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class Register extends BaseController {
    use ResponseTrait;

    public function index() {
        $rules = [
            'emp_code' => [ 'rules' => 'required|max_length[255]|is_unique[new_emp_master.emp_code]' ],
            'password' => [ 'rules' => 'required|min_length[6]|max_length[255]' ],
            'confirm_password' => [ 'label' => 'confirm password', 'rules' => 'matches[password]' ]
        ];

        if ( $this->validate( $rules ) ) {
            $model = new UserModel();
            $data = [
                'emp_code' => $this->request->getVar( 'emp_code' ),
                'password' => md5( $this->request->getVar( 'password' ) )
            ];
            $model->save( $data );

            return $this->respond( [ 'message' => 'Registered Successfully' ], 200 );
        } else {
            $response = [
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail( $response, 409 );

        }

    }
}