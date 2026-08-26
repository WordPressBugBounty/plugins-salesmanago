<?php

namespace SALESmanago\Helper;

interface JsonDeserializable
{
    /**
     * Deserialize data
     *
     * @param string $data
     * @return void
     */
    public static function jsonDeserialize(string $json): self;
}