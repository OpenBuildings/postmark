<?php

namespace Openbuildings\Postmark;

use Swift_DependencyContainer;

/**
 * Class for manupulating a server
 *
 * @package        openbuildings\postmark
 * @author         Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license        http://spdx.org/licenses/BSD-3-Clause
 */
class Swift_PostmarkTransport extends Swift_Transport_PostmarkTransport
{
    /**
     * Create a new PostmarkTransport.
     *
     * @param null|string $token
     */
    public function __construct($token = null)
    {
        Swift_DependencyContainer::getInstance()
            ->register('transport.postmark')
            ->asNewInstanceOf('Openbuildings\Postmark\Swift_Transport_PostmarkTransport')
            ->withDependencies(array('transport.eventdispatcher'));

        call_user_func_array(
            array($this, 'Openbuildings\Postmark\Swift_Transport_PostmarkTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.postmark')
        );

        if ($token) {
            $this->setApi(new Api($token));
        }
    }

    /**
     * Create a new PostmarkTransport instance.
     *
     * @param null|string $token
     * @return Swift_PostmarkTransport
     */
    public static function newInstance($token = null)
    {
        return new self($token);
    }
}
