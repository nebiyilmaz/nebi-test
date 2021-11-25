<?php

namespace Divido\ResponseSchemas;

use Divido\Services\Activation\Activation;

/**
 * Class ActivationSchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class ActivationSchema
{
    /**
     * @param Activation $resource
     * @return array|null
     */
    public function getData(Activation $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'status' => $resource->getStatus(),
            'amount' => $resource->getAmount(),
            'product_data' => $resource->getProductData(),
            'reference' => $resource->getReference(),
            'delivery_method' => $resource->getDeliveryMethod(),
            'tracking_number' => $resource->getTrackingNumber(),
            'comment' => $resource->getComment(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
