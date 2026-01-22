<?php

namespace App\Models;

use CodeIgniter\Model;

class DocModel extends Model
{
    protected $table = 'doc_count'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Fields that can be inserted/updated
    protected $allowedFields = ['doctor_name', 'nid', 'mri','ct','xray','cardio_ecg','cardio_tmt','createdDTM', 'createdBy', 'bmid'];

  

    // Validation rules (optional)
    protected $validationRules = [
        'doctor_name' => 'required|string|max_length[255]',
        'nid'         => 'required|integer',
        'mri' =>        'required|integer',
        'ct' =>        'required|integer',
        'xray' =>        'required|integer',
        'cardio_ecg' =>        'required|integer',
        'cardio_tmt' =>        'required|integer',
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
        
        'bmid' => [
            'required' => 'BMID is required.',
            'integer'  => 'BMID must be a valid integer.',
        ],
    ];

    public function getDocData($nid){
        $DocModel = new \App\Models\DocModel();
        $data = $DocModel->where('nid', $nid)->findAll();
        return $data;
    }

    public function saveDocData()
    {
        $DocModel = new \App\Models\DocModel();

        // Sample data
        $data = [
            'doctor_name'  => $this->request->getPost('doctor_name'),
            'nid'          => $this->request->getPost('nid'),
            'mri'          => $this->request->getPost('mri'),
            'ct'           => $this->request->getPost('ct'),
            'xray'         => $this->request->getPost('xray'),
            'cardio_ecg'   => $this->request->getPost('cardio_ecg'),
            'cardio_tmt'   => $this->request->getPost('cardio_tmt'),
            'bmid'         => $this->request->getPost('bmid'),
            'createdDTM'    =>date('Y-m-d H:i:s'),
            'cretedBy'      => $this->request->getPost('createdBy'),
        ];

        if ($DocModel->save($data)) {
            return $this->respond(['status' => true, 'message' => 'Data saved successfully.']);
        } else {
            return $this->respond(['status' => false, 'message' => $DocModel->errors()]);
        }
 }
   

}
