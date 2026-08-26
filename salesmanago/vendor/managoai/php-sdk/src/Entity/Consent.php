<?php

namespace SALESmanago\Entity;

class Consent extends AbstractEntity
{
    protected $consentName   = '';
    protected $consentAccept = false;
    protected $agreementDate = null;
    protected $ip            = '';
    protected $optOut        = false;
    protected $consentDescriptionId = null;

    public function __construct($data = [])
    {
        $this->setDataWithSetters($data);
    }

    /**
     * @return string
     */
    public function getConsentName()
    {
        return $this->consentName;
    }

    /**
     * @param  string  $consentName
     *
     * @return Consent
     */
    public function setConsentName($consentName)
    {
        $this->consentName = $consentName;

        return $this;
    }

    /**
     * @return bool
     */
    public function getConsentAccept()
    {
        return $this->consentAccept;
    }

    /**
     * @param  bool  $consentAccept
     *
     * @return Consent
     */
    public function setConsentAccept($consentAccept)
    {
        $this->consentAccept = $consentAccept;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAgreementDate()
    {
        return $this->agreementDate;
    }

    /**
     * @param  int  $agreementDate
     *
     * @return Consent
     */
    public function setAgreementDate($agreementDate)
    {
        $this->agreementDate = $agreementDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param  string  $ip
     *
     * @return Consent
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOptOut()
    {
        return $this->optOut;
    }

    /**
     * @param  bool  $optOut
     *
     * @return Consent
     */
    public function setOptOut($optOut)
    {
        $this->optOut = $optOut;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getConsentDescriptionId()
    {
        return $this->consentDescriptionId;
    }

    /**
     * @param  int  $consentDescriptionId
     *
     * @return Consent
     */
    public function setConsentDescriptionId($consentDescriptionId)
    {
        $this->consentDescriptionId = $consentDescriptionId;

        return $this;
    }
}
