<?php

namespace SALESmanago\Model;

use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Contact\Coupon;
use SALESmanago\Exception\Exception;
use SALESmanago\Helper\DataHelper;
use SALESmanago\Entity\ConfigurationInterface;

class CouponModel
{
    /**
     * @var Coupon
     */
    protected $Coupon;

    /**
     * @param Coupon $Coupon
     */
    public function __construct(Coupon $Coupon)
    {
        $this->Coupon = $Coupon;
    }

    /**
     * @param Contact $Contact
     * @return array
     * @throws Exception
     */
    public function getCouponForTransfer(Contact $Contact)
    {
        if (empty($Contact->getEmail()) && empty($Contact->getContactId())) {
            throw new Exception('Contact identificator not set');
        }

        $contactIdentificator = !empty($Contact->getEmail())
            ? [Contact::EMAIL => $Contact->getEmail()]
            : [Contact::C_ID => $Contact->getContactId()];

        return array_merge(self::toArray($this->Coupon), $contactIdentificator);
    }

    /**
     * @param Coupon $Coupon
     * @return array
     */
    public static function toArray(Coupon $Coupon): array
    {
        return DataHelper::filterDataArray(
            [
                Coupon::NAME   => $Coupon->getName(),
                Coupon::LENGTH => $Coupon->getLength(),
                Coupon::VALID  => $Coupon->getValid(),
                Coupon::COUPON => $Coupon->getCoupon()
            ]
        );
    }
}