<?php

namespace Divido\ResponseSchemas;

use Divido\Services\AlternativeOffer\AlternativeOffer;

/**
 * Class AlternativeOfferSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class AlternativeOfferSchema
{
    /**
     * @param AlternativeOffer $resource
     * @return array|null
     */
    public function getData(AlternativeOffer $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'lender_id' => $resource->getLenderId(),
            'data' => $resource->getData(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
