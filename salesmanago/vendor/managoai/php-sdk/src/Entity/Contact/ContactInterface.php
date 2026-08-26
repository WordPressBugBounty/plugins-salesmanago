<?php

namespace SALESmanago\Entity\Contact;

interface ContactInterface
{
    /**
     * @return string|null - smclient/contactId
     */
    public function getContactId(): ?string;

    /**
     * @return string|null - email
     */
    public function getEmail(): ?string;
}