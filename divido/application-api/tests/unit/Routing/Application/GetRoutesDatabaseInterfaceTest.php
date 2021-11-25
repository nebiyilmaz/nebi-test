<?php

namespace Divido\Test\Unit\Routing\Application;

use DateTime;
use Divido\Cache\CacheInterface;
use Divido\Proxies\JsonFuse;
use Divido\Proxies\LenderApplicationStatusWkrProxy;
use Divido\Proxies\Webhook;
use Divido\Routing\Application\GetRoutes;
use Divido\Services\Application\ApplicationCreationService;
use Divido\Services\Application\ApplicationDatabaseInterface;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Application\ApplicationSubmissionService;
use Divido\Services\History\HistoryDatabaseInterface;
use Divido\Services\Tenant\Tenant;
use Divido\Services\Tenant\TenantService;
use Divido\Test\Unit\Routing\RouteTestCase;
use Redis;
use Slim\Http\Response;

class GetRoutesDatabaseInterfaceTest extends RouteTestCase
{
    public function test_WhenSendingLanguageCode_LanguageId_GetsSetAsQueryParamToDB(): void
    {
        $request = $this->createRequest('getAll', [], ['filter[language_code]' => 'en']);

        $tenantService = \Mockery::spy(TenantService::class);
        $tenantService->shouldReceive('getOne')->andReturn(
            (new Tenant())->setId('tenatnid')
        );

        $mockedPdo = \Mockery::spy(\PDO::class);
        $applicationService = \Mockery::spy(
            ApplicationService::class,
            [
                $tenantService,
                $databaseInterface = \Mockery::spy(
                    ApplicationDatabaseInterface::class,
                    [
                        $mockedPdo,
                        $mockedPdo,
                    ]
                ),
                \Mockery::spy(ApplicationSubmissionService::class),
                \Mockery::spy(ApplicationCreationService::class),
                \Mockery::spy(HistoryDatabaseInterface::class),
                \Mockery::spy(JsonFuse::class),
                \Mockery::spy(Webhook::class),
                \Mockery::spy(LenderApplicationStatusWkrProxy::class),
                \Mockery::spy(CacheInterface::class),
            ]
        );
        $applicationService->makePartial();
        $databaseInterface->makePartial();

        // Counting.
        $pdoCountStatement = \Mockery::spy(\PDOStatement::class);

        $mockedPdo->shouldReceive('prepare')->withArgs(
            static function ($countStatement) {
                if (substr($countStatement, 0, 12) !== 'SELECT COUNT') {
                    return false;
                }
                self::assertMatchesRegularExpression('/a\.language_id = \:alanguageid/', $countStatement, 'Count statement did not contain language_id');

                return true;
            }
        )->andReturn(
            $pdoCountStatement
        );

        $pdoCountStatement->shouldReceive('execute')->withArgs(
            static function ($pdoParams) {
                self::assertArrayHasKey(
                    ':alanguageid',
                    $pdoParams
                );
                self::assertSame(
                    'en',
                    $pdoParams[':alanguageid'],
                );

                return true;
            }
        );

        $pdoCountStatement->shouldReceive('fetch')->withNoArgs()->andReturn(
            (object) [
                'rows' => 1,
            ]
        );

        // Fetching data.
        $pdoFetchStatement = \Mockery::spy(\PDOStatement::class);

        $mockedPdo->shouldReceive('prepare')->withArgs(
            static function ($fetchStatement) {
                if (substr($fetchStatement, 0, 9) !== 'SELECT a.') {
                    return false;
                }
                // Check what we are using the language_id database field.
                self::assertMatchesRegularExpression('/a\.language_id = \:alanguageid/', $fetchStatement, 'Fetch statement did not contain language_id');

                return true;
            }
        )->andReturn(
            $pdoFetchStatement
        );

        $pdoFetchStatement->shouldReceive('execute')->withArgs(
            static function ($pdoParams) {
                self::assertArrayHasKey(
                    ':alanguageid',
                    $pdoParams
                );
                self::assertSame(
                    'en',
                    $pdoParams[':alanguageid'],
                );

                return true;
            }
        );

        $pdoFetchStatement->shouldReceive('fetchAll')->withNoArgs()->andReturn(
            [
                (object) [
                    'id'                         => '',
                    'token'                      => '',
                    'platform_environment_id'    => '',
                    'branch_id'                  => '',
                    'application_submission_id'  => '',
                    'country_id'                 => '',
                    'currency_id'                => '',
                    'language_id'                => '',
                    'merchant_id'                => '',
                    'customer_id'                => '',
                    'merchant_channel_id'        => '',
                    'merchant_api_key_id'        => '',
                    'merchant_user_id'           => '',
                    'finalised'                  => false,
                    'finalisation_required'      => false,
                    'status'                     => '',
                    'purchase_price'             => 36000,
                    'deposit_amount'             => '360',
                    'deposit_status'             => '',
                    'lender_fee'                 => 0,
                    'lender_fee_reported_date'   => null,
                    'form_data'                  => "{}",
                    'applicants'                 => "{}",
                    'product_data'               => '{}',
                    'metadata'                   => '{}',
                    'commission'                 => 0,
                    'partner_commission'         => 0,
                    'merchant_reference'         => '',
                    'merchant_response_url'      => '',
                    'merchant_checkout_url'      => '',
                    'merchant_redirect_url'      => '',
                    'finance_settings'           => "{}",
                    'terms'                      => '{"amounts": {"deposit_amount": 3000, "purchase_amount": 49700}}',
                    'merchant_finance_id'        => '',
                    'merchant_finance_option_id' => 0,
                    'available_finance_options'  => '{}',
                    'cancelled_amount'           => 0,
                    'cancelled_amount_total'     => 0,
                    'activated_amount'           => 0,
                    'activatable_amount_total'   => 0,
                    'refunded_amount'            => 0,
                    'refunded_amount_total'      => 0,
                    'signer_collection_id'       => '',
                    'created_at'                 => (new DateTime())->format('c'),
                    'updated_at'                 => (new DateTime())->format('c'),
                ],
            ]
        );

        $this->container['Service.Application'] = $applicationService;

        $routes = new GetRoutes($this->container);

        $response = $routes->getAll($request, new Response());

        $json = json_decode($response->getBody()->getContents());

        $this->assertObjectHasAttribute('meta', $json);

        $this->assertObjectHasAttribute('data', $json);
        $this->assertCount(1, $json->data);

        $application = $json->data[0];
        $this->assertEquals(49700, $application->purchase_price);
        $this->assertEquals(3000, $application->deposit_amount);
    }
}
