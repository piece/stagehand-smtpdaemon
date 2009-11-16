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

// {{{ Stagehand_PHP_SmtpDaemon_HandlerTest

/**
 * Some tests for Stagehand_SmtpDaemon_Handler
 *
 * @package    Stagehand_SmtpDaemon
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_SmtpDaemon_HandlerTest extends Stagehand_SmtpDaemonTest
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

    /**
     * @test
     */
    public function connectDaemon()
    {
        $this->connect();

        $this->assertTrue($this->connection);
        $this->assertEquals($this->getReply(), "220 localhost\r\n");
    }

    /**
     * @test
     */
    public function commandHelo()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("HELO\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: HELO hostname\r\n");

        $this->send("HELO localhost\r\n");
        $this->assertEquals($this->getReply(), "250 localhost\r\n");
    }

    /**
     * @test
     */
    public function commandMail()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("MAIL\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: MAIL FROM:<address>\r\n");

        $this->send("MAIL foo\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: MAIL FROM:<address>\r\n");

        $this->send("MAIL from:\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: MAIL FROM:<address>\r\n");

        $this->send("MAIL from:<>\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: MAIL FROM:<address>\r\n");

        $this->send("MAIL from:<foo@example.com>\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();

        $this->assertEquals($context->getSender(), 'foo@example.com');

        $this->send("MAIL from:<foo@example.com>\r\n");
        $this->assertEquals($this->getReply(), "503 nested MAIL command\r\n");
    }

    /**
     * @test
     */
    public function commandRcpt()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("RCPT\r\n");
        $this->assertEquals($this->getReply(), "503 Error: need MAIL command\r\n");

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: RCPT TO:<address>\r\n");

        $this->send("RCPT foo\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: RCPT TO:<address>\r\n");

        $this->send("RCPT to:\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: RCPT TO:<address>\r\n");

        $this->send("RCPT to:<>\r\n");
        $this->assertEquals($this->getReply(), "501 Syntax: RCPT TO:<address>\r\n");

        $this->send("RCPT to:<bar@example.com>\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();
        $recipients = $context->getRecipients();

        $this->assertEquals(count($recipients), 1);
        $this->assertEquals($recipients[0], 'bar@example.com');

        $this->send("RCPT to:<baz@example.com>\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();
        $recipients = $context->getRecipients();

        $this->assertEquals(count($recipients), 2);
        $this->assertEquals($recipients[0], 'bar@example.com');
        $this->assertEquals($recipients[1], 'baz@example.com');
    }

    /**
     * @test
     */
    public function commandData()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("DATA\r\n");
        $this->assertEquals($this->getReply(), "503 Error: need RCPT command\r\n");
        $context = $this->debug();
        $this->assertFalse($context->isDataState());

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("DATA\r\n");
        $this->assertEquals($this->getReply(), "503 Error: need RCPT command\r\n");
        $context = $this->debug();
        $this->assertFalse($context->isDataState());

        $this->send("RCPT to:baz@example.com\r\n");
        $this->getReply();

        $this->send("DATA\r\n");
        $this->assertEquals($this->getReply(), "354 End data with <CR><LF>.<CR><LF>\r\n");
        $context = $this->debug();
        $this->assertTrue($context->isDataState());
    }

    /**
     * @test
     */
    public function sendContentsData()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT to:baz@example.com\r\n");
        $this->getReply();

        $this->send("DATA\r\n");
        $this->getReply();

        $this->send("A first line\r\n");
        $this->send("and second.\r\n");
        $this->send(".\r\n");

        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();
        $data = $context->getData();

        $this->assertEquals($data,
                            'A first line' . PHP_EOL .
                            'and second.'  . PHP_EOL
                            );
    }

    /**
     * @test
     */
    public function commandRset()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("RSET\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();

        $this->assertNull($context->getSender());
        $this->assertEquals(count($context->getRecipients()), 0);

        $this->send("MAIL from:foo@example.com\r\n");
        $this->getReply();

        $this->send("RCPT to:bar@example.com\r\n");
        $this->getReply();

        $context = $this->debug();

        $this->assertEquals($context->getSender(), 'foo@example.com');
        $this->assertEquals(count($context->getRecipients()), 1);

        $this->send("RSET\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");

        $context = $this->debug();

        $this->assertNull($context->getSender());
        $this->assertEquals(count($context->getRecipients()), 0);
    }

    /**
     * @test
     */
    public function commandNoop()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $this->send("NOOP\r\n");
        $this->assertEquals($this->getReply(), "250 Ok\r\n");
    }

    /**
     * @test
     */
    public function commandQuit()
    {
        $this->connect();
        $this->assertTrue($this->connection);
        $this->getReply();

        $context = $this->debug();

        $this->send("QUIT\r\n");
        $this->assertEquals($this->getReply(), "220 Bye\r\n");
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
