<?php

/*
 * This file is part of the RuudkPostmarkBundle package.
 *
 * (c) Ruud Kamphuis <ruudk@mphuis.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ruudk\PostmarkBundle\Tests\Postmark;

use BCC\ResqueBundle\Resque;
use Buzz\Client\Curl;
use Ruudk\PostmarkBundle\Postmark\Postmark;
use Ruudk\PostmarkBundle\Postmark\Transport;

class PostmarkTest extends \PHPUnit_Framework_TestCase
{
    protected $queueName = 'test_postmark';

    /**
     * @return Transport
     */
    protected function getTransport()
    {
        return new Transport(new Curl, 'POSTMARK_API_TEST');
    }

    /**
     * @return Postmark
     */
    protected function getPostmark()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../data/views');
        $environment = new \Twig_Environment($loader);

        $resque = new Resque(array(
            'kernel.root_dir'    => '',
            'kernel.debug'       => true,
            'kernel.environment' => 'dev'
        ));
        $resque->setRedisConfiguration('localhost', 6379, 0);

        $postmark = new Postmark($this->getTransport(), $environment, $resque, $this->queueName);

        return $postmark;
    }

    public function testDefaultFrom()
    {
        $postmark = $this->getPostmark();

        $message = $postmark->compose();
        $this->assertEmpty($message->getPayload());

        /**
         * Set the default from
         */
        $postmark->setFrom('test@gmail.com', 'My name');

        $message = $postmark->compose();
        $this->assertNotEmpty($message->getPayload());
        $payload = $message->getPayload();
        $this->assertEquals('My name <test@gmail.com>', $payload['From']);
    }

    public function testCompose()
    {
        $postmark = $this->getPostmark();

        $message = $postmark->compose();
        $message->setFrom('test@gmail.com', 'My name');
        $message->addTo('ruudk@mphuis.com', 'Ruud Kamphuis');
        $message->setSubject("Message 1");
        $message->setTextBody("PlainText");
        $message->setHtmlBody("Html");

        $result = $postmark->send($message);

        $this->assertEquals(0, $result['ErrorCode']);
        $this->assertEquals('Test job accepted', $result['Message']);
    }

    public function testComposeView()
    {
        $postmark = $this->getPostmark();

        $message = $postmark->compose('mail.html.twig', array(
            'subject' => 'My custom subject',
            'text'    => 'Text',
            'html'    => 'Html'
        ));
        $payload = $message->getPayload();

        $this->assertEquals("My custom subject", $payload['Subject']);
        $this->assertEquals("Body: Text", $payload['TextBody']);
        $this->assertEquals("Body: Html", $payload['HtmlBody']);
    }

    public function testQueueingAndBatch()
    {
        $postmark = $this->getPostmark();

        $message = $postmark->compose();
        $message->setFrom('test@gmail.com', 'My name');
        $message->addTo('ruudk@mphuis.com', 'Ruud Kamphuis');
        $message->setSubject("Message 1");
        $message->setTextBody("PlainText");
        $message->setHtmlBody("Html");

        $postmark->enqueue($message);

        $message = $postmark->compose();
        $message->setFrom('test@gmail.com', 'My name');
        $message->addTo('ruudk@mphuis.com', 'Ruud Kamphuis');
        $message->setSubject("Message 2");
        $message->setTextBody("PlainText");
        $message->setHtmlBody("Html");

        $postmark->enqueue($message);

        $this->assertCount(2, $postmark->getQueue());

        $result = $postmark->send();

        $this->assertCount(2, $result);
        $this->assertEquals(0, $result[0]['ErrorCode']);
        $this->assertEquals(0, $result[1]['ErrorCode']);
        $this->assertEquals('Test job accepted', $result[0]['Message']);
        $this->assertEquals('Test job accepted', $result[1]['Message']);
    }

    public function testDelayed()
    {
        $postmark = $this->getPostmark();

        $worker = new \Resque_Worker($this->queueName);
        $worker->registerWorker();

        $job = $worker->reserve();
        $this->assertFalse($job, 'The queue is not empty.');

        $message = $postmark->compose();
        $message->setFrom('test@gmail.com', 'My name');
        $message->addTo('ruudk@mphuis.com', 'Ruud Kamphuis');
        $message->setSubject("My delayed message");
        $message->setTextBody("PlainText");
        $message->setHtmlBody("Html");

        $postmark->delayed()->send($message);

        $job = $worker->reserve();
        $this->assertInstanceOf('\Resque_Job', $job);
        $this->assertEquals('Ruudk\PostmarkBundle\Job\SendJob', $job->payload['class']);
        $this->assertNotEmpty($job->payload['args'][0]['message']);

        $message = unserialize($job->payload['args'][0]['message']);
        $payload = $message->getPayload();
        $this->assertEquals('My delayed message', $payload['Subject']);

        /**
         * @var \Ruudk\PostmarkBundle\Job\SendJob $sendJob
         */
        $sendJob = $job->getInstance();
        $sendJob->setTransport($this->getTransport());

        /**
         * Run the job!
         */
        $job->perform();
    }
}
