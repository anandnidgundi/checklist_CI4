<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\FileModel;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use App\Services\JwtService;

class FileUpload extends BaseController
{
    use ResponseTrait;


    // {
    //     helper(['form', 'url']);

    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code;

    //     // Set validation rules for the uploaded file
    //     $validationRule = [
    //         'file' => [
    //             'rules' => 'uploaded[file]|mime_in[file,image/jpeg,image/png,application/pdf]|max_size[file,5120]',
    //             'errors' => [
    //                 'uploaded' => 'No file selected.',
    //                 'mime_in' => 'Invalid file type. Allowed types: jpg, png, pdf.',
    //                 'max_size' => 'File exceeds the maximum size of 5MB.',
    //             ],
    //         ],
    //     ];

    //     if (!$this->validate($validationRule)) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'errors' => $this->validator->getErrors(),
    //         ]);
    //     }

    //     $file = $this->request->getFile('file');

    //     if ($file->isValid() && !$file->hasMoved()) {
    //         $fileName = $file->getClientName(); // Get the original file name
    //         $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . date('YmdHis') . '.' . $file->getClientExtension(); // Append dateTime to the original file name
    //         $file->move(WRITEPATH . 'uploads', $fileName); // Move the file to the uploads directory

    //         // Prepare data for database insertion
    //         $fileData = [
    //             'file_name' => $fileName,
    //             'mid' => $this->request->getPost('mid'),
    //             'nid' => $this->request->getPost('nid'),
    //             'cm_mid' => $this->request->getPost('cm_mid'),
    //             'cm_nid' => $this->request->getPost('cm_nid'),
    //             'emp_code' => $user,
    //             'createdDTM' => date('Y-m-d H:i:s'),
    //         ];

    //         $fileModel = new FileModel();
    //         $insertedId = $fileModel->insert($fileData);

    //         if ($insertedId) {
    //             return $this->response->setJSON([
    //                 'status' => 'success',
    //                 'message' => 'File uploaded successfully.',
    //                 'data' => ['f_id' => $insertedId],
    //             ]);
    //         } else {
    //             return $this->response->setJSON([
    //                 'status' => 'error',
    //                 'message' => 'Failed to save file details in the database.',
    //             ]);
    //         }
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'error',
    //         'message' => 'Invalid file upload.',
    //     ]);
    // }

    // public function uploadFile()
    // {
    //     helper(['form', 'url']);

    //     $userDetails = $this->validateAuthorization();
    //     $user = $userDetails->emp_code;
    //     $file = $this->request->getFile('file');
    //       // Log file details for debugging
    //       //log_message('error', 'File MIME Type: ' . $file->getMimeType());
    //       //log_message('error', 'File Client Extension: ' . $file->getClientExtension());
    //       //log_message('error', 'File Client Name: ' . $file->getClientName());

    //     $validationRule = [
    //         'file' => [
    //         'rules' => 'uploaded[file]|mime_in[file,image/jpeg,image/png,application/pdf,application/octet-stream]|max_size[file,5120]',
    //         'errors' => [
    //             'uploaded' => 'No file selected.',
    //             'mime_in' => 'Invalid file type. Allowed types: jpg, png, pdf.',
    //             'max_size' => 'File exceeds the maximum size of 5MB.',
    //         ],
    //         ],
    //     ];

    //     if (!$this->validate($validationRule)) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'errors' => $this->validator->getErrors(),
    //         ]);
    //     }


    //     if ($file->isValid() && !$file->hasMoved()) {
    //         $fileName = pathinfo($file->getClientName(), PATHINFO_FILENAME)
    //         . '_' . date('YmdHis') . '.' . $file->getClientExtension();
    //         $uploadPath = FCPATH . 'public/uploads';
    //         if (!is_dir($uploadPath)) {
    //         mkdir($uploadPath, 0777, true);
    //         }
    //         $file->move($uploadPath, $fileName);

    //         $fileData = [
    //             'file_name' => trim($fileName),
    //             'mid' => $this->request->getPost('mid'),
    //             'nid' => $this->request->getPost('nid'),
    //             'cm_mid' => $this->request->getPost('cm_mid'),
    //             'cm_nid' => $this->request->getPost('cm_nid'),
    //             'bmw_id' => $this->request->getPost('bmw_id'),
    //             'emp_code' => $user,
    //             'createdDTM' => date('Y-m-d H:i:s'),
    //         ];

    //         $fileModel = new FileModel();
    //         $insertedId = $fileModel->insert($fileData);

