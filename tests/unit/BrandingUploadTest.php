<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\BrandingChecklistController;

class BrandingUploadTest extends CIUnitTestCase
{
     public function test_upload_forbidden_for_unallowed_role()
     {
          // Arrange
          $ctrl = new class extends BrandingChecklistController {
               public $mockUser;
               public function __construct()
               {
                    parent::__construct();
               }
               protected function validateAuthorizationNew()
               {
                    return $this->mockUser;
               }
               public function setModelPublic($m)
               {
                    $this->model = $m;
               }
               public function invokeUpload($id)
               {
                    return $this->uploadPhoto($id);
               }
          };

          $ctrl->mockUser = (object)['role' => 'ZONAL_MANAGER', 'emp_code' => '5000018'];

          // Provide a model that simulates existing checklist
          $mockModel = new class {
               public function find($id)
               {
                    return ['id' => $id, 'created_by' => '5000018'];
               }
               public function addPhoto() {}
               public function getPhotos()
               {
                    return [];
               }
          };
          $ctrl->setModelPublic($mockModel);

          // Act
          $result = $ctrl->invokeUpload(4);

          // Assert
          $this->assertEquals(403, $result->getStatusCode());
     }

     public function test_branding_auditor_cannot_upload_for_others()
     {
          $ctrl = new class extends BrandingChecklistController {
               public $mockUser;
               public function __construct()
               {
                    parent::__construct();
               }
               protected function validateAuthorizationNew()
               {
                    return $this->mockUser;
               }
               public function setModelPublic($m)
               {
                    $this->model = $m;
               }
               public function invokeUpload($id)
               {
                    return $this->uploadPhoto($id);
               }
          };

          $ctrl->mockUser = (object)['role' => 'BRANDING_AUDITOR', 'emp_code' => '5000018'];
          // Checklist created by someone else
          $mockModel = new class {
               public function find($id)
               {
                    return ['id' => $id, 'created_by' => '5000020'];
               }
               public function addPhoto() {}
               public function getPhotos()
               {
                    return [];
               }
          };
          $ctrl->setModelPublic($mockModel);

          $result = $ctrl->invokeUpload(5);

          $this->assertEquals(403, $result->getStatusCode());
     }

     public function test_branding_auditor_can_upload_own_checklist_but_no_files_returns_400()
     {
          $ctrl = new class extends BrandingChecklistController {
               public $mockUser;
               public function __construct()
               {
                    parent::__construct();
               }
               protected function validateAuthorizationNew()
               {
                    return $this->mockUser;
               }
               public function setModelPublic($m)
               {
                    $this->model = $m;
               }
               public function invokeUpload($id)
               {
                    return $this->uploadPhoto($id);
               }
          };

          $ctrl->mockUser = (object)['role' => 'BRANDING_AUDITOR', 'emp_code' => '5000018'];
          $mockModel = new class {
               public function find($id)
               {
                    return ['id' => $id, 'created_by' => '5000018'];
               }
               public function addPhoto()
               {
                    return true;
               }
               public function getPhotos()
               {
                    return [];
               }
          };
          $ctrl->setModelPublic($mockModel);

          // Act
          $result = $ctrl->invokeUpload(6);

          // Expect 400 because no files provided in the request
          $this->assertEquals(400, $result->getStatusCode());
     }
}
