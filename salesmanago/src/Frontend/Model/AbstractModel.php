<?php

namespace bhr\Frontend\Model;

if(!defined('ABSPATH')) exit;

class AbstractModel
{
    const
        CONFIGURATION       = 'salesmanago_configuration',
        PLATFORM_SETTINGS   = 'salesmanago_platform_settings',
	    OPT_IN_EMAIL        = 'sm-optin-email',
	    OPT_IN_MOBILE       = 'sm-optin-mobile',
        EMAIL               = 'email',
        SM_CLIENT           = 'smclient',
        SM_EVENT            = 'smevent',

        TAGS                = 'tags',
        TAGS_NEWSLETTER     = 'newsletter',
        TAGS_REGISTRATION   = 'registration',
        TAGS_LOGIN          = 'login',
        TAGS_PURCHASE       = 'purchase',
        TAGS_GUEST_PURCHASE = 'guestPurchase',

        MAX_STRING_LENGTH   = 255,
        COOKIE_TTL_10YRS    = 315576000;

    protected $db;

    public function __construct()
    {
        $this->db = $GLOBALS['wpdb'];
    }

    public function getTokenExpiresAt()
    {
        $now = date("Y-m-d H:i:s", time());
        return strtotime("$now +29 Days + 20 Hours");
    }
}
