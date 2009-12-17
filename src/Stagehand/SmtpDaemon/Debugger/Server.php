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
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_SmtpDaemon_Debugger_Server

/**
 * Stagehand_SmtpDaemon_Debugger_Server
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemon_Debugger_Server
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $handler;
    protected $command;

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
     * @param object $handler
     * @param string $command
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    // }}}
    // {{{ isDebugCommand()

    /**
     * @param string $command
     * @return boolean
     */
    public function isDebugCommand($command)
    {
        if (!$command || !$this->command) {
            return false;
        }

        $parts = explode(' ', strtolower($command));
        if (count($parts) < 2) {
            return false;
        }

        if (strtolower($this->command) !== $parts[0]) {
            return false;
        }

        if ($parts[1] !== 'context'
            && $parts[1] !== 'response'
            ) {
            return false;
        }

        return true;
    }

    // }}}
    // {{{ setCommand()

    /**
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    // }}}
    // {{{ debug()

    /**
     * @param integer $clientId
     * @param string $command
     */
    public function debug($clientId, $command)
    {
        $parts = explode(' ', strtolower($command));

        switch ($parts[1]) {
        case 'context':
            $this->dumpContext($clientId);
            break;
        case 'response':
            $this->dumpResponse($clientId);
            break;
        default:
            break;
        }
    }

    // }}}
    // {{{ dumpContext()

    /**
     * @param integer $clientId
     */
    public function dumpContext($clientId)
    {
        $context = $this->handler->getContext();
        $this->handler->_server->sendData($clientId, serialize($context));
    }

    // }}}
    // {{{ dumpResponse()

    /**
     * @param integer $clientId
     */
    public function dumpResponse($clientId)
    {
        $response = $this->handler->getResponse();
        $this->handler->_server->sendData($clientId, serialize($response));
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
