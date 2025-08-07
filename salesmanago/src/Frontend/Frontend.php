<?php

namespace bhr\Frontend;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\HooksModel;
use bhr\Frontend\Model\RestApiModel;
use bhr\Frontend\Model\Settings as SettingsModel;
use bhr\Frontend\Model\LeadooHooksModel;

class Frontend
{
    protected $HooksModel;
    protected $SettingsModel;

    public function __construct()
    {
        $this->SettingsModel = new SettingsModel();

        if($this->SettingsModel == false) {
            return;
        }

        $this->HooksModel = new RestApiModel($this->SettingsModel);

        //SALESmanago integration hooks initialization:
        if ($this->SettingsModel->isUserAuthorized()) {
            $this->HooksModel = new HooksModel($this->SettingsModel);
        }

        //Leadoo integration hooks initialization:
        if ($this->SettingsModel->getConfiguration()->getLeadooScript()) {
            $this->HooksModel = new LeadooHooksModel($this->SettingsModel);
        }
    }
}