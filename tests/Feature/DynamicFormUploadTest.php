<?php

use CodeIgniter\Test\FeatureTestCase;

/**
 * @extends \CodeIgniter\Test\FeatureTestCase
 */
class DynamicFormUploadTest extends FeatureTestCase
{
     public function test_upload_to_missing_checklist_returns_404()
     {
          // Use an unlikely checklist id to avoid collisions
          $id = 999999;
          $result = $this->call('post', 'dynamic-form/' . $id . '/photos');

          $this->assertEquals(404, $result->getStatusCode());
          $body = json_decode((string)$result->getBody(), true);
          $this->assertIsArray($body);
          $this->assertStringContainsString('Checklist not found', $body['message'] ?? '');
     }

     public function test_upload_to_existing_checklist_without_files_returns_400()
     {
          $db = \Config\Database::connect();
          // Insert a lightweight checklist row
          $data = [
               'centre_name' => 'Test Centre',
               'date_of_visit' => date('Y-m-d'),
               'status' => 'draft'
          ];
          $db->table('branding_checklists')->insert($data);
          $id = $db->insertID();
          $this->assertNotEmpty($id, 'Failed to create checklist for test');

          // Call upload with no files
          $result = $this->call('post', 'dynamic-form/' . $id . '/photos');
          $this->assertEquals(400, $result->getStatusCode());
          $body = json_decode((string)$result->getBody(), true);
          $this->assertIsArray($body);
          $this->assertStringContainsString('No files uploaded', $body['message'] ?? '');

          // Cleanup
          $db->table('branding_checklists')->delete(['id' => $id]);
     }

     public function test_file_upload_persists_row_and_file_on_disk()
     {
          $db = \Config\Database::connect();
          // create checklist
          $data = [
               'centre_name' => 'Upload Test Centre',
               'date_of_visit' => date('Y-m-d'),
               'status' => 'draft'
          ];
          $db->table('branding_checklists')->insert($data);
          $id = $db->insertID();
          $this->assertNotEmpty($id, 'Failed to create checklist for upload test');

          // create a tiny PNG file in temp
          $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_upload_' . uniqid() . '.png';
          $pngHeader = hex2bin('89504E470D0A1A0A0000000D49484452');
          file_put_contents($tmp, $pngHeader . random_bytes(64));
          $this->assertFileExists($tmp);

          // Prepare $_FILES style array for multiple photos[]
          $files = [
               'photos' => [
                    'name' => [basename($tmp)],
                    'type' => ['image/png'],
                    'tmp_name' => [$tmp],
                    'error' => [0],
                    'size' => [filesize($tmp)],
               ],
          ];

          // Perform upload
          $result = $this->call('post', 'dynamic-form/' . $id . '/photos', [], [], $files);

          // Assert success
          $this->assertEquals(201, $result->getStatusCode());
          $body = json_decode((string)$result->getBody(), true);
          $this->assertIsArray($body);
          $this->assertNotEmpty($body['saved'] ?? []);
          $savedFilename = $body['saved'][0] ?? null;
          $this->assertNotEmpty($savedFilename, 'No saved filename returned');

          // Check DB row present
          $row = $db->table('branding_photos')->where('checklist_id', $id)->where('filename', $savedFilename)->get()->getRowArray();
          $this->assertNotEmpty($row, 'DB row for uploaded photo not found');

          // Check file on disk
          $uploadPath = WRITEPATH . 'uploads/branding_photos' . DIRECTORY_SEPARATOR . $savedFilename;
          $this->assertFileExists($uploadPath, 'Uploaded file not found on disk');

          // Cleanup
          if (is_file($uploadPath)) @unlink($uploadPath);
          $db->table('branding_photos')->where('checklist_id', $id)->delete();
          $db->table('branding_checklists')->delete(['id' => $id]);
          if (is_file($tmp)) @unlink($tmp);
     }

     public function test_dynamic_submission_upload_creates_checklist_and_photo()
     {
          $db = \Config\Database::connect();
          // create a form_submissions row
          $sub = [
               'form_id' => 1,
               'dept_id' => 1,
               'header' => 'Test dynamic submission',
               'status' => 'draft',
               'created_by' => 'testuser',
               'created_dtm' => date('Y-m-d H:i:s')
          ];
          $db->table('form_submissions')->insert($sub);
          $subId = $db->insertID();
          $this->assertNotEmpty($subId, 'Failed to create form_submissions row');

          // create a tiny PNG file in temp
          $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_upload_' . uniqid() . '.png';
          $pngHeader = hex2bin('89504E470D0A1A0A0000000D49484452');
          file_put_contents($tmp, $pngHeader . random_bytes(64));
          $this->assertFileExists($tmp);

          // prepare files array
          $files = [
               'photos' => [
                    'name' => [basename($tmp)],
                    'type' => ['image/png'],
                    'tmp_name' => [$tmp],
                    'error' => [0],
                    'size' => [filesize($tmp)],
               ],
          ];

          // perform upload to dynamic-form/{submissionId}/photos
          $result = $this->call('post', 'dynamic-form/' . $subId . '/photos', [], [], $files);
          $this->assertEquals(201, $result->getStatusCode());
          $body = json_decode((string)$result->getBody(), true);
          $this->assertIsArray($body);
          $this->assertNotEmpty($body['saved'] ?? []);
          $savedFilename = $body['saved'][0] ?? null;
          $this->assertNotEmpty($savedFilename, 'No saved filename returned');

          // Verify branding_checklists row was created and preferrably re-used the submission id when possible
          $chk = $db->table('branding_checklists')->where('centre_name', 'Imported from dynamic-form ' . $subId)->get()->getRowArray();
          $this->assertNotEmpty($chk, 'branding_checklists row not created for dynamic submission');
          $chkId = $chk['id'] ?? null;
          $this->assertNotEmpty($chkId);

          // If we were able to create a checklist with the same id as submission, assert that
          if (is_int($subId) && $subId > 0) {
               $this->assertEquals((int)$subId, (int)$chkId, 'Checklist id should match submission id when possible');
          }

          // Verify photo row exists supporting that checklist
          $row = $db->table('branding_photos')->where('checklist_id', $chkId)->where('filename', $savedFilename)->get()->getRowArray();
          $this->assertNotEmpty($row, 'branding_photos row not created for uploaded file');

          // cleanup
          if (!empty($savedFilename)) {
               $uploadPath = WRITEPATH . 'uploads/branding_photos' . DIRECTORY_SEPARATOR . $savedFilename;
               if (is_file($uploadPath)) @unlink($uploadPath);
          }
          $db->table('branding_photos')->where('checklist_id', $chkId)->delete();
          $db->table('branding_checklists')->delete(['id' => $chkId]);
          $db->table('form_submissions')->delete(['id' => $subId]);
          if (is_file($tmp)) @unlink($tmp);
     }
}
