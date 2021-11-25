<?php

namespace Divido\ResponseSchemas;
use Divido\Services\Refund\Refund;

/**
 * Class RefundSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class RefundSchema
{
    /**
     * @param Refund $resource
     * @return array|null
     */
    public function getData(Refund $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'status' => $resource->getStatus(),
            'amount' => $resource->getAmount(),
            'product_data' => $resource->getProductData(),
            'reference' => $resource->getReference(),
            'comment' => $resource->getComment(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
