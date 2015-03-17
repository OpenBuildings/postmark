<?php

namespace Openbuildings\Postmark;

/**
 * Class for manupulating a server
 *
 * @package        openbuildings\postmark
 * @author         Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license        http://spdx.org/licenses/BSD-3-Clause
 */
class Swift_Transport_PostmarkTransport implements \Swift_Transport
{
    /**
     * The Postmark API SDK instance.
     *
     * @var Openbuildings\Postmark\Api
     */
    protected $api;

    /**
     * @var Swift_Events_EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(\Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Convert email dictionary with emails and names
     * to array of emails with names.
     *
     * @param array $emails
     * @return array
     */
    public static function convertEmailsArray(array $emails)
    {
        $convertedEmails = array();

        foreach ($emails as $email => $name) {
            $convertedEmails [] = $name
                ? '"'.str_replace('"', '\\"', $name)."\" <{$email}>"
                : $email;
        }

        return $convertedEmails;
    }

    /**
     * Get the Postmark API SDK instance
     *
     * @return Openbuildings\Postmark\Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Set the Postmark API SDK instance
     *
     * @param Api $api
     * @return $this
     */
    public function setApi(Api $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return false;
    }

    /**
     * @param Swift_Mime_Message $message
     * @param string              $mimeType
     * @return Swift_Mime_MimePart
     */
    protected function getMIMEPart(\Swift_Mime_Message $message, $mimeType)
    {
        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), $mimeType) === 0) {
                return $part;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $data = array(
            'From' => join(',', static::convertEmailsArray($message->getFrom())),
            'To' => join(',', static::convertEmailsArray($message->getTo())),
            'Subject' => $message->getSubject(),
        );

        if ($cc = $message->getCc()) {
            $data['Cc'] = join(',', static::convertEmailsArray($cc));
        }

        if ($reply_to = $message->getReplyTo()) {
            $data['ReplyTo'] = join(',', static::convertEmailsArray($reply_to));
        }

        if ($bcc = $message->getBcc()) {
            $data['Bcc'] = join(',', static::convertEmailsArray($bcc));
        }

        switch ($message->getContentType()) {
            case 'text/html':
            case 'multipart/alternative':
                $data['HtmlBody'] = $message->getBody();
                break;
            default:
                $data['TextBody'] = $message->getBody();
                break;
        }

        if ($plain = $this->getMIMEPart($message, 'text/plain')) {
            $data['TextBody'] = $plain->getBody();
        }

        if ($html = $this->getMIMEPart($message, 'text/html')) {
            $data['HtmlBody'] = $html->getBody();
        }

        if ($message->getChildren()) {
            $data['Attachments'] = array();

            foreach ($message->getChildren() as $attachment) {
                if (is_object($attachment) and $attachment instanceof \Swift_Mime_Attachment) {
                    $data['Attachments'][] = array(
                        'Name' => $attachment->getFilename(),
                        'Content' => base64_encode($attachment->getBody()),
                        'ContentType' => $attachment->getContentType(),
                        'ContentID' => sprintf('cid:%s', $attachment->getId())
                    );
                }
            }
        }

        $response = $this->getApi()->send($data);

        if ($evt) {
            $evt->setResult(\Swift_Events_SendEvent::RESULT_SUCCESS);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        if (isset($response['MessageID'])) {
            $responseEvent = $this->eventDispatcher->createResponseEvent(
                $this,
                $response['MessageID'],
                true
            );
            $this->eventDispatcher->dispatchEvent($responseEvent, 'responseReceived');
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}
