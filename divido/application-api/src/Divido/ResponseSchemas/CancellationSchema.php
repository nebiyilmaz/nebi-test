<?php

namespace Divido\ResponseSchemas;
use Divido\Services\Cancellation\Cancellation;

/**
 * Class CancellationSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class CancellationSchema
{
    /**
     * @param Cancellation $resource
     * @return array|null
     */
    public function getData(Cancellation $resource): ?array
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
