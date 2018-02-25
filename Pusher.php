<?php

namespace veirons\pusher;

use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * This component enables you to use Pusher in Yii2.
 */
class Pusher extends Component
{
    public $appId = null;
    public $appKey = null;
    public $appSecret = null;
    public $options = [];
    private $_pusher = null;
    private $_selectableOptions = ['host', 'port', 'timeout', 'encrypted', 'cluster'];

    public function init()
    {
        parent::init();

        /*
         * Mandatory config parameters.
         */
        if (!$this->appId) {
            throw new InvalidConfigException('AppId cannot be empty!');
        }

        if (!$this->appKey) {
            throw new InvalidConfigException('AppKey cannot be empty!');
        }

        if (!$this->appSecret) {
            throw new InvalidConfigException('AppSecret cannot be empty!');
        }

        foreach (array_keys($this->options) as $key) {
            if (in_array($key, $this->_selectableOptions) === false) {
                throw new InvalidConfigException($key . ' is not a valid option!');
            }
        }

        /*
         * Create a new Pusher object if it hasn't already been created.
         */
        if ($this->_pusher === null) {
            $this->_pusher = new \Pusher($this->appKey, $this->appSecret, $this->appId, $this->options);
        }
    }

    /**
     * Proxy the calls to the Pusher object if the methods doesn't explicitly exist in this class.
     *
     * If the method called is not found in the Pusher Object continue with standard Yii behaviour
     */
    public function __call($method, $params)
    {
        /*
         * Override the normal Yii functionality.
         */
        if (method_exists($this->_pusher, $method)) {
            return call_user_func_array([$this->_pusher, $method], $params);
        }

        /*
         * Use standard Yii functionality, checking behaviours.
         */
        return parent::__call($method, $params);
    }

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * Overriding this method to deal with clash with base class trigger method.
     *
     * @param array $channels An array of channel names to publish the event on.
     * @param string $event
     * @param mixed $data Event data
     * @param int $socket_id [optional]
     * @param bool $debug [optional]
     * @return bool|string
     */
    public function push($channels, $event, $data, $socket_id = null, $debug = false, $already_encoded = false)
    {
        $this->_pusher->trigger($channels, $event, $data, $socket_id, $debug, $already_encoded);
    }
}
