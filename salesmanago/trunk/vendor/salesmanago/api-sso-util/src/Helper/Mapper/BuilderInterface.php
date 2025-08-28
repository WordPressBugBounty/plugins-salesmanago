<?php

namespace SALESmanago\Helper\Mapper;

interface BuilderInterface
{
    public function build($toObject, string $map, AdapterInterface $adapter = null );
}