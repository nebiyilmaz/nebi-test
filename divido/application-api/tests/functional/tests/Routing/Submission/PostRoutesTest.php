<?php

namespace Divido\Test\Functional\Routing\Submission;

use Divido\ApiExceptions\TenantMissingOrInvalidException;
use Divido\Test\Functional\ApiTest;
use Divido\Test\Functional\DockerFunctionalTestsHelper;

class PostRoutesTest extends ApiTest
{
    public function test_AddSubmission_ReturnsSuccess()
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => 1,
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

        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsObject($json->data);
        $this->assertNotEmpty($json->data->id);
        $this->assertSame($applicationId, $json->data->application_id);
        $this->assertSame($data->order, $json->data->order);
        $this->assertSame($data->decline_referred, $json->data->decline_referred);
        $this->assertSame($data->lender_id, $json->data->lender_id);
        $this->assertSame($data->application_alternative_offer_id, $json->data->application_alternative_offer_id);
        $this->assertSame($data->merchant_finance_plan_id, $json->data->merchant_finance_plan_id);
        $this->assertSame($data->status, $json->data->status);
        $this->assertSame($data->lender_reference, $json->data->lender_reference);
        $this->assertSame($data->lender_status, $json->data->lender_status);
        $this->assertIsObject($json->data->lender_data);
        $this->assertObjectHasAttribute('created_at', $json->data);
        $this->assertObjectHasAttribute('updated_at', $json->data);
    }

    public function test_AddSubmission_ShouldNotThrowErrorsWhenSettingsJSONIsEmpty()
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => 1,
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

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        $statement = $pdo->prepare('UPDATE `lender` SET `settings` = :settings WHERE `id` = :id');

        $statement->execute(
            [
                ':id' => 'lender-1',
                ':settings' => ''
            ]
        );

        $request = $this->createRequest('POST', '/applications/' . $applicationId . '/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_AddSubmission_ShouldNotCareIfFaultyDataInDatabase()
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => 1,
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

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        $statement = $pdo->prepare('UPDATE `lender` SET `settings` = :settings WHERE `id` = :id');

        $statement->execute(
            [
                ':id' => 'lender-1',
                ':settings' => 'NOTVALIDJSON'
            ]
        );

        $request = $this->createRequest('POST', '/applications/' . $applicationId . '/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_AddSubmission_ShouldNotThrowErrorsWhenParsingValidLenderSettingsJSON()
    {
        $applicationId = '-proposal-no-submission-';

        $data = (object) [
            'order' => 1,
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

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        $statement = $pdo->prepare('UPDATE `lender` SET `settings` = :settings WHERE `id` = :id');

        $statement->execute(
            [
                ':id' => 'lender-1',
                ':settings' => json_encode(
                    [
                        'lender' => 'settings',
                        'as' => 'json data'
                    ]
                )
            ]
        );

        $request = $this->createRequest('POST', '/applications/' . $applicationId . '/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_AddNewSubmission_InvalidStatus()
    {
        $data = (object) [
            'order' => 1,
            'decline_referred' => false,
            'lender_id' => 'lender-1',
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'merchant-1-finance-plan-1',
            'status' => 'INVALID',
            'lender_reference' => 'lender reference',
            'lender_loan_reference' => 'lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['lender_data' => true]
        ];
        $request = $this->createRequest('POST', '/applications/-proposal-no-submission-/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(400002, $json->code);
        $this->assertSame(true, $json->error);
    }

    public function test_AddNewSubmission_InvalidLender()
    {
        $data = (object) [
            'order' => 1,
            'decline_referred' => false,
            'lender_id' => 'invalid-lender',
            'application_alternative_offer_id' => null,
            'merchant_finance_plan_id' => 'merchant-1-finance-plan-1',
            'status' => 'ACCEPTED',
            'lender_reference' => 'lender reference',
            'lender_loan_reference' => 'lender loan reference',
            'lender_status' => 'approved',
            'lender_data' => (object) ['lender_data' => true]
        ];
        $request = $this->createRequest('POST', '/applications/-proposal-no-submission-/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);

        $contents = $response->getBody()->getContents();

        $this->assertSame(404, $response->getStatusCode(), print_r(json_decode($contents), true));

        $json = json_decode($contents);

        $this->assertSame(404001, $json->code);
        $this->assertSame(true, $json->error);
    }

    public function test_AddNewSubmission_DeletedLender()
    {
        $data = (object) [
            'order' => 1,
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

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        $statement = $pdo->prepare('UPDATE `lender` SET `deleted_at` = :deleted_at WHERE `id` = :id');

        $statement->execute(
            [
                ':id' => 'lender-1',
                ':deleted_at' => (new \DateTimeImmutable('now'))->format("Y-m-d H:i:s")
            ]
        );

        $request = $this->createRequest('POST', '/applications/-proposal-no-submission-/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $response = $this->getHttpClient()->send($request);

        $this->assertSame(404, $response->getStatusCode(), 'Should return 404 if the lender is deleted.');

        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(404001, $json->code);
        $this->assertSame(true, $json->error);

    }

    public function test_AddSubmission_DeletedTenant()
    {
        $data = (object) [
            'order' => 1,
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

        $pdo = DockerFunctionalTestsHelper::getPlatformDbAsPdo();
        $statement = $pdo->prepare('UPDATE `platform_environment` SET `deleted_at` = :deleted_at WHERE `code` = :code')->execute(
            [
                'code' => 'divido',
                ':deleted_at' => (new \DateTimeImmutable('now'))->format("Y-m-d H:i:s")
            ]
        );

        $request = $this->createRequest('POST', '/applications/-proposal-no-submission-/submissions', [], ['X-Divido-Tenant-Id' => 'divido'], json_encode(['data' => $data]));

        $this->getHttpClient()->send($request);

        $response = $this->getHttpClient()->send($request);
        $json = json_decode($response->getBody()->getContents());

        $this->assertSame(404, $response->getStatusCode(), 'Should respond with 404.');
        $this->assertSame((new TenantMissingOrInvalidException(''))->getCode(), $json->code);
        $this->assertSame(true, $json->error);

    }
}
