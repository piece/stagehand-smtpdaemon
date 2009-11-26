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

// {{{ Stagehand_PHP_SmtpDaemon_PluginTest

/**
 * Some tests for Stagehand_SmtpDaemon_Plugin
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemon_PluginTest extends Stagehand_SmtpDaemonTest
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $port = 9125;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    /**
     * @test
     */
    public function attachToConnect()
    {
        $this->connect();

        $this->assertTrue($this->connection);
        $this->assertEquals($this->getReply(), "221 attached to connection\r\n");
    }

    /**
     * @test
     */
    public function attachToHelo()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->assertEquals($this->getReply(), "251 attached to helo\r\n");
    }

    /**
     * @test
     */
    public function attachToMail()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->getReply();

        $this->send("MAIL from:foo@example.com\r\n");
        $this->assertEquals($this->getReply(), "251 attached to mail\r\n");

        $context = $this->debug();

        $this->assertEquals($context->getSender(), 'foo@example.com');
    }

    /**
     * @test
     */
    public function attachToRcpt()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->getReply();

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT to:bar@example.com\r\n");
        $this->assertEquals($this->getReply(), "251 attached to rcpt\r\n");

        $context = $this->debug();
        $recipients = $context->getRecipients();

        $this->assertEquals(count($recipients), 1);
        $this->assertEquals($recipients[0], 'bar@example.com');
    }

    /**
     * @test
     */
    public function attachToData()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->getReply();

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT to:bar@example.com\r\n");
        $this->getReply();

        $this->send("DATA\r\n");
        $this->assertEquals($this->getReply(), "354 attached to data\r\n");

        $context = $this->debug();

        $this->assertTrue($context->isDataState());
    }

    /**
     * @test
     */
    public function attachToDataReceived()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->getReply();

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT to:bar@example.com\r\n");
        $this->getReply();

        $this->send("DATA\r\n");
        $this->getReply();

        $this->send("foo\r\n");
        $this->send("bar\r\n");
        $this->send(".\r\n");

        $this->assertEquals($this->getReply(), "251 attached to data received\r\n");
    }

    /**
     * @test
     */
    public function attachToRset()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("RSET\r\n");
        $this->assertEquals($this->getReply(), "251 attached to rset\r\n");
    }

    /**
     * @test
     */
    public function attachToNoop()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("Noop\r\n");
        $this->assertEquals($this->getReply(), "251 attached to noop\r\n");
    }

    /**
     * @test
     */
    public function attachToQuit()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("QUIT\r\n");
        $this->assertEquals($this->getReply(), "221 attached to quit\r\n");
    }

    /**
     * @test
     */
    public function disallowInMailByPlugin()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO localhost\r\n");
        $this->getReply();

        $this->send("MAIL from:ng@example.com\r\n");
        $this->assertEquals($this->getReply(), "421\r\n");

        $context = $this->debug();

        $this->assertNull($context->getSender());
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
