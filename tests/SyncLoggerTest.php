<?php

namespace Craftix\Tests;

use Craftix\Logger\Logger;
use PHPUnit\Framework\TestCase;

class SyncLoggerTest extends TestCase
{
    /** Initial simple test */
    public function testItLogsInfoMessageDirectly()
    {
        $logger = new Logger('TestService', ['enableBuffer' => false]);
        $logger::$printers = [];
        $result = $logger->info('This is a test info message');
        $this->assertTrue($result);
    }
}