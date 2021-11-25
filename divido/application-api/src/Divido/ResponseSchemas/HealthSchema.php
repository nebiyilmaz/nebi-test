<?php

namespace Divido\ResponseSchemas;

use Divido\Services\Health\Health;

/**
 * Class HealthSchema
 *
 * @copyright (c) 2018. Divido Financial Services Ltd
 */
class HealthSchema
{
    /**
     * @param Health $resource
     * @return array|null
     */
    public function getData(Health $resource): ?array
    {
        return [
            'status' => 'ok',
            'checked_at'=>$resource->getCheckedAt(),
            'service'=>'application-api'
        ];
    }
}
