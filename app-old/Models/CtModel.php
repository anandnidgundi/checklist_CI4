<?php

namespace App\Models;

use CodeIgniter\Model;

class CtModel extends Model
{
    protected $table = 'ct'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Fields that can be inserted/updated
    protected $allowedFields = ['doctor_name', 'nid', 'doctor_count', 'bmid'];

 

    // Validation rules (optional)
    protected $validationRules = [
        'doctor_name' => 'required|string|max_length[255]',
        'nid'         => 'required|integer', 
        'doctor_count' => 'required|integer',
        'bmid'        => 'required|integer',
    ];

    protected $validationMessages = [
        'doctor_name' => [
            'required' => 'Doctor name is required.',
            'string'   => 'Doctor name must be a valid string.',
        ],
        'nid' => [
            'required' => 'NID is required.',
            'integer'  => 'NID must be a valid integer.',
        ],
        'doctor_count' => [
            'required' => 'Doctor count is required.',
            'integer'  => 'Doctor count must be a valid integer.',
        ],
        'bmid' => [
            'required' => 'BMID is required.',
            'integer'  => 'BMID must be a valid integer.',
        ],
    ];

    public function getCtData($nid){
        $data = $this->where('nid', $nid)->findAll();
        return $data;
    }
 

        public function saveCtData()
    {
        $CtModel = new \App\Models\CtModel();

        // Sample data
        $data = [
            'doctor_name'  => $this->request->getPost('doctor_name'),
            'nid'          => $this->request->getPost('nid'),
            'doctor_count' => $this->request->getPost('doctor_count'),
            'bmid'         => $this->request->getPost('bmid'),
        ];

        if ($CtModel->save($data)) {
            return $this->respond(['status' => true, 'message' => 'Data saved successfully.']);
        } else {
            return $this->respond(['status' => false, 'message' => $CtModel->errors()]);
        }
    }

}
