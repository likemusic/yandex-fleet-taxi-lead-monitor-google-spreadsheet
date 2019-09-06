<?php

namespace Likemusic\YandexFleetTaxi\LeadMonitor\GoogleSpreadsheet\Tests;

use Google_Exception;
use Likemusic\YandexFleetTaxi\LeadMonitor\GoogleSpreadsheet\Monitor;
use PHPUnit\Framework\TestCase;

class MonitorTest extends TestCase
{
    /**
     * @throws Google_Exception
     * @doesNotPerformAssertions
     */
    public function testRun()
    {
        $testMonitor = $this->getTestMonitor();
        $testMonitor->run();
    }

    /**
     * @return Monitor
     * @throws Google_Exception
     */
    private function getTestMonitor(): Monitor
    {
        $credentialsPath = 'credentials.json';
        $tokenPath = 'token.json';
        $spreadsheetId = '1baxAq-otNeyKajWcIt7yQHq1oWyVDGHp54YbwRJszIw';
        $yandexFleetLogin = 'socol-test';
        $yandexFleetPassword = 's12346';
        $parkId = '8d40b7c41af544afa0499b9d0bdf2430';

        return new Monitor($credentialsPath,$tokenPath, $spreadsheetId, $yandexFleetLogin, $yandexFleetPassword, $parkId);
    }
}
