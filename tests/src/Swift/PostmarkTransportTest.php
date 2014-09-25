<?php

namespace Openbuildings\Postmark\Test;

use Openbuildings\Postmark\Swift_PostmarkTransport;
use PHPUnit_Framework_TestCase;
use Swift_DependencyContainer;

/**
 * @group   swift.postmark-transport
 */
class Swift_PostmarkTransportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Openbuildings\Postmark\Swift_PostmarkTransport::newInstance
     */
    public function test_newInstance()
    {
        $postmarkTransport = Swift_PostmarkTransport::newInstance();
        $this->assertInstanceOf(
            'Openbuildings\Postmark\Swift_PostmarkTransport',
            $postmarkTransport
        );
        $this->assertNull($postmarkTransport->api());

        $postmarkTransport = Swift_PostmarkTransport::newInstance('token');
        $this->assertEquals('token', $postmarkTransport->api()->token());
    }

    /**
     * @covers Openbuildings\Postmark\Swift_PostmarkTransport::__construct
     */
    public function test_constructor()
    {
        $postmarkTransport = Swift_PostmarkTransport::newInstance('token');
        $this->assertTrue(Swift_DependencyContainer::getInstance()
            ->has('transport.postmark'));

        $this->assertEquals('token', $postmarkTransport->api()->token());
    }
}
