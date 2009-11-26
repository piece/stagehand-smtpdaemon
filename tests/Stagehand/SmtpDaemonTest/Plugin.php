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

// {{{ Stagehand_PHP_SmtpDaemonTest_Plugin

/**
 * A plugin class for Stagehand_SmtpDaemonTest
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemonTest_Plugin
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected static $instance;
    protected $server;
    protected $context;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ getInstance()

    /**
    * @return Stagehand_SmtpDaemonTest_Plugin
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Stagehand_SmtpDaemonTest_Plugin();
        }

        return self::$instance;
    }

    // }}}
    // {{{ setServer()

    /**
    * @param object $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    // }}}
    // {{{ setContext()

    /**
    * @param object $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    // }}}
    // {{{ setResponse()

    /**
    * @param object $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    // }}}
    // {{{ onConnect()

    /**
     * @param integer $clientId
     */
    public function onConnect($clientId = 0)
    {
        $this->response->setCode(221);
        $this->response->setMessage('attached to connection');
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

        $this->server->sendData($clientId, $result);
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
