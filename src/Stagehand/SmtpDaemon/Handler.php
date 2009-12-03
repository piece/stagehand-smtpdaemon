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
    protected $response;
    protected $debugger;

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
        $this->debugger = new Stagehand_SmtpDaemon_Debugger_Server($this);
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

        if ($this->debugger->isDebugCommand($data)) {
            return $this->debugger->debug($clientId, $data);
        }

        if ($this->context->isDataState()) {
            $this->onDataReceived($clientId, $data);
            return;
        }

        list($command, $argument) = $this->parseReceiveData($data);

        $this->response->setCode(554);
        $this->response->setMessage('Transaction failed');

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
            $this->response->setCode(502);
            $this->response->setMessage('Error: command not recognized');
            return $this->reply($clientId);
        }
    }

    // }}}
    // {{{ getContext()

    /**
     * @return object
     */
    public function getContext()
    {
        return $this->context;
    }

    // }}}
    // {{{ getResponse()

    /**
     * @return object
     */
    public function getResponse()
    {
        return $this->response;
    }

    // }}}
    // {{{ useDebugCommand()

    /**
     * @param string $command
     */
    public function useDebugCommand($command)
    {
        $this->debugger->setCommand($command);
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
        if ($this->validateHeloHost($data)) {
            $this->context->reset();
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ validateHeloHost()

    /**
     * @param string $domain
     * @return boolean
     */
    protected function validateHeloHost($host)
    {
        if (!$host) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: HELO hostname');
            return false;
        }

        $this->response->setCode(250);
        $this->response->setMessage($this->_server->domain);

        return true;
    }

    // }}}
    // {{{ onMail()

    /**
    * @param integer $clientId
    * @param string  $data
     */
    protected function onMail($clientId, $data = null)
    {
        $sender = null;
        if (preg_match('/^from:[ ]*(.+)$/i', $data, $matches)) {
            $sender = $this->normalizeAddress($matches[1]);
        }

        if ($this->validateSender($sender)) {
            $this->context->setSender($sender);
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ validateSender()

    /**
     * @param string $sender
     * @return boolean
     */
    protected function validateSender($sender)
    {
        if ($this->context->getSender()) {
            $this->response->setCode(503);
            $this->response->setMessage('nested MAIL command');
            return false;
        }

        if (!$sender) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: MAIL FROM:<address>');
            return false;
        }

        $this->response->setCode(250);
        $this->response->setMessage('Ok');

        return true;
    }

    // }}}
    // {{{ onRcpt()

    /**
     * @param integer $clientId
     * @param string  $data
     */
    protected function onRcpt($clientId, $data = null)
    {
        $recipient = null;
        if (preg_match('/^to:[ ]*(.+)$/i', $data, $matches)) {
            $recipient = $this->normalizeAddress($matches[1]);
        }

        if ($this->validateRecipient($recipient)) {
            $this->context->addRecipient($recipient);
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ validateRecipient()

    /**
     * @param string $recipient
     * @return boolean
     */
    protected function validateRecipient($recipient)
    {
        if (!$this->context->getSender()) {
            $this->response->setCode(503);
            $this->response->setMessage('Error: need MAIL command');
            return false;
        }

        if (!$recipient) {
            $this->response->setCode(501);
            $this->response->setMessage('Syntax: RCPT TO:<address>');
            return false;
        }

        $this->response->setCode(250);
        $this->response->setMessage('Ok');

        return true;
    }

    // }}}
    // {{{ onData()

    /**
     * @param integer $clientId
     */
    protected function onData($clientId)
    {
        if ($this->validateDataEvent()) {
            $this->context->setDataState(true);
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ validateDataEvent()

    /**
     * @return boolean
     */
    protected function validateDataEvent()
    {
        if (!count($this->context->getRecipients())) {
            $this->response->setCode(503);
            $this->response->setMessage('Error: need RCPT command');
            return false;
        }

        $this->response->setCode(354);
        $this->response->setMessage('End data with <CR><LF>.<CR><LF>');

        return true;
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

        if ($this->processDataReceived()) {
            $this->context->reset();
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ processDataReceived()

    /**
     * @return boolean
     */
    protected function processDataReceived()
    {
        $this->response->setCode(250);
        $this->response->setMessage('Ok');

        return true;
    }

    // }}}
    // {{{ onRset()

    /**
     * @param integer $clientId
     */
    protected function onRset($clientId)
    {
        if ($this->validateReset()) {
            $this->context->reset();
        }

        $this->reply($clientId);
    }

    // }}}
    // {{{ validateReset()

    /**
     * @return boolean
     */
    protected function validateReset()
    {
        $this->response->setCode(250);
        $this->response->setMessage('Ok');

        return true;
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
