<?php

namespace bhr\Includes;

class SmProduct {

	private $id;
	private $variantId;
	private $sku;
	private $unitPrice;
	private $name;

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return SmProduct
	 */
	public function setId( $id ) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getVariantId() {
		return $this->variantId;
	}

	/**
	 * @param mixed $variantId
	 * @return SmProduct
	 */
	public function setVariantId( $variantId ) {
		$this->variantId = $variantId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSku() {
		return $this->sku;
	}

	/**
	 * @param mixed $sku
	 * @return SmProduct
	 */
	public function setSku( $sku ) {
		$this->sku = $sku;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUnitPrice() {
		return $this->unitPrice;
	}

	/**
	 * @param mixed $unitPrice
	 * @return SmProduct
	 */
	public function setUnitPrice( $unitPrice ) {
		$this->unitPrice = $unitPrice;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 * @return SmProduct
	 */
	public function setName( $name ) {
		$this->name = $name;
		return $this;
	}


}
