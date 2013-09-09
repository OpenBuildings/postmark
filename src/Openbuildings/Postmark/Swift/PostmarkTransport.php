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
class Swift_PostmarkTransport extends Swift_Transport_PostmarkTransport
{
	/**
	 * Create a new PostmarkTransport.
	 */
	public function __construct($token = NULL)
	{
		\Swift_DependencyContainer::getInstance()
			->register('transport.postmark')
			->asNewInstanceOf('Openbuildings\Postmark\Swift_Transport_PostmarkTransport')
			->withDependencies(array('transport.eventdispatcher'));

		call_user_func_array(
			array($this, 'Openbuildings\Postmark\Swift_Transport_PostmarkTransport::__construct'),
			\Swift_DependencyContainer::getInstance()
				->createDependenciesFor('transport.postmark')
		);

		if ($token) 
		{
			$this->api(new Api($token));
		}
	}

	/**
	 * Create a new PostmarkTransport instance.
	 *
	 * @return Swift_PostmarkTransport
	 */
	public static function newInstance($token = NULL)
	{
		return new self($token);
	}
}
