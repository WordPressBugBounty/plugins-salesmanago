<?php

namespace bhr\Admin\Model;

if(!defined('ABSPATH')) exit;

class AbstractModel
{
    const
        SETTINGS_TABLE    = 'salesmanago_settings',
        CONFIGURATION     = 'salesmanago_configuration',
        PLATFORM_SETTINGS = 'salesmanago_platform_settings',
        CRON_CONFIGURATION = 'salesmanago_cron',
        TOKEN_EXPIRE_TIME = 2577600, //time in seconds added after token is retrieved. 29 days 20 h
        EXPORT_TAGS       = 'WP_EXPORT';

    protected $db;

    public function __construct()
    {
        $this->db = $GLOBALS['wpdb'];
    }

    /**
     * @return false|int
     */
    public function getTokenExpiresAt()
    {
        $now = date("Y-m-d H:i:s", time());
        return strtotime("$now +29 Days + 20 Hours");
    }
}
