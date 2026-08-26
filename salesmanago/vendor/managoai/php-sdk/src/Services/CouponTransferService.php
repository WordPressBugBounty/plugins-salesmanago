<?php


namespace SALESmanago\Services;

use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Coupon;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\ContactModel;
use SALESmanago\Model\CouponModel;
use SALESmanago\Model\EventModel;
use SALESmanago\Model\ConfModel;

class CouponTransferService
{
    const
        REQUEST_METHOD_POST = 'POST',
        API_METHOD          = '/api/contact/addContactCoupon';

    /**
     * @var RequestService
     */
    private $RequestService;

    /**
     * @var ConfModel
     */
    private $ConfModel;

    /**
     * @param ConfigurationInterface $conf
     * @throws Exception
     */
    public function __construct(ConfigurationInterface $conf)
    {
        $this->ConfModel = new ConfModel($conf);
        $this->RequestService = new RequestService($conf);
    }

    /**
     * @param Contact $Contact
     * @param Coupon $Coupon
     * @return Response
     * @throws Exception
     */
    public function transfer(
        Contact $Contact,
        Coupon $Coupon
    ) {
        $CouponModel  = new CouponModel($Coupon);
        $settings     = $this->ConfModel->getAuthorizationApiData();

        $data = array_merge(
            $settings,
            $CouponModel->getCouponForTransfer($Contact)
        );

        $Response = $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            self::API_METHOD,
            $data
        );

        return $this->RequestService->validateCustomResponse(
            $Response,
            array(
                function () use ($Response, $Coupon)  {
                    return $Response->getField('coupon') == $Coupon->getCoupon();
                }
            )
        );
    }
}
