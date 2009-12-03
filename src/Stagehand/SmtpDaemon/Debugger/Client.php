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

// {{{ Stagehand_SmtpDaemon_Debugger_Client

/**
 * Stagehand_SmtpDaemon_Debugger_Client
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemon_Debugger_Client
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
    // {{{ __construct()

    /**
     * @param resource $socket
     * @param string $command
     */
    public function __construct($socket, $command = 'debug')
    {
        $this->socket = $socket;
        $this->command = $command;
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
    // {{{ getContext()

    /**
     * @return mixed
     */
    public function getContext()
    {
        $data = $this->command . "\r\n";

        if (!socket_write($this->socket, $data, strlen($data))) {
            return;
        }

        $reply = $this->getReplyData();
        if (!$reply) {
            return;
        }

        return unserialize($reply);
    }

    public function getReplyData()
    {
        $result = null;

        $response = socket_recv($this->socket, $result, 4096, 0);
        if ($response === false) {
            $message = socket_strerror(socket_last_error());
            throw new Stagehand_SmtpDaemon_Debugger_Exception($message);
        }

        return $result;
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
