<?php

use Openbuildings\Postmark\Api;

/**
 * @group   api
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    public function test_token()
    {
        $api = new Api('custom token');

        $this->assertEquals('custom token', $api->token());

        $api->token('another token');

        $this->assertEquals('another token', $api->token());
    }

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

        $this->assertThat(
            $response['Message'],
            $this->logicalOr(
                $this->equalTo('Test job accepted'),
                $this->equalTo('Message accepted, but delivery may be delayed.')
            )
        );

        $this->setExpectedException('Exception');

        $response = $api->send(
            array(
                'Wrong' => 'support@example.com',
            )
        );
    }
}
