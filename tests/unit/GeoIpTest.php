<?php

use Coyote\User;

class GeoIpTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Coyote\GeoIp
     */
    protected $geoIp;

    protected function _before()
    {
        $this->geoIp = app('GeoIp');
    }

    protected function _after()
    {
    }

    // tests
    public function testGeocodeCity()
    {
        $result = $this->geoIp->city('Wrocław');
        $this->assertGreaterThan(0, count($result));

        $first = array_first($result);
        $this->assertEquals('Wrocław', $first['name']);
    }

    public function testGeocodeAsciiName()
    {
        $result = $this->geoIp->city('Wroclaw');
        $this->assertGreaterThan(0, count($result));

        $first = array_first($result);
        $this->assertEquals('Wrocław', $first['name']);
    }

    public function testNormalizeByLocale()
    {
        $normalize = new \Coyote\GeoIp\Normalizers\Locale('pl');

        $result = $normalize->normalize($this->geoIp->city('Wroclaw'));
        $this->assertEquals('Wrocław', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Warsaw'));
        $this->assertEquals('Warszawa', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Gdansk'));
        $this->assertEquals('Gdańsk', $result['name']);
    }

    public function testGeocodeNonExistingCityAndThrowException()
    {
        $this->expectException('Guzzle\Http\Exception\ClientErrorResponseException');
        $this->geoIp->city('zyc123');
    }

    public function testGeocodeIp()
    {
        $result = $this->geoIp->ip('104.16.34.249');
        $this->assertEquals('US', $result['country_code']);
    }
}