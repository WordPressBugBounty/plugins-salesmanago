<?php

namespace bhr\Frontend\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Frontend\Controller\TransferController;
use bhr\Frontend\Model\Settings as SettingsModel;

use bhr\Frontend\Plugins\Wp\WpController;
use bhr\Frontend\Plugins\Wc\WcController;
use bhr\Frontend\Plugins\Cf7\Cf7Controller;
use bhr\Frontend\Plugins\Gf\GfController;
use bhr\Frontend\Plugins\Ff\FfController;
use Error;

class LeadooHooksModel
{

    /**
     * @var SettingsModel
     */
	private $SettingsModel;

    /**
     * LeadooHooksModel constructor.
     *
     * @param SettingsModel $SettingsModel
     */
    public function __construct(SettingsModel $SettingsModel)
    {
        $this->SettingsModel = $SettingsModel;

        if (!empty($this->SettingsModel->getConfiguration()->getLeadooScript())) {
            Helper::addAction("wp_head", array($this, "addLeadooCode"));
        }
    }

    /**
     * @return void
     */
    public function addLeadooCode()
    {
        print $this->SettingsModel->getConfiguration()->getLeadooScript();
    }
}
