<?php

namespace bhr\Admin\Controller;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Model\AdminModel;
use bhr\Admin\Entity\MessageEntity;

use bhr\Admin\Entity\Configuration;
use SALESmanago\Entity\User;
use SALESmanago\Exception\Exception;
use \SALESmanago\Controller\LoginController as SMLoginController;
use SALESmanago\Services\UserAccountService;
use SALESmanago\Controller\User\Vendor\AccountController;

class LoginController
{
    private $AdminModel;

    public function __construct(AdminModel $AdminModel)
    {
        $this->AdminModel = $AdminModel;
    }

    /**
     * @param array $request
     */
    public function loginUser($request)
    {
        try {
            $User = new User();
            $User
                ->setEmail($request['username'])
                ->setPass($request['password']);

            $Configuration = Configuration::getInstance();
            if (isset($request['salesmanago-endpoint']) && !empty($request['salesmanago-endpoint'])) {
                $Configuration->setEndpoint($request['salesmanago-endpoint']);
            }
		} catch (Exception $e) {
			MessageEntity::getInstance()->addException($e);
        } catch (\Exception $e) {
            MessageEntity::getInstance()->addException(new Exception($e->getMessage(), 103));
        }
        try {
			$AccountController = new AccountController($Configuration, null);
			$Response = $AccountController->login($User);
        } catch (Exception $e) {
            MessageEntity::getInstance()->addException($e);
        } catch (\Exception $e) {
            MessageEntity::getInstance()->addException(new Exception($e->getMessage(), 104));
        }
        if (!empty($Response) && $Response->getStatus() == true) {
            $this->AdminModel->saveConfiguration();

            try {
                $this->updateOwnersAfterLogin();
            } catch (\Exception $e) {
                error_log('Could not update owners after login');
                error_log($e->getMessage());
            }

            try {
                $ReportingController = new ReportingController($this->AdminModel);
                $ReportingController->reportUserAction(ReportingController::ACTION_USER_LOGIN);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
            MessageEntity::getInstance()->addMessage(__('Logged in.', 'salesmanago'), 'success', 701);
        } elseif (!empty($Response)) {
            MessageEntity::getInstance()->addMessage($Response->getMessage(), 'error', 105);
        }
    }

    /**
     *
     */
    public function logoutUser()
    {
        try {
            $ReportingController = new ReportingController($this->AdminModel);
            $ReportingController->reportUserAction(ReportingController::ACTION_USER_LOGOUT);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

		try {
			$AccountController = new AccountController(Configuration::getInstance(), null);
			$AccountController->logout();
		} catch (\Exception $e) {
			error_log($e->getMessage());
		}

        try {
            $this->AdminModel->removeSettingsOnLogout();
            $_COOKIE['sm_logged_in'] = null;
            unset($_COOKIE['sm_logged_in']);
        } catch (\Exception $e) {
            MessageEntity::getInstance()->addException(new Exception($e->getMessage(), 151));
            header('Location: admin.php?page=' . SALESMANAGO . '&message=logout-error');
            return;
        }
        header('Location: admin.php?page=' . SALESMANAGO . '&message=logout');
    }

    /**
     * Update owners in platform settings after logging in.
     *
     * @return void
     */
    private function updateOwnersAfterLogin()
    {
        $ownersInConfiguration = $this->AdminModel->getConfiguration()->getOwnersList();

        $defaultOwner = $ownersInConfiguration[0];

        foreach (SUPPORTED_PLUGINS as $plugin) {
            if (!in_array($this->AdminModel->getPlatformSettings()->getPluginByName($plugin)->getOwner(), $ownersInConfiguration))
            {
                $this->AdminModel->getPlatformSettings()->getPluginByName($plugin)->setOwner($defaultOwner);

                $forms = $this->AdminModel->getPlatformSettings()->getPluginByName($plugin)->getForms();
                forEach($forms as &$form) {
                    $form['owner'] = $defaultOwner;
                }
                $this->AdminModel->getPlatformSettings()->getPluginByName($plugin)->setForms($forms);
            }
        }

        if (!in_array($this->AdminModel->getPlatformSettings()->getMonitCode()->getOwner(), $ownersInConfiguration)) {
            $this->AdminModel->getPlatformSettings()->getMonitCode()->setOwner($defaultOwner);

        }
        $this->AdminModel->savePlatformSettings();
    }
}
