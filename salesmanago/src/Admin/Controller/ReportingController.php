<?php

namespace bhr\Admin\Controller;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Model\AdminModel;

use SALESmanago\Controller\ContactAndEventTransferController;
use bhr\Admin\Model\ReportingModel;
use bhr\Admin\Entity\Configuration;

class ReportingController
{
    const
        ACTION_SETTINGS_SAVED  = 'settings',
        ACTION_USER_LOGIN      = 'login',
        ACTION_USER_LOGOUT     = 'logout',
        ACTION_EXPORT          = 'export',
        ACTION_PLUGIN_UPDATE   = 'update',

        LOGIN_EVENT            = 'LOGIN',
        LOGOUT_EVENT           = 'RETURN',
        PLUGIN_UPDATE          = 'DOWNLOAD',
        SETTINGS_SAVED         = 'SURVEY',
        EXPORT                 = 'PACKED';

    private $AdminModel;
    private $ReportingModel;
    private $vendorEndpoint;

    public function __construct(AdminModel $AdminModel)
    {
        $this->AdminModel = $AdminModel;
        $this->ReportingModel = new ReportingModel();
        return $this;
    }

    /**
     * @param $action
     * @param $additionalData1
     * @param $additionalData2
     */
    public function reportUserAction($action, $additionalData1 = null, $additionalData2 = null)
    {
        try {
            $this->vendorEndpoint = $this->AdminModel->getConfiguration()->getEndpoint();

            $this->AdminModel->getConfiguration()->setEndpoint(ReportingModel::REPORTING_ENDPOINT);
            $CaETC = new ContactAndEventTransferController($this->AdminModel->getConfiguration());

            $this->ReportingModel->buildContact($this->vendorEndpoint, Configuration::getInstance());

            switch ($action) {
                case self::ACTION_USER_LOGIN:
                    $this->ReportingModel->buildEvent(self::LOGIN_EVENT);
                    break;
                case self::ACTION_USER_LOGOUT:
                    $this->ReportingModel->buildEvent(self::LOGOUT_EVENT);
                    break;
                case self::ACTION_SETTINGS_SAVED:
                    $userSettings = json_encode($this->AdminModel->getPlatformSettings()) . "\n" . json_encode($this->AdminModel->getConfiguration());
                    $this->ReportingModel->buildEvent(self::SETTINGS_SAVED, $userSettings);
                    break;
                case self::ACTION_EXPORT:
                    $this->ReportingModel->buildEvent(self::EXPORT, $additionalData1);
                    break;
                case self::ACTION_PLUGIN_UPDATE:
                    $this->ReportingModel->buildEvent(self::SETTINGS_SAVED, '', $additionalData1, $additionalData2);
                    break;
            }

            $CaETC->transferBoth($this->ReportingModel->getContact(), $this->ReportingModel->getEvent());
            $this->AdminModel->getConfiguration()->setEndpoint($this->vendorEndpoint);
        } catch (\Exception $e) {
            $this->AdminModel->getConfiguration()->setEndpoint($this->vendorEndpoint);
        }
    }
}
