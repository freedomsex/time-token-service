<?php

namespace FreedomSex\Tests\Services;

use FreedomSex\Services\TimeTokenService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class TimeTokenServiceTest extends TestCase
{
    /**
     * @var TimeTokenService
     */
    public $timeTokenService;

    public function setUp()
    {
        $this->memory = new FilesystemAdapter();
        $this->timeTokenService = new TimeTokenService($this->memory);
    }

    public function testCreate()
    {
        $id = '12345';
        $delay = $this->timeTokenService->start($id);
        $this->assertEquals(TimeTokenService::DEFAULT_DELAY, $delay);
    }

    public function testExpires()
    {
        $time = '2';
        $expect = $this->timeTokenService->expires($time, 3);
        $this->assertEquals($expect, 5);
    }

    public function testDelay()
    {
        $default = 10;
        $token = new TimeTokenService($this->memory, $default);
        $this->assertEquals($default, $token->delay());
        $this->assertEquals(5, $token->delay(5));
        $this->assertEquals(TimeTokenService::DEFAULT_DELAY, $this->timeTokenService->delay());
    }

    public function testStart()
    {
        $id = '54321';
        $delay = $this->timeTokenService->start($id);
        $expect = TimeTokenService::DEFAULT_DELAY + time();
        $this->assertEquals($expect, $this->timeTokenService->expect($id));
    }

    public function testRestore()
    {
        $id = '54321';
        $delay = $this->timeTokenService->start($id, 5);
        $expect = TimeTokenService::DEFAULT_DELAY + time();
        $this->assertEquals(5, $this->timeTokenService->restore($id));
        $this->assertEquals(TimeTokenService::DEFAULT_DELAY, $this->timeTokenService->restore('8523'));
    }

    public function testReady()
    {
        $id = '12345';
        $this->timeTokenService->start($id);
        $this->assertFalse($this->timeTokenService->ready($id));
        $this->assertNull($this->timeTokenService->ready('8523'));
    }

    public function testLeft()
    {
        $id = '12345';
        $delay = $this->timeTokenService->start($id);
        $this->assertEquals(-2, $this->timeTokenService->left($id));
        sleep(2);
        $this->assertEquals(0, $this->timeTokenService->left($id));
        sleep(1);
        $this->assertEquals(1, $this->timeTokenService->left($id));
    }

    public function testTimer()
    {
        $id = '12345';
        $delay = $this->timeTokenService->start($id, 2);
        $this->assertFalse($this->timeTokenService->ready($id));
        sleep(1);
        $this->assertFalse($this->timeTokenService->ready($id));
        sleep(1);
        $this->assertTrue($this->timeTokenService->ready($id));
        sleep(1);
        $this->assertTrue($this->timeTokenService->ready($id));
    }

}
