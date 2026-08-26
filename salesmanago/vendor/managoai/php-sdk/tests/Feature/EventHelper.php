<?php

namespace Tests\Feature;

use _PHPStan_0a43b4828\Nette\Utils\DateTime;
use Faker;
use SALESmanago\Entity\Event\Event;
use SALESmanago\Model\Collections\EventsCollection;
use SALESmanago\Exception\Exception;

class EventHelper
{
    const
        TYPE_PURCHASE = 'PURCHASE',
        TYPE_CART = 'CART';

    /**
     * @var Faker\Factory
     */
    protected $faker;

    /**
     * @var DateTime
     */
    protected $dateTime;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
        $this->dateTime = new DateTime();
    }

    /**
     * Generates events collection
     *
     * @param string $email
     * @param int $nbOfItems
     * @return EventsCollection
     * @throws Exception
     */
    public function generateCollection($email, $nbOfItems = 500)
    {
        $collection = new EventsCollection();

        while ($nbOfItems) {
            $collection->addItem($this->generate($email));
            $nbOfItems--;
        }

        return $collection;
    }

    /**
     * Generate event
     *
     * @return Event
     * @throws Exception
     */
    public function generate($forEmail = null)
    {
        $prodsDetails = $this->generateProductDetails();

        return (new Event())
            ->setEmail($forEmail)
            ->setDate($this->dateTime->format('c'))
            ->setLocation('sm_' . $this->faker->sha1)
            ->setValue($this->faker->randomFloat(2))
            ->setExternalId($this->faker->uuid)
            ->setShopDomain($this->faker->domainName)
            ->setContactExtEventType('PURCHASE')
            ->setDescription($this->faker->text(200))
            ->setForceOptIn(false)
            ->setProducts($prodsDetails['skus'])
            ->setDetail(implode(",", $prodsDetails['names']), 1)
            ->setDetail(implode("/", $prodsDetails['qtys']), 2)
            ->setDetail(implode(",", $prodsDetails['ids']), 3)
            ->setDetail($this->faker->currencyCode, 4)
            ->setDetail($this->faker->word, 5)
            ->setDetail($this->faker->creditCardType, 6)
            ->setDetail($this->faker->countryCode, 7)
            ->setDetail($this->generateCategoryIds(), 8)
            ->setDetail($this->faker->locale, 9)
            ->setDetail(json_encode($this->generateMeta()), 10)
            ->setDetail($this->faker->sha1, 11)
            ->setDetail($this->faker->randomFloat(2), 12)
            ->setDetail($this->faker->word, 13)
            ->setDetail(
                (!empty($prodsDetails['freeProdsIds']))
                    ? implode(',', $prodsDetails['freeProdsIds'])
                    : '',
                14
            );
    }

    /**
     * @return string
     */
    protected function generateCategoryIds()
    {
        $countCategories = $this->faker->randomNumber(1);

        $categoriesIdsArr = $this->generateItems(
            $countCategories,
            function () {return $this->faker->randomNumber();}
        );

        return implode(',', $categoriesIdsArr);
    }

    /**
     * Generates meta
     *
     * @return array[]
     */
    protected function generateMeta()
    {
        return [
            'metadata' => [
                'from'         => $this->faker->word,
                'timeOfAction' => time()
            ]
        ];
    }

    /**
     * Generate basic product details
     *
     * @return array
     */
    protected function generateProductDetails()
    {
        $nbOfProducts = $this->faker->randomNumber(1);

        return [
            'ids' => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->randomNumber();}
            ),
            'skus' => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->word . $this->faker->randomNumber();}
            ),
            'names' => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->word;}
            ),
            'qtys' => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->randomNumber(2);}
            ),
            'freeProdsIds'  => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->randomNumber(2);}
            ),
            'freeProdsSkus' => $this->generateItems(
                $nbOfProducts,
                function () {return $this->faker->word . $this->faker->randomNumber();}
            )
        ];
    }

    /**
     * Generates details for given product numbers
     *
     * @param int $nbOfItems
     * @param callable $callback
     * @return array
     */
    protected function generateItems($nbOfItems, callable $callback)
    {
        $items = [];
        while ($nbOfItems) {
            $items[] = $callback();
            $nbOfItems--;
        }
        return $items;
    }
}