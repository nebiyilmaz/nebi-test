<?php

namespace Divido\Services\Tenant;

use Divido\ApiExceptions\TenantMissingOrInvalidException;

/**
 * Class TenantService
 *
 * @property TenantDatabaseInterface tenantDatabaseInterface
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class TenantService
{
    /**
     * @var string
     */
    private $defaultTenant;

    /**
     * @var TenantDatabaseInterface
     */
    private $tenantDatabaseInterface;

    /**
     * MerchantPortalService constructor.
     * @param string $defaultTenant
     * @param TenantDatabaseInterface $tenantDatabaseInterface
     */
    function __construct(string $defaultTenant, TenantDatabaseInterface $tenantDatabaseInterface)
    {
        $this->defaultTenant = $defaultTenant;
        $this->tenantDatabaseInterface = $tenantDatabaseInterface;
    }

    /**
     * @param null $id
     * @return Tenant
     * @throws TenantMissingOrInvalidException
     */
    public function getOne($id = null): Tenant
    {
        $id = ($id) ? $id: $this->defaultTenant;

        return $this->tenantDatabaseInterface->getTenantFromId($id);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->tenantDatabaseInterface->getAllPlatformEnvironments();
    }
}
