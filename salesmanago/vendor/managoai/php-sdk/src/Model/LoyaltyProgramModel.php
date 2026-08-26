<?php

namespace SALESmanago\Model;

use SALESmanago\Entity\LoyaltyProgram;

class LoyaltyProgramModel
{
    /* Examples of addresseeType field value according to  http://docs.salesmanago.com/#api-loyalty-program */
    const
        EMAIL                = 'email',
        TAG                  = 'tag',
        CONTACT_ID           = 'contact_id',

        VALUE                = 'value',
        POINTS               = 'points',
        COMMENT              = 'comment',
        ADDRESSEE_TYPE       = 'addresseeType',
        MODIFICATION_TYPE    = 'modificationType',
        LOYALTY_PROGRAM_NAME = 'loyaltyProgram';

    /**
     * @var LoyaltyProgram
     */
    private $LoyaltyProgram;

    /**
     * LoyaltyProgramModel constructor.
     *
     * @param LoyaltyProgram $LoyaltyProgram
     */
    public function __construct(LoyaltyProgram $LoyaltyProgram)
    {
        $this->LoyaltyProgram = $LoyaltyProgram;
    }

    /**
     * @return array
     */
    public function getDataForRequest()
    {
        $result = [];

        switch ($this->LoyaltyProgram->getAddresseType()) {
            case self::EMAIL:
                $result = [
                    self::ADDRESSEE_TYPE => self::EMAIL,
                    self::VALUE          => $this->LoyaltyProgram->getValue()
                ];
                break;
            case self::TAG:
                $result = [
                    self::ADDRESSEE_TYPE => self::TAG,
                    self::VALUE          => $this->LoyaltyProgram->getValue()
                ];
                break;
            case self::CONTACT_ID:
                $result = [
                    self::ADDRESSEE_TYPE => self::CONTACT_ID,
                    self::VALUE          => $this->LoyaltyProgram->getValue()
                ];
                break;
        }
        return $result;
    }

    public function getDataForModifyRequest()
    {
        return [
            self::POINTS            => $this->LoyaltyProgram->getPoints(),
            self::COMMENT           => $this->LoyaltyProgram->getComment(),
            self::MODIFICATION_TYPE => $this->LoyaltyProgram->getModificationType()
        ];
    }
}