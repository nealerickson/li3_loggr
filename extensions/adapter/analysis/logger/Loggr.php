<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_loggr\extensions\adapter\analysis\logger;

use lithium\util\String;
use lithium\core\Libraries;
use li3_loggr\util\Loggr as LoggrAgent;

/**
 *
 *  The `Loggr` logger adapter integrates with the realtime web app monitoring service Loggr (http://loggr.net)
 *
 * {{{
 *  use lithium\analysis\Logger;
 *
 *	Logger::error("Sample error message", array(
 *		'loggr' => array(
 *			'tags' => 'error testing',
 *			'value' => 100,
 *			'data' => String::insert('<b>Hello</b>, {:world}', array('world' => 'World!')),
 *			'user' => 'neal',
 *			'source' => 'web',
 *			'geoIp' => true,
 *			'dataType' => 'html'
 *		)
 *	));
 *
 * }}}
 *
 *
 * @see lithium\analysis\logger\adapter\File::__construct()
 */
class Loggr extends \lithium\core\Object {

	/**
	 * Array that maps `Logger` message priority names to default Loggr event tags.
	 *
	 * @var array
	 */
	protected $_priorityTags = array(
		'emergency' => 'emergency',
		'alert'     => 'alert',
		'critical'  => 'critical',
		'error'     => 'error',
		'warning'   => 'warning',
		'notice'    => 'notice',
		'info'      => 'info',
		'debug'     => 'debug'
	);

	/**
	 * Class constructor.
	 *
	 * @see lithium\util\String::insert()
	 * @param array $config Settings used to configure the adapter. Available options:
	 *              - `'logKey'` _string_: The name of the Loggr log where you want the event to be posted`.
	 *              - `'apiKey'` _string_: Loggr API key for your log`.
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'logKey' => LOGGR_LOG_KEY,
			'apiKey' => LOGGR_API_KEY,
			'logByDefault' => false,
			'source' => null,
			'tags' => null,
			'geoIp' => false,
			'dataType' => 'html'
		);
		parent::__construct($config + $defaults);
	}

	/**
	 * Log the event to Loggr
	 *
	 * @see lithium\analysis\Logger::$_priorities
	 * @param string $priority The message priority. See `Logger::$_priorities`.
	 * @param string $message The text of the Loggr event.
	 * @param array $options Any options that are passed to the `post()` method.
	 *				See the `$options` parameter of `post()`.
	 * @return closure Function returning boolean `true` on successful write, `false` otherwise.
	 */
	public function write($priority, $message, array $options = array()) {
		$_self =& $this;
		$_config = $this->_config;
		$_priorityTags = $this->_priorityTags;

		$options['priority'] = $priority;

		return function($self, $params) use (&$_self, $_config, $_priorityTags) {
			$options = $params['options'];
			$post = $_config['logByDefault'];
			$priorityTags = '';

			if (isset($options['loggr'])) {
				if (is_array($options['loggr'])) {
					$post = true;
				} elseif (is_scalar($options['loggr'])) {
					$post = (boolean) $options['loggr'];
				} else {
					$post = false;
				}
			}
			if (!$post) {
				return true;
			}

			if (!isset($options['loggr']) || !is_array($options['loggr'])) {
				$options['loggr'] = array();
			}
			if (isset($params['priority']) && isset($_priorityTags[ $params['priority'] ])) {
				$priorityTags = $_priorityTags[ $params['priority'] ];
			}

			return $_self->post($params['message'], compact('priorityTags') + $options['loggr']);
		};
	}

	public function post($message, array $options = array()) {
		$defaults = array(
			'logKey' => $this->_config['logKey'],
			'apiKey' => $this->_config['apiKey'],
			'text' => $message,
			'exception' => null,
			'variable' => null,
			'data' => null,
			'tags' => null,
			'source' => $this->_config['source'],
			'user' => null,
			'value' => null,
			'geo' => null,
			'geoIp' => $this->_config['source'],
			'link' => null,
			'dataType' => $this->_config['dataType'],
			'priorityTags' => null
		);
		$options += $defaults;

		// Initialize the base Loggr class
		$agent = LoggrAgent::init($options['logKey'], $options['apiKey']);

		if ($options['exception']) {
			$event = $agent->events->createFromException($options['exception']);
		} elseif ($options['variable']) {
			$event = $agent->events->createFromVariable($options['variable']);
		} else {
			$event = $agent->events->create();
		}
		$event->text($options['text']);

		// Check for and set any global tags that were configured
		if ($this->_config['tags']) {
			if ($options['tags']) {
				$options['tags'] .= " " . $this->_config['tags'];
			} else {
				$options['tags'] = $this->_config['tags'];
			}
		}
		if ($options['priorityTags']) {
			$options['tags'] .= " " . $options['priorityTags'];
		}
		$options['tags'] .= " " . $event->event->tags;

		// Clean up the tags and make sure our tags are unique
		$tags = explode(" ", $options['tags']);
		$tags = array_unique($tags);
		$options['tags'] = implode(" ", $tags);

		if (!empty($options['tags'])) {
			$event->tags($options['tags']);
		}

		if ($options['data']) {
			if ($options['exception'] || $options['variable']) {
				$options['data'] = $options['data'] . " " . $event->event->data;
			}
			$event->data($options['data']);
		}

		if ($options['value'] !== null) {
			$event->value($options['value']);
		}

		if ($options['source']) {
			$event->source($options['source']);
		}

		if ($options['user']) {
			$event->user($options['user']);
		}

		if (is_array($options['geo']) && isset($options['geo'][1])) {
			$event->user($options['geo'][0], $options['geo'][1]);
		}

		if ($options['geoIp']) {
			$event->geoIp();
		}

		if ($options['link']) {
			$event->user($options['link']);
		}

		if ($options['dataType']) {
			$event->dataType($options['dataType']);
		}

		try {
			$event->post();
		} catch (\Exception $e) {
			// For now do nothing
			return false;
		}
		return true;
	}
}

?>
