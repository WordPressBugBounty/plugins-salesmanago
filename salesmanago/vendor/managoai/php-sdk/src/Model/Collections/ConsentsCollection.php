<?php

namespace SALESmanago\Model\Collections;

use SALESmanago\Entity\Consent;
use SALESmanago\Exception\Exception;

class ConsentsCollection extends AbstractCollection
{
    const
        CONSENT_DETAILS = 'consentDetails';

    /**
     * @return array
     */
    public function get()
    {
        return $this->collection;
    }

    /**
     * Append consent to consent list.
     *
     * @param  Consent  $object
     *
     * @return $this
     * @throws Exception
     */
    public function addItem($object)
    {
        if (empty($object) || !($object instanceof Consent)) {
            throw new Exception('Not right entity type');
        }
        $this->collection[] = $object;
        return $this;
    }

    /**
     * @return array
     */
   public function toArray(): array
    {
        $consents = [];

        if (!$this->isEmpty()) {
            foreach ($this->collection as $consent) {
                $consents[] = $consent->jsonSerialize();
            }
        }
        return $consents;
    }

    /**
     * @param array $param
     *
     * @throws Exception
     */
    private static function checkParamsValues($param)
    {
        foreach ( $param as $key => $value ) {
            if (!($value instanceof Consent)) {
                throw new Exception('Passed array does\'t contain arrays or Consent object');
            }
        }
    }
}

