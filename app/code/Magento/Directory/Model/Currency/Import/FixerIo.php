<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Currency\Import;

use Exception;
use Laminas\Http\Request;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\LaminasClientFactory as HttpClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\LaminasClient;

/**
 * Currency rate import model (From http://fixer.io/)
 */
class FixerIo extends AbstractImport
{
    /**
     * @var string
     */
    public const CURRENCY_CONVERTER_URL = 'https://api.apilayer.com/fixer/latest?symbols{{CURRENCY_TO}}' .
                                          '&base={{CURRENCY_FROM}}';
    
    /**
     * @var string
     */
    private const API_KEY_CONFIG_PATH = 'currency/fixerio/api_key';

    /**
     * @var HttpClientFactory
     */
    protected $httpClientFactory;

    /**
     * Core scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $currencyConverterServiceHost = '';

    /**
     * Initialize dependencies
     *
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param HttpClientFactory $httpClientFactory
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        HttpClientFactory $httpClientFactory
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @inheritdoc
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }
            $data = $this->convertBatch($data, $currencyFrom, $currencies);
            ksort($data[$currencyFrom]);
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }

    /**
     * Return currencies convert rates in batch mode
     *
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    private function convertBatch(array $data, string $currencyFrom, array $currenciesTo): array
    {
        $accessKey = $this->scopeConfig->getValue(self::API_KEY_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if (empty($accessKey)) {
            $this->_messages[] = __('No API Key was specified or an invalid API Key was specified.');
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        $currenciesStr = implode(',', $currenciesTo);
        $url = str_replace(
            ['{{CURRENCY_FROM}}', '{{CURRENCY_TO}}'],
            [$currencyFrom, $currenciesStr],
            self::CURRENCY_CONVERTER_URL
        );
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);
        try {
            $response = $this->getServiceResponse($url);
        } finally {
            ini_restore('max_execution_time');
        }

        if (!$this->validateResponse($response, $currencyFrom)) {
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        foreach ($currenciesTo as $currencyTo) {
            if ($currencyFrom == $currencyTo) {
                $data[$currencyFrom][$currencyTo] = $this->_numberFormat(1);
            } else {
                if (empty($response['rates'][$currencyTo])) {
                    $serviceHost =  $this->getServiceHost($url);
                    $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $serviceHost, $currencyTo);
                    $data[$currencyFrom][$currencyTo] = null;
                } else {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat(
                        (double)$response['rates'][$currencyTo]
                    );
                }
            }
        }
        return $data;
    }

    /**
     * Get Fixer.io service response
     *
     * @param string $url
     * @param int $retry
     * @return array
     */
    private function getServiceResponse(string $url, int $retry = 0): array
    {
        $accessKey = $this->scopeConfig->getValue(self::API_KEY_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        
        /** @var LaminasClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $httpClient->setUri($url);
            $httpClient->setOptions(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/fixerio/timeout',
                        ScopeInterface::SCOPE_STORE
                    ),
                ]
            );
            $httpClient->setMethod(Request::METHOD_GET);
            
            $httpClient->getHeaders()->addHeaders([
                'Content-Type' => 'text/plain',
                'apikey' => $accessKey,
            ]);
            
            $jsonResponse = $httpClient->send()->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }

    /**
     * Validates rates response.
     *
     * @param array $response
     * @param string $baseCurrency
     * @return bool
     */
    private function validateResponse(array $response, string $baseCurrency): bool
    {
        if ($response['success']) {
            return true;
        }

        $errorCodes = [
            101 => __('No API Key was specified or an invalid API Key was specified.'),
            102 => __('The account this API request is coming from is inactive.'),
            105 => __('The "%1" is not allowed as base currency for your subscription plan.', $baseCurrency),
            201 => __('An invalid base currency has been entered.'),
        ];

        $this->_messages[] = $errorCodes[$response['error']['code']] ?? __('Currency rates can\'t be retrieved.');

        return false;
    }

    /**
     * Creates array for provided currencies with empty rates.
     *
     * @param array $currenciesTo
     * @return array
     */
    private function makeEmptyResponse(array $currenciesTo): array
    {
        return array_fill_keys($currenciesTo, null);
    }

    /**
     * Get currency converter service host.
     *
     * @param string $url
     * @return string
     */
    private function getServiceHost(string $url): string
    {
        if (!$this->currencyConverterServiceHost) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->currencyConverterServiceHost = parse_url($url, PHP_URL_SCHEME) . '://'
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                . parse_url($url, PHP_URL_HOST);
        }
        return $this->currencyConverterServiceHost;
    }
}
