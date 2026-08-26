<?php

namespace SALESmanago\Entity\Api\V3\Product;

use SALESmanago\Entity\DetailsInterface;
use SALESmanago\Helper\DataHelper;
use Exception;

class CustomDetailsEntity implements DetailsInterface
{
    /**
     * @var array
     */
    protected $details = [];

    /**
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($methodName, $arguments) {
        if (strpos($methodName, 'setDetail') === 0)
        {
            $detailNumber = (int) str_replace('setDetail', '', $methodName);
            return $this->set($arguments[0], $detailNumber);
        }

        if (strpos($methodName, 'getDetail') === 0)
        {
            $detailNumber = (int) str_replace('getDetail', '', $methodName);
            return $this->get($detailNumber);
        }

        throw new Exception("Method {$methodName} does not exist.");
    }

    /**
     * @inheritDoc
     */
    public function set($value, $index = null)
    {
        if ($index != null) {
            $this->details[$index] = $value;
        } else {
            $this->details[] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get($index = null)
    {
        return $this->details[$index] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function unset($index = null)
    {
        if (!isset($this->details[$index])) {
           return $this;
        }

        unset($this->details[$index]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->details = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $response = [];

        if (empty($this->details)) {
            return $response;
        }

        foreach ($this->details as $key => $detail) {
            if ($detail === null) {
                continue;
            }

            $response['detail' . $key] = (string) $detail;
        }

        return DataHelper::filterDataArray($response);
    }

	/**
	 * return array
	 */
	public function getEmptyFields() {
		$emptyFields = [];

		for ($detail = 1; $detail <= 5; $detail++) {
			if (empty($this->get($detail))) {
				$emptyFields[] = "detail" . $detail;
			}
		}

		return $emptyFields;
	}
}
