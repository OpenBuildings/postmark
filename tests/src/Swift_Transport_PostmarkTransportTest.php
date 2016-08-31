<?php

namespace Openbuildings\Postmark\Test;

use Openbuildings\Postmark\Swift_PostmarkTransport;
use Openbuildings\Postmark\Api;
use Openbuildings\Postmark\Swift_Transport_PostmarkTransport;
use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;
use PHPUnit_Framework_TestCase;
use Swift_DependencyContainer;
use Swift_Events_ResponseEvent;
use Swift_Events_SimpleEventDispatcher;

/**
 * @group   swift.postmark-transport
 */
class Swift_Transport_PostmarkTransportTest extends PHPUnit_Framework_TestCase
{
    public function getTransportPostmark()
    {
        return new Swift_Transport_PostmarkTransport(
            new Swift_Events_SimpleEventDispatcher()
        );
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::getApi
     */
    public function testGetApi()
    {
        $transportPostmark = $this->getTransportPostmark();
        $this->assertNull($transportPostmark->getApi());

        $api = new Api();
        $transportPostmark->setApi($api);
        $this->assertSame($api, $transportPostmark->getApi());
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::setApi
     */
    public function testSetApi()
    {
        $api = new Api();
        $transportPostmark = $this->getTransportPostmark();
        $transportPostmark->setApi($api);
        $this->assertSame($api, $transportPostmark->getApi());
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::isStarted
     */
    public function testIsStarted()
    {
        $this->assertFalse($this->getTransportPostmark()->isStarted());
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::start
     */
    public function testStart()
    {
        $this->assertFalse($this->getTransportPostmark()->start());
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::stop
     */
    public function testStop()
    {
        $this->assertFalse($this->getTransportPostmark()->stop());
    }

    public function dataConvertEmailsArray()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array(
                    'john.smith@example.com' => 'John Smith',
                    'john.long.doe@example.com' => 'John "Long" Doe',
                    'me@example.com' => '',
                ),
                array(
                    '"John Smith" <john.smith@example.com>',
                    '"John \\"Long\\" Doe" <john.long.doe@example.com>',
                    'me@example.com',
                ),
            ),
        );
    }

    /**
     * @dataProvider dataConvertEmailsArray
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::convertEmailsArray
     */
    public function testConvertEmailsArray(array $emails, $expectedConvertedEmails)
    {
        $transportPostmark = $this->getTransportPostmark();
        $this->assertSame(
            $expectedConvertedEmails,
            Swift_Transport_PostmarkTransport::convertEmailsArray($emails)
        );
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::send
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::getMIMEPart
     */
    public function testSend()
    {
        $eventDispatcherMock = $this->getMockBuilder(
            'Swift_Events_SimpleEventDispatcher'
        )
            ->setMethods(array('createResponseEvent', 'dispatchEvent'))
            ->getMock();

        $transport = new Swift_Transport_PostmarkTransport(
            $eventDispatcherMock
        );

        $responseEvent = new Swift_Events_ResponseEvent($transport, array(
            'MessageID' => '123456',
        ), true);

        $eventDispatcherMock
            ->expects($this->once())
            ->method('createResponseEvent')
            ->with(
                $transport,
                '123456',
                true
            )
            ->will($this->returnValue($responseEvent));

        $eventDispatcherMock
            ->expects($this->at(3))
            ->method('dispatchEvent')
            ->with($responseEvent, 'responseReceived');

        $api = $this->getMockBuilder('Openbuildings\Postmark\Api')
            ->setConstructorArgs(array('POSTMARK_API_TEST'))
            ->getMock();

        $transport->setApi($api);

        $mailer = Swift_Mailer::newInstance($transport);

        $api->expects($this->at(0))
            ->method('send')
            ->with(
                $this->equalTo(
                    array(
                        'From' => 'test@example.com',
                        'To' => 'test2@example.com',
                        'Subject' => 'Test',
                        'TextBody' => 'Test Email',
                    )
                )
            )
            ->will($this->returnValue(array(
                'MessageID' => '123456',
            )));

        $api->expects($this->at(1))
            ->method('send')
            ->with(
                $this->equalTo(
                    array(
                        'From' => 'test@example.com',
                        'To' => 'test2@example.com,test3@example.com',
                        'Subject' => 'Test This',
                        'HtmlBody' => 'Test Email',
                    )
                )
            );

        $attachment1 = Swift_Attachment::fromPath(__DIR__ . '/../test_data/logo_black.png');
        $attachment1->setDisposition('inline');
        $attachment1->setId('test@example.com');

        $api->expects($this->at(2))
            ->method('send')
            ->with(
                $this->equalTo(
                    array(
                        'From' => '"John Smith" <test12@example.com>',
                        'To' => '"Paul Smith" <test13@example.com>',
                        'Cc' => '"Jane \"Panny\" Smith" <test14@example.com>,test15@example.com',
                        'Bcc' => '"Gale Smith" <test16@example.com>,"Mark Smith" <test17@example.com>',
                        'ReplyTo' => '"Tom Smith" <test18@example.com>',
                        'Subject' => 'Test Big',
                        'TextBody' => 'Text Part',
                        'HtmlBody' => 'HTML Part',
                        'Attachments' => array(
                            array(
                                'Name' => 'logo_black.png',
                                'Content' => 'iVBORw0KGgoAAAANSUhEUgAAAHsAAAASCAYAAAB7PKHtAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABR0RVh0Q3JlYXRpb24gVGltZQAyLzcvMTOGU1gaAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAA+ZJREFUaIHtmeF12zYQx3/W83drA6kTRJkg7ATWBqUniDJB6AmiTlB6gsoThNmA2YDeQJmg/XDAI3S8A0G7TZ22//fwLIL3B+6Aw90RvsJGDeyBHbAJfV+BATgBrZLfAcfkuTVkLOR4dWgeBqAP+gzq3TGM7aFPuOcZblWgq4VOzXdw5LaInVWY4yb0f0HsatVYFv8Q+G8Ut8vpuAtCf8y0AXGGiEq9bzLKpcjxmgI9vPm6Qt5Z2WFxPV3P5B0qle0cmVIbe2euuoA7GDYWEXWrArdS/Y23Ago5XrNQl/TUdQu5+wzX0zUu5NqxbW6zl+rYK76lT27D19eBqEMUwFPoi5PsEYeIYebBMeLvwgOX4XrPGLYA3iP6pjIR98nvdeBukr4jEtKXYoOEycnJmUELvFN9D4xpJer4S3j3xHiwInRaeGAM2XsubaxJ0lXHpSe02B67xs4hleI3BtdCjteod5XBPzj8TvVbODnje1yta9qsfOydbD3O2bENJB932CH87Iyf4oA6xDs1+YAfmiL0+wp70eeQ4zXYm6H1sBa1w96wFHtnbo+rddVNb4i32drJvMJtDkv3jBXTENQwrVA15t5/L7xEj5fa8KSeTxQsOHCrxtDpsxRfkt8bZMOP+FGCFVOPfE7u+qdQq+fuO3FB0tlj8hzzdw4la30MulhNf/qluEHqls/IaT+hbLxm6o2v5dRq6IWqmIZAz1Er9VwzFj8A33hesVkHXiwUb5HI2DjyJWu9Y1q8WWiD7Hvn/W1oh6Bnf+0IvkZ8mnn/yPTzJOLzDLdZrI3gzLjh8SvlI+WOUxL2c4gFWIOk4xtD5k3QZ7syFFv6GfEa8Ej+ti2He56fN8G+HfMiTKeeK0OmDTrFNocBsX0NvAU+ILedKW6CzKQq1QqVoMKubF/Ca8hXvwOyMJUxbjfD7ZEN3hZwS3Ql6OLN1yVyvXpn6Z/iuXtTa+4K8cK0snxH/nOgDgq/NAQtxc/AVdK2jCF0Dleq7RAbh79QvwPTE2VBR5EW2+nmsEZs9yJxqztW4a/e3E+IE6RF0T70/caYBzxsEY/1Wu5O+UdFzN/fZuRaLp1igxyehnHT4w1alxnnhBzM34NcrfjaqS7GasmHPqtVgVst5HUOr0n0aZy5StAp7hJ43JyuKWp8eyN2XN6AlbZYC+ibw5K2jSc7KllSEIB4751hxP+QQ/PrjEyPOE9J2AdZ73vGkH1ECrG5KBJxBwwr1dkAPyGX6tZAT4ghO8r+X/1fRUn+jv+2vMvIfkU2dcs0ksQC06q+Ix6RCr0FKVZy2DLmgZ7Xe+Hyb0EV/p7x7wxy2CH52uT/Cao6KpIl3Ep0AAAAAElFTkSuQmCC',
                                'ContentType' => 'image/png',
                                'ContentID' => 'cid:' . $attachment1->getId()
                            ),
                        )
                    )
                )
            );

        $attachment2 = Swift_Attachment::fromPath(__DIR__ . '/../test_data/logo_black.png');
        $attachment2->setDisposition('attachment');

        $api->expects($this->at(3))
            ->method('send')
            ->with(
                $this->equalTo(
                    array(
                        'From' => 'test12@example.com',
                        'To' => 'test13@example.com',
                        'Subject' => 'Test Big',
                        'TextBody' => 'Text Body',
                        'Attachments' => array(
                            array(
                                'Name' => 'logo_black.png',
                                'Content' => 'iVBORw0KGgoAAAANSUhEUgAAAHsAAAASCAYAAAB7PKHtAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABR0RVh0Q3JlYXRpb24gVGltZQAyLzcvMTOGU1gaAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAA+ZJREFUaIHtmeF12zYQx3/W83drA6kTRJkg7ATWBqUniDJB6AmiTlB6gsoThNmA2YDeQJmg/XDAI3S8A0G7TZ22//fwLIL3B+6Aw90RvsJGDeyBHbAJfV+BATgBrZLfAcfkuTVkLOR4dWgeBqAP+gzq3TGM7aFPuOcZblWgq4VOzXdw5LaInVWY4yb0f0HsatVYFv8Q+G8Ut8vpuAtCf8y0AXGGiEq9bzLKpcjxmgI9vPm6Qt5Z2WFxPV3P5B0qle0cmVIbe2euuoA7GDYWEXWrArdS/Y23Ago5XrNQl/TUdQu5+wzX0zUu5NqxbW6zl+rYK76lT27D19eBqEMUwFPoi5PsEYeIYebBMeLvwgOX4XrPGLYA3iP6pjIR98nvdeBukr4jEtKXYoOEycnJmUELvFN9D4xpJer4S3j3xHiwInRaeGAM2XsubaxJ0lXHpSe02B67xs4hleI3BtdCjteod5XBPzj8TvVbODnje1yta9qsfOydbD3O2bENJB932CH87Iyf4oA6xDs1+YAfmiL0+wp70eeQ4zXYm6H1sBa1w96wFHtnbo+rddVNb4i32drJvMJtDkv3jBXTENQwrVA15t5/L7xEj5fa8KSeTxQsOHCrxtDpsxRfkt8bZMOP+FGCFVOPfE7u+qdQq+fuO3FB0tlj8hzzdw4la30MulhNf/qluEHqls/IaT+hbLxm6o2v5dRq6IWqmIZAz1Er9VwzFj8A33hesVkHXiwUb5HI2DjyJWu9Y1q8WWiD7Hvn/W1oh6Bnf+0IvkZ8mnn/yPTzJOLzDLdZrI3gzLjh8SvlI+WOUxL2c4gFWIOk4xtD5k3QZ7syFFv6GfEa8Ej+ti2He56fN8G+HfMiTKeeK0OmDTrFNocBsX0NvAU+ILedKW6CzKQq1QqVoMKubF/Ca8hXvwOyMJUxbjfD7ZEN3hZwS3Ql6OLN1yVyvXpn6Z/iuXtTa+4K8cK0snxH/nOgDgq/NAQtxc/AVdK2jCF0Dleq7RAbh79QvwPTE2VBR5EW2+nmsEZs9yJxqztW4a/e3E+IE6RF0T70/caYBzxsEY/1Wu5O+UdFzN/fZuRaLp1igxyehnHT4w1alxnnhBzM34NcrfjaqS7GasmHPqtVgVst5HUOr0n0aZy5StAp7hJ43JyuKWp8eyN2XN6AlbZYC+ibw5K2jSc7KllSEIB4751hxP+QQ/PrjEyPOE9J2AdZ73vGkH1ECrG5KBJxBwwr1dkAPyGX6tZAT4ghO8r+X/1fRUn+jv+2vMvIfkU2dcs0ksQC06q+Ix6RCr0FKVZy2DLmgZ7Xe+Hyb0EV/p7x7wxy2CH52uT/Cao6KpIl3Ep0AAAAAElFTkSuQmCC',
                                'ContentType' => 'image/png',
                            ),
                        )
                    )
                )
            );

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo('test2@example.com');
        $message->setSubject('Test');
        $message->setBody('Test Email');

        $mailer->send($message);

        $message = Swift_Message::newInstance();

        $message->setFrom('test@example.com');
        $message->setTo(array('test2@example.com', 'test3@example.com'));
        $message->setSubject('Test This');
        $message->setBody('Test Email', 'text/html');

        $mailer->send($message);

        $message = Swift_Message::newInstance();

        $message->setFrom('test12@example.com', 'John Smith');
        $message->setTo('test13@example.com', 'Paul Smith');
        $message->setReplyTo('test18@example.com', 'Tom Smith');
        $message->setSubject('Test Big');
        $message->setCc(array('test14@example.com' => 'Jane "Panny" Smith', 'test15@example.com'));
        $message->setBcc(array('test16@example.com' => 'Gale Smith', 'test17@example.com' => 'Mark Smith'));
        $message->addPart('HTML Part', 'text/html');
        $message->addPart('Text Part', 'text/plain');
        $message->attach($attachment1);

        $mailer->send($message);

        $message = Swift_Message::newInstance();

        $message->setFrom('test12@example.com');
        $message->setTo('test13@example.com');
        $message->setSubject('Test Big');
        $message->setBody('Text Body');
        $message->attach($attachment2);

        $mailer->send($message);

        $transport->stop();

        $transport->registerPlugin(
            $this->getMockBuilder('Swift_Events_EventListener')->getMock()
        );
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::send
     */
    public function testSendEventCancelled()
    {
        $event_dispatcher_mock = $this->getMockBuilder(
            'Swift_Events_SimpleEventDispatcher'
        )
            ->setMethods(array('createSendEvent', 'dispatchEvent'))
            ->getMock();

        $transportPostmark = new Swift_Transport_PostmarkTransport(
            $event_dispatcher_mock
        );

        $send_event_mock = $this->getMockBuilder('Swift_Events_SendEvent')
            ->setMethods(array('bubbleCancelled'))
            ->disableOriginalConstructor()
            ->getMock();

        $send_event_mock
            ->expects($this->once())
            ->method('bubbleCancelled')
            ->will($this->returnValue(true));

        $message = $this->getMockBuilder('Swift_Mime_SimpleMessage')
            ->disableOriginalConstructor()
            ->getMock();

        $event_dispatcher_mock
            ->expects($this->once())
            ->method('createSendEvent')
            ->will($this->returnValue($send_event_mock));

        $event_dispatcher_mock
            ->expects($this->once())
            ->method('dispatchEvent');

        $result = $transportPostmark->send($message);
        $this->assertSame(0, $result);
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::send
     */
    public function testResponseEventFires()
    {
        $eventDispatcher = $this->getMockBuilder('Swift_Events_SimpleEventDispatcher')
            ->getMock();

        $transport = new Swift_Transport_PostmarkTransport($eventDispatcher);
        $event = new Swift_Events_ResponseEvent($transport, 1234, true);
        $api = $this->getMockBuilder('Openbuildings\Postmark\Api')
            ->setConstructorArgs(array('POSTMARK_API_TEST'))
            ->getMock();

        $transport->setApi($api);

        $api->expects($this->at(0))
            ->method('send')
            ->will($this->returnValue(array('MessageID' => 1234)));

        $eventDispatcher->expects($this->once())
            ->method('createResponseEvent')
            ->with($this->equalTo($transport), $this->equalTo('1234'), $this->equalTo(true))
            ->will($this->returnValue($event));

        $eventDispatcher->expects($this->once())
            ->method('dispatchEvent')
            ->with($this->equalTo($event), $this->equalTo('responseReceived'));

        $api->expects($this->at(1))
            ->method('send')
            ->will($this->returnValue(array()));

        $message = Swift_Message::newInstance();
        $message->setFrom('test12@example.com');
        $message->setTo('test13@example.com');
        $message->setSubject('Test Big');
        $message->setBody('Text Body');

        // Response Event should fire.
        $transport->send($message);

        // Response Event should not fire.
        $transport->send($message);
    }

    /**
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::__construct
     * @covers Openbuildings\Postmark\Swift_Transport_PostmarkTransport::registerPlugin
     */
    public function testRegisterPlugin()
    {
        $event_dispatcher = $this->getMockBuilder(
            'Swift_Events_SimpleEventDispatcher'
        )
            ->setMethods(array('bindEventListener'))
            ->getMock();

        $event_listener = $this->getMockBuilder('Swift_Plugins_MessageLogger')
            ->getMock();

        $event_dispatcher
            ->expects($this->once())
            ->method('bindEventListener')
            ->with($event_listener);

        $transportPostmark = new Swift_Transport_PostmarkTransport($event_dispatcher);
        $transportPostmark->registerPlugin($event_listener);
    }
}
