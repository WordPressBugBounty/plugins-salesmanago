<?php


namespace SALESmanago\Adapter;


use SALESmanago\Entity\Contact\Contact;

interface ContactStatusSynchronizationManagerAdapter
{
    /**
     * Subscribe Customer to the platform newsletter;
     * @param Contact $Contact
     * @return bool
     */
    public function subscribe(Contact $Contact);
}