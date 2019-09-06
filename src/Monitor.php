<?php
namespace Likemusic\YandexFleetTaxi\LeadMonitor\GoogleSpreadsheet;

use Google_Exception;
use Google_Service_Sheets;
use Http\Client\Curl\Client as CurlClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Likemusic\YandexFleetTaxi\LeadMonitor\Monitor as BaseMonitor;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\Converter\RowToLead as RowToLeadConverter;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\Converter\RowToLead\RowToCarPostData as RowToCarPostDataConverter;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\Converter\RowToLead\RowToDriverPostData as RowToDriverPostDataConverter;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\Google\AuthorizedClient as GoogleAuthorizedClient;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\GoogleSheetClient;
use Likemusic\YandexFleetTaxi\LeadRepository\GoogleSpreadsheet\LeadRepository as GoogleSpreadsheetLeadRepository;
use Likemusic\YandexFleetTaxiClient\Client as YandexFleetTaxiClient;
use Likemusic\YandexFleetTaxiClient\PageParser\FleetTaxiYandexRu\Index as DashboardPageParser;
use Likemusic\YandexFleetTaxiClient\PageParser\PassportYandexRu\Auth\Welcome as WelcomePageParser;


class Monitor extends BaseMonitor
{
    /**
     * Monitor constructor.
     * @param string $credentialsPath
     * @param string $tokenPath
     * @param string $spreadsheetId
     * @param string $yandexFleetLogin
     * @param string $yandexFleetPassword
     * @param string $parkId
     * @throws Google_Exception
     */
    public function __construct(string $credentialsPath, string $tokenPath, string $spreadsheetId, string $yandexFleetLogin, string $yandexFleetPassword, string $parkId)
    {
        $googleAuthorizedClient = new GoogleAuthorizedClient($credentialsPath, $tokenPath);
        $googleServiceSheets = new Google_Service_Sheets($googleAuthorizedClient);
        $googleSheetClient = new GoogleSheetClient($googleServiceSheets);

        $rowToDriverPostDataConverter = new RowToDriverPostDataConverter();
        $rowToCarPostDataConverter = new RowToCarPostDataConverter();

        $rowToLeadConverter = new RowToLeadConverter($rowToDriverPostDataConverter, $rowToCarPostDataConverter);
        $leadRepository = new GoogleSpreadsheetLeadRepository($googleSheetClient, $spreadsheetId, $rowToLeadConverter, $parkId);


        $options = [
            CURLOPT_PROXY => 'host.docker.internal:8888',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];

        $httpClient = new CurlClient(null, null, $options);
        $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $welcomePageParser = new WelcomePageParser();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $dashboardPageParser = new DashboardPageParser();

        $yandexFleetTaxiClient = new YandexFleetTaxiClient(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $welcomePageParser,
            $dashboardPageParser
        );

        parent::__construct($leadRepository, $yandexFleetTaxiClient, $yandexFleetLogin, $yandexFleetPassword, $parkId);
    }
}
