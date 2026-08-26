<?php

namespace SALESmanago\Services\Api\V3;

use \Exception;
use SALESmanago\Entity\RequestClientConfigurationInterface;
use SALESmanago\Exception\ApiV3Exception;
use SALESmanago\Helper\ConnectionClients\cURLClient;

class RequestService
{
    private $connectionClient;

    public function __construct(
        RequestClientConfigurationInterface $cUrlClientConfiguration
    ) {
        $this->connectionClient = new cURLClient();
        $this->connectionClient->setConfiguration($cUrlClientConfiguration);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $data
     * @throws ApiV3Exception
     */
    final public function request($method, $uri, $data = null)
    {
        try {
            $this->connectionClient
                ->setType($method)
                ->setEndpoint($uri);

            $this->connectionClient->request($data);

            $response = $this->connectionClient->responseJsonDecode();

            if (!empty($response['messages'])
                && !empty($response['reasonCode'])
                && ($response['reasonCode'] != 12)
            ) {
                $messages = is_array($response['messages'])
                    ? implode(', ', $response['messages'])
                    : $response['messages'];

                throw (new ApiV3Exception($messages, $response['reasonCode']))
                    ->setCodes([$response['reasonCode']])
                    ->setMessages(is_array($response['messages'])
                        ? $response['messages']
                        : [$response['messages']]
                    );
            }

            if (isset($response['problems'])) {
                $messages = [];
                $codes    = [];
                $problems = [];

                foreach ($response['problems'] as $problem) {
                    if ($problem['reasonCode'] == 12) {
                        continue;
                    }

                    $codes[] = $problem['reasonCode'];
                    $problems[] = $problem['message'];
                    $messages[] = 'reasonCode: ' . $problem['reasonCode'] . ' - message: ' . $problem['message'];
                }

                $messages = implode('; ', $messages);

                if (!empty($messages)) {
                    throw (new ApiV3Exception($messages, 400))
                        ->setCodes($codes)
                        ->setMessages($problems)
                        ->setRequiredFields();
                }
            }

            return $response;
        } catch (Exception $e) {
            if ($e instanceof ApiV3Exception) {
                throw $e;
            }
            throw (new ApiV3Exception($e->getMessage(), $e->getCode()))
                ->setCodes([$e->getCode()])
                ->setMessages([$e->getMessage()]);
        }
    }

    /**
     * Sends multiple requests in parallel using curl_multi_* functions and processes the responses
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @throws ApiV3Exception
     */
    final public function requestCurlMulti($method, $uri, $data = null)
    {
        try {
            $this->connectionClient
                ->setType($method)
                ->setEndpoint($uri);

            $this->connectionClient->requestCurlMulti($data);

            $responses = $this->connectionClient->getResponses();

            $mergedResponses = $this->mergeResponsesByCatalogId($responses, $data);

            $messages = [];
            $codes    = [];
            $problems = [];

            foreach ($mergedResponses as $catalogId => $response) {
                if (isset($response['response']['problems'])) {
                    foreach ($response['response']['problems'] as $problem) {
                        if ($problem['reasonCode'] == 12) {
                            continue;
                        }
                        $codes[] = $problem['reasonCode'];
                        $problems[] = $problem['message'];
                        $messages[] = 'catalogId: ' . $catalogId . ' - reasonCode: ' . $problem['reasonCode'] . ' - message: ' . $problem['message'];
                    }
                }
            }

            if (!empty($messages)) {
                $messagesAsString = implode('; ', $messages);

                throw (new ApiV3Exception($messagesAsString, 400))
                    ->setCodes($codes)
                    ->setMessages($problems)
                    ->setRequiredFields();
            }

            return $mergedResponses;
        } catch (Exception $e) {
            if ($e instanceof ApiV3Exception) {
                throw $e;
            }
            throw (new ApiV3Exception($e->getMessage(), $e->getCode()))
                ->setCodes([$e->getCode()])
                ->setMessages([$e->getMessage()]);
        }
    }

    /**
     * Merges responses by catalogId
     *
     * @param array $responses
     * @param array $originalData
     * @return array
     */
    private function mergeResponsesByCatalogId(array $responses, array $originalData): array
    {
        $merged = [];

        foreach ($responses as $index => $response) {
            // Extract catalogId from original request data
            $catalogId = $originalData[$index]['catalogId'] ?? $index;

            if (!isset($merged[$catalogId])) {
                // First occurrence of this catalogId
                $merged[$catalogId] = [
                    'response' => $response
                ];
            } else {
                if (isset($response['problems']) && isset($merged[$catalogId]['response']['problems'])) {
                    $merged[$catalogId]['response']['problems'] = array_merge(
                        $merged[$catalogId]['response']['problems'],
                        $response['problems']
                    );
                } elseif (isset($response['problems'])) {
                    $merged[$catalogId]['response']['problems'] = $response['problems'];
                }

                // Merge other response fields
                foreach ($response as $key => $value) {
                    if ($key == 'problems') {
                        continue;
                    }
                    if (isset($merged[$catalogId]['response'][$key])) {
                        // Handle merging based on data type
                        if (is_array($value) && is_array($merged[$catalogId]['response'][$key])) {
                            $merged[$catalogId]['response'][$key] = array_merge(
                                $merged[$catalogId]['response'][$key],
                                $value
                            );
                        }
                        // For non-array values, keep the existing value
                    } else {
                        $merged[$catalogId]['response'][$key] = $value;
                    }
                }

            }
        }

        return $merged;
    }
}
