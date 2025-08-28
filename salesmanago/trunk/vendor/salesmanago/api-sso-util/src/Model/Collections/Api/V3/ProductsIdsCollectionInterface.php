<?php

namespace SALESmanago\Model\Collections\Api\V3;

interface ProductsIdsCollectionInterface
{
    /**
     * Build array with all products ids
     *
     * @return array - of products ids
     */
    public function idsToArray(): array;
}