<?php


namespace SALESmanago\Services;


use SALESmanago\Entity\ApiDoubleOptIn;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\Response;
use SALESmanago\Exception\Exception;
use SALESmanago\Model\Collections\Collection;
use SALESmanago\Model\Collections\ContactsCollection;
use SALESmanago\Model\Collections\EventsCollection;
use SALESmanago\Model\ConfModel;

class ExportService
{
    const
        REQUEST_METHOD_POST         = 'POST',
        API_METHOD_EXPORT_EVENTS    = '/api/contact/batchAddContactExtEvent',//@deprecated
        API_V2_METHOD_EXPORT_EVENTS = '/api/v2/contact/addBatchContactExtEvent',
        API_METHOD_EXPORT_CONTACTS  = '/api/contact/batchupsertv2',
        INTERNAL_INTEGRATION        = 'internalIntegration';

    private $RequestService;

    /**
     * @var ConfigurationInterface
     */
    private $conf;

    /**
     * @var ConfModel
     */
    private $ConfModel;

    public function __construct(ConfigurationInterface $conf)
    {
        $this->conf = $conf;
        $this->ConfModel = new ConfModel($conf);
        $this->RequestService = new RequestService($conf);
    }

    /**
     * @param Collection $collection
     * @return Response
     * @throws Exception
     */
    public function export(Collection $collection)
    {
        $internalIntegrationParams = [];

        switch(get_class($collection)) {
            case ContactsCollection::class:
                $endpoint = self::API_METHOD_EXPORT_CONTACTS;

                $internalIntegrationParams = array(
                    ApiDoubleOptIn::U_API_D_OPT_IN => false,
                    self::INTERNAL_INTEGRATION => true
                );
                break;
            case EventsCollection::class:
                $endpoint = self::API_V2_METHOD_EXPORT_EVENTS;
                break;
            default:
                throw new Exception(get_class($collection) . 'can\'t be exported', 301);
        }

        $settings = $this->ConfModel->getAuthorizationApiDataWithOwner();

        try {
            $collectionArray = $collection->toArray();
        } catch (\Exception $e) {
            throw new Exception('Collection to array conversion failed: ' . $e->getMessage(), 302);
        }

        $data = array_merge($settings, $collectionArray, $internalIntegrationParams);

        return $this->RequestService->request(
            self::REQUEST_METHOD_POST,
            $endpoint,
            $data
        );
    }

}