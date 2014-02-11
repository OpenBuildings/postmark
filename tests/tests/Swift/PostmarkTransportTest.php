<?php

use Openbuildings\Postmark\Swift_PostmarkTransport;

/**
 * @group   swift.postmark-transport
 */
class Swift_PostmarkTransportTest extends PHPUnit_Framework_TestCase {

	public function test_send()
	{
		$transport = Swift_PostmarkTransport::newInstance('POSTMARK_API_TEST');
		$this->assertInstanceOf('Openbuildings\Postmark\Api', $transport->api());

		$api = $this->getMock('Openbuildings\Postmark\Api', array(), array('POSTMARK_API_TEST'));
		$transport->api($api);
		$mailer = Swift_Mailer::newInstance($transport);

		$api->expects($this->at(0))
			->method('send')
			->with($this->equalTo(array(
				'From' => 'test@example.com',
				'To' => 'test2@example.com',
				'Subject' => 'Test',
				'TextBody' => 'Test Email',
			)));

		$api->expects($this->at(1))
			->method('send')
			->with($this->equalTo(array(
				'From' => 'test@example.com',
				'To' => 'test2@example.com,test3@example.com',
				'Subject' => 'Test This',
				'HtmlBody' => 'Test Email',
			)));

		$api->expects($this->at(2))
			->method('send')
			->with($this->equalTo(array(
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
						'ContentType' => 'image/png'
					),
				)
			)));

		$api->expects($this->at(3))
			->method('send')
			->with($this->equalTo(array(
				'From' => 'test12@example.com',
				'To' => 'test13@example.com',
				'Subject' => 'Test Big',
				'TextBody' => 'Text Body',
				'Attachments' => array(
					array(
						'Name' => 'logo_black.png',
						'Content' => 'iVBORw0KGgoAAAANSUhEUgAAAHsAAAASCAYAAAB7PKHtAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABR0RVh0Q3JlYXRpb24gVGltZQAyLzcvMTOGU1gaAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAA+ZJREFUaIHtmeF12zYQx3/W83drA6kTRJkg7ATWBqUniDJB6AmiTlB6gsoThNmA2YDeQJmg/XDAI3S8A0G7TZ22//fwLIL3B+6Aw90RvsJGDeyBHbAJfV+BATgBrZLfAcfkuTVkLOR4dWgeBqAP+gzq3TGM7aFPuOcZblWgq4VOzXdw5LaInVWY4yb0f0HsatVYFv8Q+G8Ut8vpuAtCf8y0AXGGiEq9bzLKpcjxmgI9vPm6Qt5Z2WFxPV3P5B0qle0cmVIbe2euuoA7GDYWEXWrArdS/Y23Ago5XrNQl/TUdQu5+wzX0zUu5NqxbW6zl+rYK76lT27D19eBqEMUwFPoi5PsEYeIYebBMeLvwgOX4XrPGLYA3iP6pjIR98nvdeBukr4jEtKXYoOEycnJmUELvFN9D4xpJer4S3j3xHiwInRaeGAM2XsubaxJ0lXHpSe02B67xs4hleI3BtdCjteod5XBPzj8TvVbODnje1yta9qsfOydbD3O2bENJB932CH87Iyf4oA6xDs1+YAfmiL0+wp70eeQ4zXYm6H1sBa1w96wFHtnbo+rddVNb4i32drJvMJtDkv3jBXTENQwrVA15t5/L7xEj5fa8KSeTxQsOHCrxtDpsxRfkt8bZMOP+FGCFVOPfE7u+qdQq+fuO3FB0tlj8hzzdw4la30MulhNf/qluEHqls/IaT+hbLxm6o2v5dRq6IWqmIZAz1Er9VwzFj8A33hesVkHXiwUb5HI2DjyJWu9Y1q8WWiD7Hvn/W1oh6Bnf+0IvkZ8mnn/yPTzJOLzDLdZrI3gzLjh8SvlI+WOUxL2c4gFWIOk4xtD5k3QZ7syFFv6GfEa8Ej+ti2He56fN8G+HfMiTKeeK0OmDTrFNocBsX0NvAU+ILedKW6CzKQq1QqVoMKubF/Ca8hXvwOyMJUxbjfD7ZEN3hZwS3Ql6OLN1yVyvXpn6Z/iuXtTa+4K8cK0snxH/nOgDgq/NAQtxc/AVdK2jCF0Dleq7RAbh79QvwPTE2VBR5EW2+nmsEZs9yJxqztW4a/e3E+IE6RF0T70/caYBzxsEY/1Wu5O+UdFzN/fZuRaLp1igxyehnHT4w1alxnnhBzM34NcrfjaqS7GasmHPqtVgVst5HUOr0n0aZy5StAp7hJ43JyuKWp8eyN2XN6AlbZYC+ibw5K2jSc7KllSEIB4751hxP+QQ/PrjEyPOE9J2AdZ73vGkH1ECrG5KBJxBwwr1dkAPyGX6tZAT4ghO8r+X/1fRUn+jv+2vMvIfkU2dcs0ksQC06q+Ix6RCr0FKVZy2DLmgZ7Xe+Hyb0EV/p7x7wxy2CH52uT/Cao6KpIl3Ep0AAAAAElFTkSuQmCC',
						'ContentType' => 'image/png'
					),
				)
			)));

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
		$message->attach(Swift_Attachment::fromPath(__DIR__.'/../../test_data/logo_black.png'));

		$mailer->send($message);

		$message = Swift_Message::newInstance();

		$message->setFrom('test12@example.com');
		$message->setTo('test13@example.com');
		$message->setSubject('Test Big');
		$message->setBody('Text Body');
		$message->attach(Swift_Attachment::fromPath(__DIR__.'/../../test_data/logo_black.png'));

		$mailer->send($message);

		$transport->stop();

		$transport->registerPlugin($this->getMock('Swift_Events_EventListener'));
	}
}