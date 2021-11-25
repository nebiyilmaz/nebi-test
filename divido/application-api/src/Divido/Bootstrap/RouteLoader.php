<?php

namespace Divido\Bootstrap;

use Divido\Middleware\TenantMiddleware;
use Slim\App as SlimApplication;

/**
 * Class RouteLoader
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class RouteLoader
{
    /**
     * Prepare routes
     *
     * @param SlimApplication $app
     */
    public function load(SlimApplication $app)
    {
        $app->get('/health', \Divido\Routing\Generic\GetRoutes::class . ':health');
        $app->get('/deps', \Divido\Routing\Generic\GetRoutes::class . ':dependencies');
        $app->get('/not-healthy', \Divido\Routing\Generic\GetRoutes::class . ':exception');

        $app->group('', function () use ($app) {

            $app->post('/applications', \Divido\Routing\Application\PostRoutes::class . ':create');
            $app->get('/applications', \Divido\Routing\Application\GetRoutes::class . ':getAll');
            $app->get('/applications/{id}', \Divido\Routing\Application\GetRoutes::class . ':getOne');

            $app->patch('/applications/{id}', \Divido\Routing\Application\PatchRoutes::class . ':patch');
            $app->delete('/applications/{id}', \Divido\Routing\Application\DeleteRoutes::class . ':delete');

            $app->get('/applications/{applicationId}/histories', \Divido\Routing\History\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/statuses', \Divido\Routing\History\PostRoutes::class . ':createStatus');
            $app->post('/applications/{applicationId}/comments', \Divido\Routing\History\PostRoutes::class . ':createComment');
            $app->get('/histories/{id}', \Divido\Routing\History\GetRoutes::class . ':getOne');
            $app->patch('/histories/{id}', \Divido\Routing\History\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/signatories', \Divido\Routing\Signatory\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/signatories', \Divido\Routing\Signatory\PostRoutes::class . ':create');
            $app->get('/signatories/{id}', \Divido\Routing\Signatory\GetRoutes::class . ':getOne');
            $app->patch('/signatories/{id}', \Divido\Routing\Signatory\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/activations', \Divido\Routing\Activation\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/activations', \Divido\Routing\Activation\PostRoutes::class . ':create');
            $app->get('/activations/{id}', \Divido\Routing\Activation\GetRoutes::class . ':getOne');
            $app->patch('/activations/{id}', \Divido\Routing\Activation\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/deposits', \Divido\Routing\Deposit\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/deposits', \Divido\Routing\Deposit\PostRoutes::class . ':create');
            $app->get('/deposits/{id}', \Divido\Routing\Deposit\GetRoutes::class . ':getOne');
            $app->patch('/deposits/{id}', \Divido\Routing\Deposit\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/cancellations', \Divido\Routing\Cancellation\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/cancellations', \Divido\Routing\Cancellation\PostRoutes::class . ':create');
            $app->get('/cancellations/{id}', \Divido\Routing\Cancellation\GetRoutes::class . ':getOne');
            $app->patch('/cancellations/{id}', \Divido\Routing\Cancellation\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/refunds', \Divido\Routing\Refund\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/refunds', \Divido\Routing\Refund\PostRoutes::class . ':create');
            $app->get('/refunds/{id}', \Divido\Routing\Refund\GetRoutes::class . ':getOne');
            $app->patch('/refunds/{id}', \Divido\Routing\Refund\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/alternative-offers', \Divido\Routing\AlternativeOffer\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/alternative-offers', \Divido\Routing\AlternativeOffer\PostRoutes::class . ':create');
            $app->get('/alternative-offers/{id}', \Divido\Routing\AlternativeOffer\GetRoutes::class . ':getOne');
            $app->patch('/alternative-offers/{id}', \Divido\Routing\AlternativeOffer\PatchRoutes::class . ':patch');

            $app->get('/applications/{applicationId}/submissions', \Divido\Routing\Submission\GetRoutes::class . ':getAll');
            $app->post('/applications/{applicationId}/submissions', \Divido\Routing\Submission\PostRoutes::class . ':create');
            $app->get('/submissions/{id}', \Divido\Routing\Submission\GetRoutes::class . ':getOne');
            $app->patch('/submissions/{id}', \Divido\Routing\Submission\PatchRoutes::class . ':patch');

            $app->post('/event', \Divido\Routing\Event\PostRoutes::class . ':newEvent');

            $app->get('/form-configuration', \Divido\Routing\FormConfiguration\GetRoutes::class . ':index');
            $app->get('/form-configuration/{token}', \Divido\Routing\FormConfiguration\GetRoutes::class . ':render');

            /*
             * TODO:
             * Move query and notification endpoints to own Routing classes
             */
            $app->post('/submit/{token}', \Divido\Routing\LenderCall\PostRoutes::class . ':submit');
            $app->get('/query/{applicationSubmissionId}', \Divido\Routing\LenderCall\GetRoutes::class . ':query');
            $app->get('/call/{applicationSubmissionId}/{callName}', \Divido\Routing\LenderCall\GetRoutes::class . ':call');
            $app->post('/call/{applicationSubmissionId}/{callName}', \Divido\Routing\LenderCall\PostRoutes::class . ':call');
            $app->patch('/call/{applicationSubmissionId}/{callName}', \Divido\Routing\LenderCall\PatchRoutes::class . ':call');
            $app->put('/call/{applicationSubmissionId}/{callName}', \Divido\Routing\LenderCall\PutRoutes::class . ':call');
            $app->delete('/call/{applicationSubmissionId}/{callName}', \Divido\Routing\LenderCall\DeleteRoutes::class . ':call');
            $app->post('/notification/{applicationSubmissionId}', \Divido\Routing\LenderCall\PostRoutes::class . ':notification');

            $app->get('/applicants/{token}', \Divido\Routing\Applicant\GetRoutes::class . ':applicants');
            $app->patch('/applicants/{token}', \Divido\Routing\Applicant\PatchRoutes::class . ':applicants');

            $app->get('/form-data/{token}', \Divido\Routing\Applicant\GetRoutes::class . ':formData');
            $app->patch('/form-data/{token}', \Divido\Routing\Applicant\PatchRoutes::class . ':formData');

            $app->post('/form-data-to-applicants', \Divido\Routing\FormDataToApplicants\PostRoutes::class . ':convert');

        })->add(
            new TenantMiddleware($app->getContainer())
        );
    }
}
