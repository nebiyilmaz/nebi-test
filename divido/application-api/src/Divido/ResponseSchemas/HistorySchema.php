<?php

namespace Divido\ResponseSchemas;
use Divido\Services\History\History;

/**
 * Class ApplicationHistorySchema
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019. Divido Financial Services Ltd
 */
class HistorySchema
{
    /**
     * @param History $resource
     * @return array|null
     */
    public function getData(History $resource): ?array
    {
        return [
            'id' => $resource->getId(),
            'application_id' => $resource->getApplicationId(),
            'type' => $resource->getType(),
            'status' => $resource->getStatus(),
            'user' => $resource->getUser(),
            'subject' => $resource->getSubject(),
            'text' => $resource->getText(),
            'internal' => $resource->isInternal(),
            'date' => $resource->getDate()->format("Y-m-d"),
            'ip_address' => $resource->getIpAddress(),
            'created_at' => $resource->getCreatedAt()->format("c"),
            'updated_at' => $resource->getUpdatedAt()->format("c")
        ];
    }
}
