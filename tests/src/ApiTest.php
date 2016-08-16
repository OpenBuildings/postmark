<?php

namespace Openbuildings\Postmark\Test;

use Openbuildings\Postmark\Api;
use PHPUnit_Framework_TestCase;

/**
 * @group   api
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Openbuildings\Postmark\Api::__construct
     */
    public function testConstructor()
    {
        $api = new Api();
        $this->assertNull($api->getToken());

        $api = new Api('token');
        $this->assertEquals('token', $api->getToken());
    }

    /**
     * @covers Openbuildings\Postmark\Api::setToken
     */
    public function testSetToken()
    {
        $api = new Api();
        $api->setToken('token');
        $this->assertEquals('token', $api->getToken());
    }

    /**
     * @covers Openbuildings\Postmark\Api::getToken
     */
    public function testGetToken()
    {
        $api = new Api();
        $this->assertNull($api->getToken());

        $api = new Api('custom token');
        $this->assertEquals('custom token', $api->getToken());

        $api->setToken('another token');
        $this->assertEquals('another token', $api->getToken());
    }

    /**
     * @covers Openbuildings\Postmark\Api::getHeaders
     */
    public function testGetHeaders()
    {
        $expected = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: custom token',
        );

        $api = new Api('custom token');

        $this->assertEquals($expected, $api->getHeaders());
    }

    /**
     * @covers Openbuildings\Postmark\Api::getHeaders
     */
    public function testGetHeadersNoTokenException()
    {
        $api = new Api();
        $this->setExpectedException('Exception', 'You must set postmark token');
        $api->getHeaders();
    }

    /**
     * @covers Openbuildings\Postmark\Api::send
     */
    public function testSendWrongEmail()
    {
        $api = new Api('POSTMARK_API_TEST');

        $this->setExpectedException(
            'Exception',
            sprintf(
                "Postmark delivery failed: Error parsing 'To': Illegal email domain '%s' in address '%s'",
                'example.com>',
                'Jimmy Kenno <test_email@example.com>'
            )
        );

        $response = $api->send(
            array(
                'From' => 'support@example.com',
                'To' => '"Jimmy Kenno <test_email@example.com>"',
                'Subject' => 'Test',
                'TextBody' => 'Hello',
            )
        );
    }

    /**
     * @covers Openbuildings\Postmark\Api::send
     */
    public function testSend()
    {
        $api = new Api('POSTMARK_API_TEST');

        $response = $api->send(
            array(
                'From' => 'Mark Smith <support@example.com>',
                'To' => 'test_email@example.com,test_email2@example.com,test_email3@example.com',
                'Subject' => 'Test',
                'HtmlBody' => '<b>Hello</b>',
                'TextBody' => 'Hello',
                'ReplyTo' => 'reply@example.com',
                'Cc' => 'test2_email@example.com,"Tom ,\'\\" Smith" <test4_email@example.com>',
                'Bcc' => 'test3_email@example.com,test5_email@example.com',
                'Attachments' => array(
                    array(
                        'Name' => 'readme.txt',
                        'Content' => 'dGVzdCBjb250ZW50',
                        'ContentType' => 'text/plain'
                    ),
                    array(
                        'Name' => 'report.pdf',
                        'Content' => 'dGVzdCBjb250ZW50',
                        'ContentType' => 'application/octet-stream'
                    )
                )
            )
        );

        $this->assertArrayHasKey('To', $response);
        $this->assertArrayHasKey('SubmittedAt', $response);
        $this->assertArrayHasKey('MessageID', $response);
        $this->assertArrayHasKey('ErrorCode', $response);
        $this->assertArrayHasKey('Message', $response);
        $this->assertEquals(0, $response['ErrorCode']);

        $this->assertThat(
            $response['Message'],
            $this->logicalOr(
                $this->equalTo('Test job accepted'),
                $this->equalTo('Message accepted, but delivery may be delayed.')
            )
        );

        $this->setExpectedException(
            'Openbuildings\Postmark\Exception',
            "Postmark delivery failed: Invalid 'From' value.",
            300
        );

        $response = $api->send(array(
            'Wrong' => 'support@example.com',
        ));
    }

    /**
     * @covers Openbuildings\Postmark\Api::isSecure
     */
    public function testIsSecure()
    {
        $api = new Api();
        $this->assertTrue($api->isSecure());

        $api->setSecure(false);
        $this->assertFalse($api->isSecure());

        $api->setSecure(true);
        $this->assertTrue($api->isSecure());
    }

    /**
     * @covers Openbuildings\Postmark\Api::setSecure
     */
    public function testSetSecure()
    {
        $api = new Api();
        $this->assertSame($api, $api->setSecure(false));
        $this->assertFalse($api->isSecure());

        $api->setSecure(true);
        $this->assertTrue($api->isSecure());
    }

    /**
     * @covers Openbuildings\Postmark\Api::getSendUri
     */
    public function testGetSendUri()
    {
        $api = new Api();
        $this->assertEquals(Api::SEND_URI_SECURE, $api->getSendUri());

        $api->setSecure(false);
        $this->assertEquals(Api::SEND_URI, $api->getSendUri());

        $api->setSecure(true);
        $this->assertEquals(Api::SEND_URI_SECURE, $api->getSendUri());
    }

    /**
     * @covers Openbuildings\Postmark\Api::send
     */
    public function testSendWrongJson()
    {
        $apiMock = $this->getMockBuilder('Openbuildings\Postmark\Api')
            ->setMethods(array('getSendUri'))
            ->setConstructorArgs(array('POSTMARK_API_TEST'))
            ->getMock();

        $pathToWrongJson = 'file://'.realpath(__DIR__.'/../test_data/wrong-json.json');

        $apiMock
            ->expects($this->once())
            ->method('getSendUri')
            ->will($this->returnValue($pathToWrongJson));

        $this->setExpectedException(
            'Exception',
            'Postmark delivery failed: wrong json response'
        );

        $response = $apiMock->send(array(
            'From' => 'Mark Smith <support@example.com>',
            'To' => 'test_email@example.com,test_email2@example.com,test_email3@example.com',
            'Subject' => 'Test',
            'HtmlBody' => '<b>Hello</b>',
            'TextBody' => 'Hello',
            'ReplyTo' => 'reply@example.com',
        ));
    }
}
