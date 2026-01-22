<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PestcontrolModel;
use App\Models\FileModel;
use App\Services\JwtService;

class PestControl extends ResourceController
{
    protected $pestcontrolModel;

    public function __construct()
    {
        $this->pestcontrolModel = new PestcontrolModel();
    }

    public function createPestControl()
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        // Get request data with better input handling
        $input = null;
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = $this->request->getBody();
            $input = json_decode($rawInput, true);
        } else {
            $input = $this->request->getPost();
        }

        // Log the raw input for debugging
        // log_message('error', "Raw Input: " . print_r($this->request->getBody(), true));
        // log_message('error', "Content Type: $contentType");
        // log_message('error', "Parsed Input: " . print_r($input, true));

        if (empty($input) || $input === null) {
            return $this->respond(['status' => 'error', 'message' => 'No input data received'], 400);
        }

        // Validate required fields
        $requiredFields = ['vendor_id', 'branch_id'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                return $this->respond(['status' => 'error', 'message' => ucfirst($field) . ' is required'], 400);
            }
        }

        $data = [
            'service_date' => $input['service_date'] ?? null,
            'visiter_name' => $input['visiter_name'] ?? null,
            'visiter_mobile' => $input['visiter_mobile'] ?? null,
            'remarks' => $input['remarks'] ?? null,
            'branch_id' => $input['branch_id'],
            'vendor_id' => $input['vendor_id'],
             
            'createdDTM' => date('Y-m-d H:i:s'),
            'createdBy' =>  $emp_code,
        ];

        $insertId = $this->pestcontrolModel->insert($data);
        // log_message('error', "Insert ID: $insertId");
        if (!$insertId) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to insert data'], 500);
        }

        // File handling remains the same
        // File handling: Support multiple files from 'files[]'
        $files = $this->request->getFiles();

        if (isset($files['files']) && is_array($files['files'])) {
            foreach ($files['files'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (!in_array(strtolower($file->getExtension()), $allowedTypes)) {
                        log_message('error', "Invalid file type: " . $file->getClientName());
                        continue; // Skip this file, optionally collect errors
                    }

                    if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                        log_message('error', "File too large: " . $file->getClientName());
                        continue; // Skip this file
                    }

                    $uploadPath = WRITEPATH . 'uploads/secure_files';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $fileName = $file->getRandomName();
                    $file->move($uploadPath, $fileName);

                    $fileData = [
                        'file_name' => $fileName,
                        'pest_control_id' => (int)$insertId,
                        'emp_code' => $emp_code,
                        'createdDTM' => date('Y-m-d H:i:s'),
                    ];

                    log_message('error', "File Data: " . print_r($fileData, true));

                    $fileModel = new FileModel();
                    $fileModel->insert($fileData);
                }
            }
        }

        return $this->respond(['status' => 'success', 'message' => 'Pest control record created successfully'], 200);
    }

    // public function createPestControl()
    // {
    //     $tokenDecoded = $this->validateAuthorization();
    //     $emp_code = $tokenDecoded->emp_code;

    //     // Get request data with better input handling
    //     $input = null;
    //     $contentType = $this->request->getHeaderLine('Content-Type');

    //     if (strpos($contentType, 'application/json') !== false) {
    //         $rawInput = $this->request->getBody();
    //         $input = json_decode($rawInput, true);
    //     } else {
    //         $input = $this->request->getPost();
    //     }

    //     // Log the raw input for debugging
    //     // log_message('error', "Raw Input: " . print_r($this->request->getBody(), true));
    //     // log_message('error', "Content Type: $contentType");
    //     // log_message('error', "Parsed Input: " . print_r($input, true));

    //     if (empty($input) || $input === null) {
    //         return $this->respond(['status' => 'error', 'message' => 'No input data received'], 400);
    //     }

    //     // Validate required fields
    //     $requiredFields = ['vendor_id', 'branch_id'];
    //     foreach ($requiredFields as $field) {
    //         if (!isset($input[$field]) || empty($input[$field])) {
    //             return $this->respond(['status' => 'error', 'message' => ucfirst($field) . ' is required'], 400);
    //         }
    //     }

    //     $data = [
    //         'service_date' => $input['service_date'] ?? null,
    //         'visiter_name' => $input['visiter_name'] ?? null,
    //         'visiter_mobile' => $input['visiter_mobile'] ?? null,
    //         'remarks' => $input['remarks'] ?? null,
    //         'branch_id' => $input['branch_id'],
    //         'vendor_id' => $input['vendor_id'],
    //         'scheduled_date' => $input['scheduled_date'] ?? null,
    //         'createdDTM' => date('Y-m-d H:i:s'),
    //         'createdBy' =>  $emp_code,
    //     ];

    //     $insertId = $this->pestcontrolModel->insert($data);
    //     // log_message('error', "Insert ID: $insertId");
    //     if (!$insertId) {
    //         return $this->respond(['status' => 'error', 'message' => 'Failed to insert data'], 500);
    //     }

    //     // File handling remains the same
    //     $file = $this->request->getFile('file');
    //     if ($file && $file->isValid() && !$file->hasMoved()) {
    //         $allowedTypes = ['jpg', 'png', 'pdf'];
    //         if (!in_array($file->getExtension(), $allowedTypes)) {
    //             return $this->respond(['status' => 'error', 'message' => 'Invalid file type'], 400);
    //         }

    //         if ($file->getSize() > 2097152) {
    //             return $this->respond(['status' => 'error', 'message' => 'File size exceeds 2MB limit'], 400);
    //         }

    //         $uploadPath = WRITEPATH . 'uploads/secure_files';
    //         if (!is_dir($uploadPath)) {
    //             mkdir($uploadPath, 0777, true);
    //         }

    //         $fileName = $file->getClientName();
    //         $file->move($uploadPath, $fileName);

    //         $fileData = [
    //             'file_name' => $fileName,
    //             'pest_control_id' => (int)$insertId,
    //             'emp_code' => $emp_code,
    //             'createdDTM' => date('Y-m-d H:i:s'),
    //         ];
    //         log_message('error', "File Data: " . print_r($fileData, true));
    //         $fileModel = new FileModel();
    //         $fileModel->insert($fileData);
    //     }

    //     return $this->respond(['status' => 'success', 'message' => 'Pest control record created successfully'], 200);
    // }

    public function getPestControlById($pid = null)
    {
        $tokenDecoded = $this->validateAuthorization();

        $builder = $this->pestcontrolModel->builder();
        $builder->select('pest_control.*, branches.SysField as branch_name, branches.Address');

        // Join with branches table from secondary database
        $db2 = \Config\Database::connect('secondary');
        $branchesTable = $db2->database . '.branches';
        $builder->join($branchesTable, 'pest_control.branch_id = branches.id', 'left');

        $fileModel = new FileModel();
        if ($pid) {
            $builder->where('pest_control.pid', $pid);
            $query = $builder->get();
            $record = $query->getRowArray();

            if ($record) {
                $files = $fileModel->where('pest_control_id', $pid)->findAll();
                $record['files'] = $files;
                return $this->respond(['status' => 'success', 'data' => $record], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Record not found'], 404);
            }
        } else {
            $query = $builder->get();
            $records = $query->getResultArray();
            foreach ($records as &$row) {
                $files = $fileModel->where('pest_control_id', $row['pid'])->findAll();
                $row['files'] = $files;
            }
            return $this->respond(['status' => 'success', 'data' => $records], 200);
        }
    }




    public function updatePestControl($pid)
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        // Get request data with better input handling
        $input = null;
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = $this->request->getBody();
            $input = json_decode($rawInput, true);
        } else if (strpos($contentType, 'multipart/form-data') !== false) {
            $input = $this->request->getPost();
        } else {
            $input = $this->request->getRawInput();
        }


        log_message('error', "Raw Input: " . print_r($this->request->getBody(), true));
        log_message('error', "Content Type: $contentType");
        log_message('error', "Parsed Input: " . print_r($input, true));

        if (empty($input) || $input === null) {
            return $this->respond(['status' => 'error', 'message' => 'No input data received'], 400);
        }

        $data = [
            'service_date' => $input['service_date'] ?? null,
            'visiter_name' => $input['visiter_name'] ?? null,
            'visiter_mobile' => $input['visiter_mobile'] ?? null,
            'remarks' => $input['remarks'] ?? null,
            'branch_id' => $input['branch_id'] ?? null,
            'vendor_id' => $input['vendor_id'] ?? null,     
            'status' => $input['status'] ?? null,   
            'updatedDTM' => date('Y-m-d H:i:s'),
            'updatedBy' => $emp_code
        ];

        $this->pestcontrolModel->update($pid, $data);

        // File handling: Support multiple files from 'files[]'
        $files = $this->request->getFiles();

        if (isset($files['files']) && is_array($files['files'])) {
            foreach ($files['files'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (!in_array(strtolower($file->getExtension()), $allowedTypes)) {
                        log_message('error', "Invalid file type: " . $file->getClientName());
                        continue;
                    }

                    if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                        log_message('error', "File too large: " . $file->getClientName());
                        continue;
                    }

                    $uploadPath = WRITEPATH . 'uploads/secure_files';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    $fileName = $file->getRandomName();
                    $file->move($uploadPath, $fileName);

                    $fileData = [
                        'file_name' => $fileName,
                        'pest_control_id' => (int)$pid,
                        'emp_code' => $emp_code,
                        'createdDTM' => date('Y-m-d H:i:s'),
                    ];

                    $fileModel = new FileModel();
                    $fileModel->insert($fileData);
                }
            }
        }

        return $this->respond(['status' => 'success', 'message' => 'Pest control record updated successfully'], 200);
    }

    public function deletePestControl()
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        // Parse JSON input
        $json = $this->request->getJSON();
        $pid = $json->pid ?? null;

        if (!$pid) {
            return $this->respond(['status' => 'error', 'message' => 'PID is required'], 400);
        }

        try {
            $status = 'I'; // Set status to 'I' for inactive
            $data = [
                'status' => $status,
                'updatedDTM' => date('Y-m-d H:i:s'),
                'updatedBy' => $emp_code
            ];
            
            $result = $this->pestcontrolModel->update($pid, $data);
            
            if ($result) {
                return $this->respond(['status' => 'success', 'message' => 'Pest control record deleted successfully'], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Failed to delete record'], 500);
            }
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'Error processing request'], 500);
        }
    }
    public function getPestControlList()
    {
        try {
            $this->validateAuthorization();

            // Get the pest control data with branch information
            $builder = $this->pestcontrolModel->builder();
            $builder->select('pest_control.*, branches.SysField as branch_name, branches.Address, vendor.*');
            //where status = A 
            $builder->where('pest_control.status', 'A');

            // Join vendor table with better error handling
            $builder->join('vendor', 'pest_control.vendor_id = vendor.vendor_id', 'left');

            // Join with branches table from secondary database
            $db2 = \Config\Database::connect('secondary');
            $branchesTable = $db2->database . '.branches';
            $builder->join($branchesTable, 'pest_control.branch_id = branches.id', 'left');

            $query = $builder->get();
            if (!$query) {
                return $this->respond(['status' => 'error', 'message' => 'Failed to fetch data'], 500);
            }

            $result = $query->getResultArray();
            // send files list with pest control data where pid = pest_control_id
            $fileModel = new FileModel();
            foreach ($result as &$row) {
                $files = $fileModel->where('pest_control_id', $row['pid'])->findAll();
                $row['files'] = $files;
            }


            return $this->respond(['status' => 'success', 'data' => $result], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'An error occurred while fetching data'], 500);
        }
    }

    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            ////log_message( 'error', 'JwtService class not found' );
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->header('Authorization')?->getValue();
        ////log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken($authorizationHeader);

        // Handle token validation errors
        if (isset($result['error'])) {
            ////log_message( 'error', $result[ 'error' ] );
            return $this->respond(['error' => $result['error']], $result['status']);
        }

        // Extract the decoded token and get the USER-ID
        $decodedToken = $result['data'];
        return $decodedToken;
        // Assuming JWT contains USER-ID

    }
}
