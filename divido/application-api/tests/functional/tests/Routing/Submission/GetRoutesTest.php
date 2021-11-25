<?php

namespace Divido\Test\Functional\Routing\Submission;

use Divido\Test\Functional\ApiTest;
use Divido\Test\Functional\DockerFunctionalTestsHelper;

class GetRoutesTest extends ApiTest
{
    public function insertSubmission($order = 1)
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => $order,
            'decline_referred' => false,
            'lender_id' => 'lender-1',
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'merchant-1-finance-plan-1',
            'status' => 'ACCEPTED',
            'lender_reference' => 'lender reference',
            'lender_loan_reference' => 'lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['lender_data' => true]
        ];

        $request = $this->createRequest('POST', '/applications/' . $applicationId . '/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(
            200,
            $response->getStatusCode(),
            'ERROR: Could not insert proposal with POST in ' . __METHOD__
        );

        return $json->data->id;
    }

    public function test_GetOne_NotFound()
    {
        $request = $this->createRequest('GET', '/submissions/-error-', [], ['X-Divido-Tenant-Id' => 'divido']);

        $response = $this->getHttpClient()->send($request);

        $json = json_decode($response->getBody()->getContents());


        $this->assertSame(404001, $json->code);
        $this->assertSame(true, $json->error);
    }

    public function test_GetOne_Success()
    {
        $id = $this->insertSubmission();

        $request = $this->createRequest('GET', '/submissions/' . $id, [], ['X-Divido-Tenant-Id' => 'divido']);

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsObject($json->data);
        $this->assertObjectHasAttribute('id', $json->data);
        $this->assertObjectHasAttribute('application_id', $json->data);
        $this->assertObjectHasAttribute('order', $json->data);
        $this->assertObjectHasAttribute('decline_referred', $json->data);
        $this->assertObjectHasAttribute('lender_id', $json->data);
        $this->assertObjectHasAttribute('application_alternative_offer_id', $json->data);
        $this->assertObjectHasAttribute('merchant_finance_plan_id', $json->data);
        $this->assertObjectHasAttribute('status', $json->data);
        $this->assertObjectHasAttribute('lender_reference', $json->data);
        $this->assertObjectHasAttribute('lender_loan_reference', $json->data);
        $this->assertObjectHasAttribute('lender_status', $json->data);
        $this->assertObjectHasAttribute('lender_data', $json->data);
        $this->assertIsObject($json->data->lender_data);
        $this->assertObjectHasAttribute('created_at', $json->data);
        $this->assertObjectHasAttribute('updated_at', $json->data);
    }

    public function test_GetAll_Success()
    {
        $id_one = $this->insertSubmission(1);
        $id_two = $this->insertSubmission(2);
        $id_three = $this->insertSubmission(3);

        $new_id_one = 'z-is-the-last-letter-of-the-alphabet';
        $new_id_two = '1-number-starting-id';
        $new_id_three = 'a-simple-id-starting-with-a';

        $id_change = [
            [
                $id_one,
                $new_id_one
            ],
            [
                $id_two,
                $new_id_two
            ],
            [
                $id_three,
                $new_id_three
            ]
        ];

        // Change ID's of all submissions.
        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        foreach ($id_change as $item){
            $statement = $pdo->prepare('UPDATE `application_submission` SET `id` = :new_id WHERE `id` = :old_id');

            $statement->execute(
                [
                    ':new_id' => $item[1],
                    ':old_id' => $item[0]
                ]
            );
        }

        $request = $this->createRequest('GET', '/applications/-proposal-no-submission-/submissions', [], ['X-Divido-Tenant-Id' => 'divido']);

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($json->data);
        $this->assertCount(3, $json->data);

        // Check order number
        $this->assertSame(1, $json->data[0]->order);
        $this->assertSame(2, $json->data[1]->order);
        $this->assertSame(3, $json->data[2]->order);

        // Test that the new ids were set is there in the database
        $this->assertSame($new_id_one, $json->data[0]->id);
        $this->assertSame($new_id_two, $json->data[1]->id);
        $this->assertSame($new_id_three, $json->data[2]->id);

        $row = $json->data[0];
        $this->assertObjectHasAttribute('id', $row);
        $this->assertObjectHasAttribute('application_id', $row);
        $this->assertObjectHasAttribute('order', $row);
        $this->assertObjectHasAttribute('decline_referred', $row);
        $this->assertObjectHasAttribute('lender_id', $row);
        $this->assertObjectHasAttribute('application_alternative_offer_id', $row);
        $this->assertObjectHasAttribute('merchant_finance_plan_id', $row);
        $this->assertObjectHasAttribute('status', $row);
        $this->assertObjectHasAttribute('lender_reference', $row);
        $this->assertObjectHasAttribute('lender_loan_reference', $row);
        $this->assertObjectHasAttribute('lender_status', $row);
        $this->assertObjectHasAttribute('lender_data', $row);
        $this->assertIsObject($row->lender_data);
        $this->assertObjectHasAttribute('created_at', $row);
        $this->assertObjectHasAttribute('updated_at', $row);
    }
}
