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

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ onConnect()

    /**
     * @param integer $clientId
     */
    public function onConnect($clientId = 0)
    {
        $this->reply($clientId, 220, $this->_server->domain);
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
        $command = null;
        $argument = null;

        if (preg_match('/^[a-zA-Z]+$/', $data)) {
            $command = $data;
        } else {
            preg_match('/^([a-zA-Z]+)[ ]+(.*)$/', $data, $matches);
            $command  = $matches[1];
            $argument = $matches[2];
        }

        switch (strtolower($command)) {

        case 'helo':
            $this->onHelo($clientId, $argument);
            break;
        case 'quit':
            $this->onQuit($clientId);
            break;
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
            $this->reply($clientId, 501, 'Syntax: HELO hostname');
        }

        $this->reply($clientId, 250, $this->_server->domain);
    }

    // }}}
    // {{{ onQuit()

    /**
    * @param integer $clientId
     */
    protected function onQuit($clientId)
    {
        $this->reply($clientId, 220, 'Bye');
        $this->_server->closeConnection();
    }

    // }}}
    // {{{ reply()

    /**
    * @param integer $clientId
    * @param integer $code
    * @param string  $data
     */
    protected function reply($clientId, $code, $data = null)
    {
        if ($data) {
            $result = sprintf("%d %s\r\n", $code, $data);
        } else {
            $result = sprintf("%d\r\n", $code);
        }

        $this->_server->sendData($clientId, $result);
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
