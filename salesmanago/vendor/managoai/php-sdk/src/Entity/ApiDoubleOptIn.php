<?php


namespace SALESmanago\Entity;


use SALESmanago\Entity\AbstractEntity;

class ApiDoubleOptIn extends AbstractEntity
{
    const
        U_API_D_OPT_IN        = 'useApiDoubleOptIn',
        D_OPT_IN_TEMPLATE_ID  = 'apiDoubleOptInEmailTemplateId',
        D_OPT_IN_EMAIL_ACC_ID = 'apiDoubleOptInEmailAccountId',
        D_OPT_IN_EMAIL_SUBJ   = 'apiDoubleOptInEmailSubject',
        D_OPT_IN_EMAIL_ID     = 'doubleOptInEmailId',
        D_OPT_IN_LANG         = 'doubleOptInLanguage';

    /**
     * @var bool
     */
    private $enabled    = false;

    /**
     * @deprecated since 3.1.7
     * @see self::$emailId
     * @var null|string
     */
    private $templateId = null;

    /**
     * @deprecated since 3.1.7
     * @see self::$emailId
     * @var null|string
     */
    private $accountId  = null;

    /**
     * @deprecated since 3.1.7
     * @see self::$emailId
     * @var null|string
     */
    private $subject    = null;

    /**
     * @var string
     */
    protected $emailId = null;

    /**
     * @var string
     */
    protected $lang = null;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->setDataWithSetters($data);
        }
    }

    public function set($data)
    {
        $this->setDataWithSetters($data);
        return $this;
    }

    /**
     * @return ApiDoubleOptIn
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return ApiDoubleOptIn
     */
    public function setEmailId($emailId)
    {
        $this->emailId = $emailId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailId()
    {
        return $this->emailId;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setEnabled($param)
    {
        $this->enabled = $param;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::setEmailId()
     * @param $param
     * @return $this
     */
    public function setTemplateId($param)
    {
        $this->templateId = $param;
        return $this;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::getEmailId()
     * @return string|null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::setEmailId()
     * @param $param
     * @return $this
     */
    public function setAccountId($param)
    {
        $this->accountId = $param;
        return $this;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::getEmailId()
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::setEmailId()
     * @param $param
     * @return $this;
     */
    public function setSubject($param)
    {
        $this->subject = $param;
        return $this;
    }

    /**
     * @deprecated since 3.1.7
     * @see self::getEmailId()
     * @return string|null
     */
    public function getSubject(){
        return $this->subject;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (!empty($this->emailId)) {
            return array(
                'enabled'  => $this->getEnabled(),
                'emailId'  => $this->getEmailId(),
                'language' => $this->getLang()
            );
        }

        return array(
            'enabled'    => $this->getEnabled(),
            'templateId' => $this->getTemplateId(),
            'accountId'  => $this->getAccountId(),
            'subject'    => $this->getSubject()
        );
    }
}