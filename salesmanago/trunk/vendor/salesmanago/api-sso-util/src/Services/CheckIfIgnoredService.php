<?php


namespace SALESmanago\Services;


use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Response;

class CheckIfIgnoredService
{
    const
        IS_IGNORED = 'isIgnored',
        IGNORED_MESSAGE = "Contact was ignored";

    /**
     * @var Configuration
     */
    private $conf;

    /**
     * @var Contact
     */
    private $Contact;

    /**
     * CheckIfIgnoredService constructor.
     * @param ConfigurationInterface $conf
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @return Response
     */
    public function getDeclineResponse()
    {
        $Response = new Response();

        return $Response
            ->setStatus(false)
            ->setMessage(array(CheckIfIgnoredService::IGNORED_MESSAGE))
            ->setField('contactId', null)
            ->setField('eventId', null)
            ->setField('conf', $this->conf);
    }

    /**
     * @param Contact $Contact
     * @return bool
     */
    public function isContactIgnored(Contact $Contact)
    {
        $this->Contact = $Contact;

        if($this->checkIgnoredDomains()) {
            return true;
        }
        // <--put other functions to ignore contact here -->
        return false; //contact will not be ignored

    }

    /**
     * Checker for IgnoredDomains.
     * @param null
     * @return bool Returns true if contact should be ignored
     */
    protected function checkIgnoredDomains()
    {
        if (!empty($this->conf->getIgnoredDomains())
            && is_array($this->conf->getIgnoredDomains())
            && !empty($this->Contact->getEmail())) {
            $emailDomain = explode('@', $this->Contact->getEmail())[1];
            return in_array($emailDomain, $this->conf->getIgnoredDomains());
        }
        return false; //no reason to ignore Contact
    }
}