    //         if ($insertedId) {
    //             return $this->response->setJSON([
    //                 'status' => 'success',
    //                 'message' => 'File uploaded successfully.',
    //                 'data' => ['file_id' => $insertedId],
    //             ]);
    //         } else {
    //             //log_message('error', 'Database insert failed for file: ' . $fileName);
    //             return $this->response->setJSON([
    //                 'status' => 'error',
    //                 'message' => 'Failed to save file details in the database.',
    //             ]);
    //         }
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'error',
    //         'message' => 'Invalid file upload.',
    //     ]);
    // }

    public function uploadFile()
    {
        helper(['form', 'url']);
        //use services to validate .pdf files only

        $file = $this->request->getFile('file');
        // Check for embedded scripts in PDF files
        if ($file->getClientExtension() === 'pdf') {
            // Basic PDF validation - check for JavaScript markers
            $content = file_get_contents($file->getRealPath());
            if (stripos($content, '/JavaScript') !== false || stripos($content, '/JS') !== false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'PDF file contains embedded scripts which are not allowed.',
                ]);
            }
        }

        // Validate user authentication
        $userDetails = $this->validateAuthorization();
        if (!$userDetails) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access.',
            ]);
        }

        $user = $userDetails->emp_code;
        $file = $this->request->getFile('file');

        // Log file details for debugging
        // log_message('error', 'File MIME Type: ' . $file->getMimeType());
        // log_message('error', 'File Client Extension: ' . $file->getClientExtension());
        // log_message('error', 'File Client Name: ' . $file->getClientName());

        // Define allowed MIME types and extensions
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        // Validation Rules
        $validationRule = [
            'file' => [
                'rules' => 'uploaded[file]|mime_in[file,image/jpeg,image/png,application/pdf]|max_size[file,5120]|ext_in[file,jpg,jpeg,png,pdf]',
                'errors' => [
                    'uploaded' => 'No file selected.',
                    'mime_in' => 'Invalid file type. Allowed: jpg, png, pdf.',
                    'max_size' => 'File size exceeds 5MB.',
                    'ext_in' => 'Invalid file extension.',
                ],
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        // Extra MIME type validation (prevents spoofing)
        if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid file MIME type detected.',
            ]);
        }

        // Ensure file is valid and hasn't been moved
        if ($file->isValid() && !$file->hasMoved()) {
            // Generate a unique filename
            $newFileName = pathinfo($file->getClientName(), PATHINFO_FILENAME)
                . '_' . date('Mdys') . '.' . $file->getClientExtension();

            // Secure storage path outside public access
            $uploadPath = WRITEPATH . 'uploads/secure_files';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Move file to secure directory
            $file->move($uploadPath, $newFileName);

            // File metadata storage
            $fileData = [
                'file_name' => htmlspecialchars(trim($newFileName), ENT_QUOTES, 'UTF-8'),
                'mid' => htmlspecialchars($this->request->getPost('mid'), ENT_QUOTES, 'UTF-8'),
                'nid' => htmlspecialchars($this->request->getPost('nid'), ENT_QUOTES, 'UTF-8'),
                'cm_mid' => htmlspecialchars($this->request->getPost('cm_mid'), ENT_QUOTES, 'UTF-8'),
                'cm_nid' => htmlspecialchars($this->request->getPost('cm_nid'), ENT_QUOTES, 'UTF-8'),
                'bmw_id' => htmlspecialchars($this->request->getPost('bmw_id'), ENT_QUOTES, 'UTF-8'),
                'emp_code' => htmlspecialchars($user, ENT_QUOTES, 'UTF-8'),
                'createdDTM' => date('Y-m-d H:i:s'),
            ];

            // Save file details in the database
            $fileModel = new FileModel();
            $insertedId = $fileModel->insert($fileData);

            if ($insertedId) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'File uploaded successfully.',
                    'data' => ['file_id' => $insertedId],
                ]);
            } else {
                log_message('error', 'Database insert failed for file: ' . $newFileName);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to save file details in the database.',
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid file upload.',
        ]);
    }

    public function viewAttachment($fileName)
    {
        // Validate user authorization
        $userDetails = $this->validateAuthorization();
        if (!$userDetails) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ]);
        }

        // Sanitize filename and construct path
        $fileName = basename(htmlspecialchars($fileName));
        $filePath = WRITEPATH . 'uploads/secure_files/' . $fileName;

        // Check if file exists
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'File not found or not accessible'
            ]);
        }

        // Get file info
        $fileSize = filesize($filePath);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Clear any previous output
        ob_clean();

        // Set common headers
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        header("Content-Length: {$fileSize}");
        header('Accept-Ranges: bytes');

        // Handle different file types
        switch ($extension) {
            case 'pdf':
                header('Content-Type: application/pdf');
                header("Content-Disposition: inline; filename=\"{$fileName}\"");
                break;

            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                header("Content-Disposition: inline; filename=\"{$fileName}\"");
                break;

            case 'png':
                header('Content-Type: image/png');
                header("Content-Disposition: inline; filename=\"{$fileName}\"");
                break;

            default:
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"{$fileName}\"");
                break;
        }

        // Output file content directly
        readfile($filePath);
        exit();
    }


    public function getFiles()
    {
       
        $userDetails = $this->validateAuthorization();
         $role = $userDetails->role;
         $user = $userDetails->emp_code;
        $fileModel = new FileModel();
       

        if ($this->request->getPost('mid') > 0) {
            $mid = $this->request->getPost('mid');
            $files = $fileModel->where('mid', $mid)->findAll();
        
        } else if ($this->request->getPost('nid') > 0) {
            $nid = $this->request->getPost('nid');
            $files = $fileModel->where('nid', $nid)->findAll();
        } else if ($this->request->getPost('cm_mid' > 0)) {
            $cm_mid = $this->request->getPost('cm_mid');
            $files = $fileModel->where('cm_mid', $cm_mid)->findAll();
        } else if ($this->request->getPost('cm_nid') > 0) {
            $cm_nid = $this->request->getPost('cm_nid');
            $files = $fileModel->where('cm_nid', $cm_nid)->findAll();
        } else if ($this->request->getPost('bmw_id') > 0) {
            $bmw_id = $this->request->getPost('bmw_id');
            $files = $fileModel->where('bmw_id', $bmw_id)->findAll();
        } else {
            $files = [];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }




    public function downloadFile($fileId)
    {
        $fileModel = new FileModel();
        $file = $fileModel->find($fileId);
        if (!$file) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File not found.',
            ]);
        }
        $filePath = WRITEPATH . 'uploads/' . $file['file_name'];
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null);
        }
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'File not found.',
        ]);
    }


    public function download($filename)
    {
        // Sanitize the filename to avoid path traversal
        $filename = basename($filename);

        // Full path to the file
        $filePath = WRITEPATH . 'uploads/secure_files/' . $filename;

        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($filename);
        } else {
            return $this->failNotFound('File not found');
        }
    }



    public function deleteFile()
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;
        $fileModel = new FileModel();
        $file_id = $this->request->getPost('file_id');

        $file = $fileModel->find($file_id);

        if (!$file) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File not found.',
            ]);
        }

        $filePath = WRITEPATH . 'uploads/' . $file['file_name'];

        if (file_exists($filePath)) {
            unlink($filePath);
            $fileModel->delete($file_id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'File deleted successfully.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'File not found.',
        ]);
    }

    public function getFileList()
    {
        $fileModel = new FileModel();
        $files = $fileModel->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }

    public function getFileDetails($fileId)
    {
        $fileModel = new FileModel();
        $file = $fileModel->find($fileId);

        if (!$file) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $file,
        ]);
    }

    public function getFilesByUser($user)
    {
        $fileModel = new FileModel();
        $files = $fileModel->where('emp_code', $user)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }

    public function getFilesByUserAndRole($user, $role)
    {
        $fileModel = new FileModel();
        $files = $fileModel->where('emp_code', $user)->where('role', $role)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }

    public function getFilesByUserAndRoleAndDate($user, $role, $date)
    {
        $fileModel = new FileModel();
        $files = $fileModel->where('emp_code', $user)->where('role', $role)->where('createdDTM', $date)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }

    public function getFilesByUserAndDate($user, $date)
    {
        $fileModel = new FileModel();
        $files = $fileModel->where('emp_code', $user)->where('createdDTM', $date)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $files,
        ]);
    }




    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            //log_message( 'error', 'JwtService class not found' );
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->getHeader('Authorization') ? $this->request->getHeader('Authorization')->getValue() : null;
        //log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken($authorizationHeader);

        // Handle token validation errors
        if (isset($result['error'])) {
            //log_message( 'error', $result[ 'error' ] );
            return $this->respond(['error' => $result['error']], $result['status']);
        }

        // Extract the decoded token and get the USER-ID
        $decodedToken = $result['data'];
        return $decodedToken;
        // Assuming JWT contains USER-ID

    }

}