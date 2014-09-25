<?php

namespace Openbuildings\Postmark\Test;

use Openbuildings\Postmark\Api;
use Openbuildings\Postmark\Test\Mock;
use PHPUnit_Framework_TestCase;

/**
 * @group   api
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Openbuildings\Postmark\Api::__construct
     */
    public function test_constructor()
    {
        $api = new Api();
        $this->assertNull($api->token());

        $api = new Api('token');
        $this->assertEquals('token', $api->token());
    }

    /**
     * @covers Openbuildings\Postmark\Api::token
     */
    public function test_token()
    {
        $api = new Api();
        $this->assertNull($api->token());

        $api = new Api('custom token');

        $this->assertEquals('custom token', $api->token());

        $api->token('another token');

        $this->assertEquals('another token', $api->token());
    }

    /**
     * @covers Openbuildings\Postmark\Api::headers
     */
    public function test_headers()
    {
        $expected = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: custom token',
        );

        $api = new Api('custom token');

        $this->assertEquals($expected, $api->headers());
    }

    /**
     * @covers Openbuildings\Postmark\Api::headers
     */
    public function test_headers_no_token_exception()
    {
        $api = new Api();
        $this->setExpectedException('Exception', 'You must set postmark token');
        $api->headers();
    }

    /**
     * @covers Openbuildings\Postmark\Api::send
     */
    public function test_send_wrong_email()
    {
        $api = new Api('POSTMARK_API_TEST');

        $this->setExpectedException(
            'Exception',
            "Postmark delivery failed: Error parsing 'To': Illegal email domain 'example.com>' in address 'Jimmy Kenno <test_email@example.com>'"
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
    public function test_send()
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

        $response = $api->send(
            array(
                'Wrong' => 'support@example.com',
            )
        );
    }

    /**
     * @covers Openbuildings\Postmark\Api::get_is_secure
     */
    public function test_get_is_secure()
    {
        $api = new Api();
        $this->assertTrue($api->get_is_secure());

        $api->set_is_secure(false);
        $this->assertFalse($api->get_is_secure());

        $api->set_is_secure(true);
        $this->assertTrue($api->get_is_secure());
    }

    /**
     * @covers Openbuildings\Postmark\Api::set_is_secure
     */
    public function test_set_is_secure()
    {
        $api = new Api();
        $this->assertSame($api, $api->set_is_secure(false));
        $this->assertFalse($api->get_is_secure());

        $api->set_is_secure(true);
        $this->assertTrue($api->get_is_secure());
    }

    /**
     * @covers Openbuildings\Postmark\Api::get_send_uri
     */
    public function test_get_send_uri()
    {
        $api = new Api();
        $this->assertEquals(Api::SEND_URI_SECURE, $api->get_send_uri());

        $api->set_is_secure(false);
        $this->assertEquals(Api::SEND_URI, $api->get_send_uri());

        $api->set_is_secure(true);
        $this->assertEquals(Api::SEND_URI_SECURE, $api->get_send_uri());
    }
}
