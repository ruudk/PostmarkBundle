<?php

/*
 * This file is part of the RuudkPostmarkBundle package.
 *
 * (c) Ruud Kamphuis <ruudk@mphuis.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ruudk\PostmarkBundle\Job;

use BCC\ResqueBundle\ContainerAwareJob;
use Ruudk\PostmarkBundle\Postmark\Message;
use Ruudk\PostmarkBundle\Postmark\Postmark;

class SendJob extends ContainerAwareJob
{
    /**
     * @var \Ruudk\PostmarkBundle\Postmark\Transport
     */
    protected $transport;

    /**
     * @param string  $queueName
     * @param Message $message
     * @return SendJob
     */
    public static function create($queueName, Message $message)
    {
        $sendJob = new self();
        $sendJob->queue = $queueName;
        $sendJob->args = array(
            'message' => serialize($message)
        );

        return $sendJob;
    }

    /**
     * @param \Ruudk\PostmarkBundle\Postmark\Transport $transport
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param array $args
     */
    public function run($args)
    {
        if($this->transport === null) {
            $this->transport = $this->getContainer()->get('ruudk_postmark.transport');
        }

        /**
         * @var \Ruudk\PostmarkBundle\Postmark\Message $message
         */
        $message = unserialize($args['message']);

        $this->transport->send($message);
    }
}