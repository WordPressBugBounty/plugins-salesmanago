<?php

namespace SALESmanago\Model\Api\V3;

use SALESmanago\Entity\Api\V3\CatalogEntityInterface;

class CatalogModel
{

    /**
     * Build request body for delete catalog api method
     *
     * @param CatalogEntityInterface $Catalog
     * @return array
     */
    public function buildForDelete(CatalogEntityInterface $Catalog): array
    {
        return ['catalogId' => $Catalog->getId()];
    }
}