<?php

namespace Divido\ResponseSchemas;

use Divido\Services\Deposit\Deposit;

/**
 * Class DepositSchema
 *
 * @author Mike Lovely <mike.lovely@divido.com>
 * @copyright (c) 2020. Divido Financial Services Ltd
 */
class DepositSchema
{
    /**
     * @param Deposit $resource
     * @return array|null
     */
    public function getData(Deposit $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'status' => $resource->getStatus(),
            'amount' => $resource->getAmount(),
            'merchant_comment' => $resource->getMerchantComment(),
            'type' => $resource->getType(),
            'reference' => $resource->getReference(),
            'data' => $resource->getDataRaw(),
            'product_data' => $resource->getProductData(),
            'merchant_reference' => $resource->getMerchantReference(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
