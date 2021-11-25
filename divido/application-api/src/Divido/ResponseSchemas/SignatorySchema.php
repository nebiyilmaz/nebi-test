<?php

namespace Divido\ResponseSchemas;
use Divido\Services\Signatory\Signatory;

/**
 * Class ApplicationSignatorySchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class SignatorySchema
{
    /**
     * @param Signatory $resource
     * @return array|null
     */
    public function getData(Signatory $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'first_name' => $resource->getFirstName(),
            'last_name' => $resource->getLastName(),
            'email_address' => $resource->getEmailAddress(),
            'title' => $resource->getTitle(),
            'date_of_birth' => $resource->getDateOfBirth()->format("Y-m-d"),
            'lender_reference' => $resource->getLenderReference(),
            'hosted_signing' => $resource->isHostedSigning(),
            'data_raw' => $resource->getDataRaw(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
