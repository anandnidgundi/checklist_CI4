<?php

/**
 * @extends \CodeIgniter\Test\FeatureTestCase
 */
class DynamicFormValidationTest extends \CodeIgniter\Test\FeatureTestCase
{
     public function test_required_input_without_value_is_rejected()
     {
          $db = \Config\Database::connect();

          // create a deterministic form id for the test
          $formId = 9999;

          // Insert a required input for the form
          $input = [
               'input_name' => 'req_field',
               'input_label' => 'Required Field',
               'input_type' => 'text',
               'input_required' => 1,
               'form_id' => $formId,
               'status' => 'A',
          ];
          $db->table('form_inputs')->insert($input);
          $inputId = $db->insertID();
          $this->assertNotEmpty($inputId);

          // Attempt to submit without providing a value for the required input
          $payload = [
               'form_id' => $formId,
               'dept_id' => 1,
               'records' => [
                    // intentionally omit record for req_field
               ],
          ];

          $result = $this->withBody(json_encode($payload))->post('dynamic-form');
          $this->assertEquals(400, $result->getStatusCode());
          $body = json_decode((string)$result->getBody(), true);
          $this->assertIsArray($body);
          $this->assertIsArray($body['message'] ?? null);
          $this->assertStringContainsString('Required Field is required', implode(' | ', $body['message'] ?? []));

          // cleanup
          $db->table('form_inputs')->delete(['id' => $inputId]);
     }

     public function test_conditional_required_field_behaves_like_frontend()
     {
          $db = \Config\Database::connect();
          $formId = 9998;

          // Controller field
          $ctrl = [
               'input_name' => 'ctrl_field',
               'input_label' => 'Controller Field',
               'input_type' => 'text',
               'form_id' => $formId,
               'status' => 'A',
          ];
          $db->table('form_inputs')->insert($ctrl);
          $ctrlId = $db->insertID();
          $this->assertNotEmpty($ctrlId);

          // Dependent field: required when ctrl_field == 'yes'
          $dep = [
               'input_name' => 'dep_field',
               'input_label' => 'Dependent Field',
               'input_type' => 'text',
               'required_when_field' => 'ctrl_field',
               'required_when_value' => 'yes',
               'form_id' => $formId,
               'status' => 'A',
          ];
          $db->table('form_inputs')->insert($dep);
          $depId = $db->insertID();
          $this->assertNotEmpty($depId);

          // Case A: ctrl = 'yes' and dep missing -> validation should fail
          $payloadA = [
               'form_id' => $formId,
               'dept_id' => 1,
               'records' => [
                    ['section_id' => 1, 'input_id' => $ctrlId, 'input_value' => 'yes'],
                    // dep omitted
               ],
          ];

          $resA = $this->withBody(json_encode($payloadA))->post('dynamic-form');
          $this->assertEquals(400, $resA->getStatusCode());
          $bodyA = json_decode((string)$resA->getBody(), true);
          $this->assertIsArray($bodyA);
          $this->assertStringContainsString('Dependent Field is required', implode(' | ', $bodyA['message'] ?? []));

          // Case B: ctrl = 'no' and dep missing -> should succeed
          $payloadB = [
               'form_id' => $formId,
               'dept_id' => 1,
               'records' => [
                    ['section_id' => 1, 'input_id' => $ctrlId, 'input_value' => 'no'],
               ],
          ];

          $resB = $this->withBody(json_encode($payloadB))->post('dynamic-form');
          $this->assertEquals(201, $resB->getStatusCode());

          // cleanup
          $db->table('form_inputs')->delete(['id' => $ctrlId]);
          $db->table('form_inputs')->delete(['id' => $depId]);
     }
}
