<?php

namespace bhr\Frontend\Model;

use bhr\Admin\Model\AdminModel;
use bhr\Admin\Entity\Configuration;

if(!defined('ABSPATH')) exit;


class MonitCodeModel
{
    public static function getMonitCode($clientId, $endpoint, $smApp, $flags = array(), $context = 'frontend')
    {

        if (!empty($clientId) && !empty($endpoint) && is_array($flags)) {
            $smCustomString  = !empty($flags['smcustom'])
                ? "\nvar _smcustom  = true;"
                : null;
            $smBannersString = !empty($flags['smbanners'])
                ? "\nvar _smbanners = true;"
                : null;
            $smCtlString     = AdminModel::isDefaultContactCookieLifetime()
                ? null
                : "\nvar _smclt = " . (int) (Configuration::getInstance()->getContactCookieTtl() / (60 * 60 *24)) . ";";

            if (!$flags['disabled']) {
                $code = "<script>";
                $code .= "var _smid ='{$clientId}';";
                $code .= $smApp ? "var _smapp =" . $smApp . "; " : '';
                $code .= "{$smCustomString}{$smBannersString}{$smCtlString}";
                $code .= "
(function(w, r, a, sm, s ) {
w['SalesmanagoObject'] = r;
w[r] = w[r] || function () {( w[r].q = w[r].q || [] ).push(arguments)};
sm = document.createElement('script');
sm.type = 'text/javascript'; sm.async = true; sm.src = a;
s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(sm, s);
})(window, 'sm', '{$endpoint}/static/sm.js');</script>\n";

                if ($flags['popUpJs']) {
                    $code .= "<script src='{$endpoint}/dynamic/{$clientId}/popups.js'></script>";
                }
				Helper::doAction('salesmanago_edit_monitcode_enable', array('Code' => &$code));
            }
            else {
                $code = '';
	            Helper::doAction('salesmanago_edit_monitcode_disable', array('Code' => &$code));
            }

            return
                $context !== 'admin'
                    ? trim(preg_replace('/\s+/', ' ', $code))
                    : $code;
        }
        else {
            return
                $context !== 'admin'
                    ? '<script> console.log("Error when trying to build monitcode")</script>'
                    : "Error when trying to build monitcode";
        }
    }
}
