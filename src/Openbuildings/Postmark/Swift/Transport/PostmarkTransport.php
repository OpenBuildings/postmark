<?php

namespace Openbuildings\Postmark;

/**
 * Class for manupulating a server
 *
 * @package    openbuildings\postmark
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Swift_Transport_PostmarkTransport implements \Swift_Transport {
	
	protected $_api;
	protected $_eventDispatcher;

	public function __construct(\Swift_Events_EventDispatcher $eventDispatcher)
	{
		$this->_eventDispatcher = $eventDispatcher;
	}

	public function api(Api $api = NULL)
	{
		if ($api !== NULL)
		{
			$this->_api = $api;
			return $this;
		}
		return $this->_api;
	}
	
	public function isStarted() 
	{
		return FALSE; 
	}

	public function start() 
	{
		return FALSE;
	}

	public function stop() 
	{
		return FALSE;
	}
	
	/**
	 * @param Swift_Mime_Message $message
	 * @param string $mime_type
	 * @return Swift_Mime_MimePart
	 */
	protected function getMIMEPart(\Swift_Mime_Message $message, $mime_type) 
	{
		$part_content = NULL;
		foreach ($message->getChildren() as $part) 
		{
			if (strpos($part->getContentType(), $mime_type) === 0)
			{
				$part_content = $part;
			}
		}
		return $part_content;
	}

	/**
	 * @param Swift_Mime_Message $message
	 * @param array $failed_recipients
	 * @return int
	 */
	public function send(\Swift_Mime_Message $message, & $failed_recipients = NULL) 
	{
		if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
				$this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
				if ($evt->bubbleCancelled()) {
						return 0;
				}
		}
		
		$data = array(
			'From' => join(',', array_keys($message->getFrom())),
			'To' => join(',', array_keys($message->getTo())),
			'Subject' => $message->getSubject(),
		);

		if ($cc = $message->getCc()) 
		{
			$data['Cc'] = join(',', array_keys($cc));
		}

		if ($reply_to = $message->getReplyTo())
		{
			$data['ReplyTo'] = join(',', array_keys($reply_to));
		}

		if ($bcc = $message->getBcc())
		{
			$data['Bcc'] = join(',', array_keys($bcc));
		}

		switch ($message->getContentType()) 
		{
			case 'text/plain':
				$data['TextBody'] = $message->getBody();
			break;
			case 'text/html':
				$data['HtmlBody'] = $message->getBody();
			break;			
		}

		if ($plain =  $this->getMIMEPart($message, 'text/plain'))
		{
			$data['TextBody'] = $plain->getBody();
		}

		if ($html =  $this->getMIMEPart($message, 'text/html'))
		{
			$data['HtmlBody'] = $html->getBody();
		}

		if ($message->getChildren())
		{
			$data['Attachments'] = array();

			foreach ($message->getChildren() as $attachment) 
			{
				if (is_object($attachment) AND $attachment instanceof \Swift_Mime_Attachment)
				{
					$data['Attachments'][] = array(
						'Name' => $attachment->getFilename(),
						'Content' => base64_encode($attachment->getBody()),
						'ContentType' => $attachment->getContentType()
					);
				}
			}
		}

		$this->api()->send($data);

		if ($evt) {
				$evt->setResult(\Swift_Events_SendEvent::RESULT_SUCCESS);
				$this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
		}

		return 1;
	}
	
	public function registerPlugin(\Swift_Events_EventListener $plugin) 
	{
		$this->_eventDispatcher->bindEventListener($plugin);
	} 
}