<?php

namespace Divido\ResponseSchemas;

use Divido\Services\Submission\Submission;

/**
 * Class SubmissionSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2018. Divido Financial Services Ltd
 */
class SubmissionSchema
{
    /**
     * @param Submission $resource
     * @return array|null
     */
    public function getData(Submission $resource): ?array
    {
        return [
            "id" => $resource->getId(),
            "application_id" => $resource->getApplicationId(),
            "order" => $resource->getOrder(),
            "decline_referred" => $resource->isDeclineReferred(),
            "lender_id" => $resource->getLenderId(),
            "application_alternative_offer_id" => $resource->getApplicationAlternativeOfferId(),
            "merchant_finance_plan_id" => $resource->getMerchantFinancePlanId(),
            "status" => $resource->getStatus(),
            "lender_reference" => $resource->getLenderReference(),
            "lender_loan_reference" => $resource->getLenderLoanReference(),
            "lender_status" => $resource->getLenderStatus(),
            "lender_data" => $resource->getLenderData(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
