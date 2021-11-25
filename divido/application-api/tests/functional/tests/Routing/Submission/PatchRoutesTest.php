<?php

namespace Divido\Test\Functional\Routing\Submission;

use Divido\Test\Functional\ApiTest;

class PatchRoutesTest extends ApiTest
{
    public function insertSubmission()
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => 1,
            'decline_referred' => false,
            'lender_id' => 'lender-1',
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'merchant-1-finance-plan-1',
            'status' => 'REFERRED',
            'lender_reference' => 'lender reference',
            'lender_loan_reference' => 'lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['lender_data' => true]
        ];

        $request = $this->createRequest('POST', '/applications/' . $applicationId . '/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());

        return $json->data->id;
    }

    public function test_UpdateTenant_Success()
    {
        $id = $this->insertSubmission();

        $data = (object) [
            'order' => 2,
            'decline_referred' => true,
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'merchant-1-finance-plan-2',
            'status' => 'ACCEPTED',
            'lender_reference' => 'updated lender reference',
            'lender_loan_reference' => 'updated lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['updated' => true]
        ];
        $request = $this->createRequest('PATCH', '/submissions/' . $id, [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsObject($json->data);
        $this->assertObjectHasAttribute('id', $json->data);
        $this->assertSame($data->order, $json->data->order);
        $this->assertObjectHasAttribute('application_id', $json->data);
        $this->assertSame($data->decline_referred, $json->data->decline_referred);
        $this->assertObjectHasAttribute('lender_id', $json->data);
        $this->assertSame($data->application_alternative_offer_id, $json->data->application_alternative_offer_id);
        $this->assertSame($data->merchant_finance_plan_id, $json->data->merchant_finance_plan_id);
        $this->assertSame($data->status, $json->data->status);
        $this->assertSame($data->lender_reference, $json->data->lender_reference);
        $this->assertSame($data->lender_loan_reference, $json->data->lender_loan_reference);
        $this->assertSame($data->lender_status, $json->data->lender_status);
        $this->assertSame($data->lender_data->updated, $json->data->lender_data->updated);
        $this->assertObjectHasAttribute('created_at', $json->data);
        $this->assertObjectHasAttribute('updated_at', $json->data);
    }

    public function test_UpdateTenant_NotFound()
    {
        $id = $this->insertSubmission();

        $data = (object) [
            'order' => 2,
            'decline_referred' => true,
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'non-existing',
            'status' => 'ACCEPTED',
            'lender_reference' => 'updated lender reference',
            'lender_loan_reference' => 'updated lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['updated' => true]
        ];
        $request = $this->createRequest('PATCH', '/submissions/' . 'id_that_does_not_exist', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);

        $this->assertSame(404, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        $this->assertObjectHasAttribute('code', $json);
        $this->assertObjectHasAttribute('error', $json);

        $this->assertSame(404001, $json->code);
        $this->assertSame(true, $json->error);
    }

    public function test_Update_InvalidLenderDataArray()
    {
        $id = $this->insertSubmission();

        $data = (object) [
            'order' => 2,
            'decline_referred' => true,
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'non-existing',
            'status' => 'ACCEPTED',
            'lender_reference' => 'updated lender reference',
            'lender_loan_reference' => 'updated lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => ['invalid', 'array']
        ];
        $request = $this->createRequest('PATCH', '/submissions/' . $id, [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);

        $this->assertSame(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(400002, $json->code);
        $this->assertSame(true, $json->error);
    }

    public function test_Update_InvalidJSON()
    {
        $id = $this->insertSubmission();

        $request = $this->createRequest('PATCH', '/submissions/' . $id, [], ['X-Divido-Tenant-Id' => 'divido'], 'not_even_json');

        $response = $this->getHttpClient()->send($request);

        $this->assertSame(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents());
        $this->assertSame(true, $json->error);
        $this->assertSame(400003, $json->code);
    }

    public function test_Update_InvalidLenderDataString()
    {
        $id = $this->insertSubmission();

        $data = (object) [
            'order' => 2,
            'decline_referred' => true,
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'non-existing',
            'status' => 'ACCEPTED',
            'lender_reference' => 'updated lender reference',
            'lender_loan_reference' => 'updated lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => 'invalid-string-should-be-object'
        ];
        $request = $this->createRequest('PATCH', '/submissions/' . $id, [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(400002, $json->code);
        $this->assertSame(true, $json->error);
    }
}
