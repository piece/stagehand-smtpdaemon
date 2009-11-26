<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 mbarracuda <mbarracuda@gmail.com>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_SmtpDaemon_Handler

/**
 * Stagehand_SmtpDaemon_Handler
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemon_Handler extends Net_Server_Handler
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $context;
    protected $plugin;
    protected $response;
    protected $debugCommand = null;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     */
    public function __construct()
    {
        $this->context = new Stagehand_SmtpDaemon_Context();
        $this->response = new Stagehand_SmtpDaemon_Response();
    }

    // }}}
    // {{{ onConnect()

    /**
     * @param integer $clientId
     */
    public function onConnect($clientId = 0)
    {
        $this->response->setCode(220);
        $this->response->setMessage($this->_server->domain);

        if ($this->plugin) {
            $this->plugin->onConnect($clientId);
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ onReceiveData()

    /**
    * @param integer $clientId
    * @param string  $data
     */
    public function onReceiveData($clientId = 0, $data = "")
    {
        $data = trim($data);

        if ($this->context->isDataState()) {
            if ($this->isDebugCommand($data)) {
                return $this->debug($clientId);
            }

            $this->onDataReceived($clientId, $data);
            return;
        }

        list($command, $argument) = $this->parseReceiveData($data);

        switch ($command) {
        case 'helo':
            $this->onHelo($clientId, $argument);
            break;
        case 'mail':
            $this->onMail($clientId, $argument);
            break;
        case 'rcpt':
            $this->onRcpt($clientId, $argument);
            break;
        case 'data':
            $this->onData($clientId);
            break;
        case 'rset':
            $this->onRset($clientId);
            break;
        case 'noop':
            $this->onNoop($clientId);
            break;
        case 'quit':
            $this->onQuit($clientId);
            break;
        default:
            if ($this->isDebugCommand($command)) {
                return $this->debug($clientId);
            } else {
                $this->response->setCode(502);
                $this->response->setMessage('Error: command not recognized');
                return $this->reply($clientId);
            }
        }
    }

    // }}}
    // {{{ setPlugin()

    /**
     * @param object $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;

        $this->plugin->setServer($this->_server);
        $this->plugin->setContext($this->context);
        $this->plugin->setResponse($this->response);
    }

    // }}}
    // {{{ useDebugCommand()

    /**
     * @param string $command
     */
    public function useDebugCommand($command)
    {
        if ($command) {
            $this->debugCommand = $command;
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ onHelo()

    /**
     * @param integer $clientId
     * @param string  $data
     */
    protected function onHelo($clientId, $data = null)
    {
        if (!$data) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: HELO hostname');
            return $this->reply($clientId);
        }

        $this->context->reset();

        $this->response->setCode(250);
        $this->response->setMessage($this->_server->domain);

        if ($this->plugin) {
            $this->plugin->onHelo($clientId);
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ onMail()

    /**
    * @param integer $clientId
    * @param string  $data
     */
    protected function onMail($clientId, $data = null)
    {
        if ($this->context->getSender()) {
            $this->response->setCode(503);
            $this->response->setMessage('nested MAIL command');
            return $this->reply($clientId);
        }

        if (!preg_match('/^from:[ ]*(.+)$/i', $data, $matches)) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: MAIL FROM:<address>');
            return $this->reply($clientId);
        }

        $address = $this->normalizeAddress($matches[1]);
        if (!$address) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: MAIL FROM:<address>');
            return $this->reply($clientId);
        }

        $this->context->setSender($address);

        $this->response->setCode(250);
        $this->response->setMessage('Ok');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onRcpt()

    /**
     * @param integer $clientId
     * @param string  $data
     */
    protected function onRcpt($clientId, $data = null)
    {
        if (!$this->context->getSender()) {
            $this->response->setCode(503);
            $this->response->setMessage('Error: need MAIL command');
            return $this->reply($clientId);
        }

        if (!preg_match('/^to:[ ]*(.+)$/i', $data, $matches)) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: RCPT TO:<address>');
            return $this->reply($clientId);
        }

        $address = $this->normalizeAddress($matches[1]);
        if (!$address) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: RCPT TO:<address>');
            return $this->reply($clientId);
        }

        $this->context->addRecipient($address);

        $this->response->setCode(250);
        $this->response->setMessage('Ok');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onData()

    /**
     * @param integer $clientId
     */
    protected function onData($clientId)
    {
        if (!count($this->context->getRecipients())) {
            $this->response->setCode(503);
            $this->response->setMessage('Error: need RCPT command');
            return $this->reply($clientId);
        }

        $this->context->setDataState(true);

        $this->response->setCode(354);
        $this->response->setMessage('End data with <CR><LF>.<CR><LF>');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onDataReceived()

    /**
     * @param integer $clientId
     * @param string  $data
     */
    protected function onDataReceived($clientId, $data)
    {
        if (!preg_match('/^\.$/', $data)) {
            $this->context->addDataLine($data);
            return;
        }

        $this->context->reset();

        $this->response->setCode(250);
        $this->response->setMessage('Ok');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onRset()

    /**
     * @param integer $clientId
     */
    protected function onRset($clientId)
    {
        $this->context->reset();

        $this->response->setCode(250);
        $this->response->setMessage('Ok');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onNoop()

    /**
     * @param integer $clientId
     */
    protected function onNoop($clientId)
    {
        $this->response->setCode(250);
        $this->response->setMessage('Ok');
        $this->reply($clientId);
    }

    // }}}
    // {{{ onQuit()

    /**
     * @param integer $clientId
     */
    protected function onQuit($clientId)
    {
        $this->response->setCode(220);
        $this->response->setMessage('Bye');
        $this->reply($clientId);

        $this->_server->closeConnection();
    }

    // }}}
    // {{{ reply()

    /**
     * @param integer $clientId
     */
    protected function reply($clientId)
    {
        $this->_server->sendData($clientId, $this->response->getData());
    }

    // }}}
    // {{{ parseReceiveData()

    /**
     * @param string $data
     * @return array
     */
    protected function parseReceiveData($data)
    {
        $command = null;
        $argument = null;

        if (preg_match('/^[a-zA-Z]+$/', $data)) {
            $command = strtolower($data);
        } else {
            preg_match('/^([a-zA-Z]+)[ ]+(.*)$/', $data, $matches);
            $command  = strtolower($matches[1]);
            $argument = $matches[2];
        }

        return array($command, $argument);
    }

    // }}}
    // {{{ normalizeAddress()

    /**
     * @param string $address
     * @return string
     */
    protected function normalizeAddress($address)
    {
        if (!preg_match('/^<(.*)>$/', $address, $matches)) {
            return $address;
        }

        return $matches[1];
    }

    // }}}
    // {{{ debug()

    /**
     * @param integer $clientId
     */
    protected function debug($clientId)
    {
        $this->_server->sendData($clientId, serialize($this->context));
    }

    // }}}
    // {{{ isDebugCommand()

    /**
     * @param string $command
     * @return boolean
     */
    protected function isDebugCommand($command)
    {
        if (!$this->debugCommand) {
            return false;
        }

        return strtolower($this->debugCommand) === strtolower($command);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
