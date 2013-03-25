<?php

/*
 * This file is part of the RuudkPostmarkBundle package.
 *
 * (c) Ruud Kamphuis <ruudk@mphuis.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ruudk\PostmarkBundle\Postmark;

use Ruudk\PostmarkBundle\Job\SendJob;
use Twig_Environment;
use BCC\ResqueBundle\Resque;

class Postmark
{
    /**
     * @var Transport
     */
    protected $transport;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \BCC\ResqueBundle\Resque
     */
    protected $resque;

    /**
     * @var string
     */
    protected $queueName = 'postmark';

    /**
     * @var bool
     */
    protected $delayed = false;

    /**
     * @var array
     */
    protected $queue = array();

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var string
     */
    protected $fromName;

    public function __construct(Transport $transport, Twig_Environment $twig, Resque $resque, $queueName)
    {
        $this->transport = $transport;
        $this->twig = $twig;
        $this->resque = $resque;
        $this->queueName = $queueName;
    }

    /**
     * @param string      $email
     * @param string|null $name
     */
    public function setFrom($email, $name = null)
    {
        $this->fromEmail = $email;

        if($name !== null) {
            $this->fromName = $name;
        }
    }

    /**
     * @param string $viewName
     * @param array $context
     * @return Message
     */
    public function compose($viewName = null, array $context = array())
    {
        $message = new Message($this);

        if($this->fromEmail !== null) {
            $message->setFrom($this->fromEmail, $this->fromName);
        }

        if($viewName !== null) {
            /**
             * @var \Twig_Template $template
             */
            $template = $this->twig->loadTemplate($viewName);

            if($template->hasBlock('subject')) {
                $message->setSubject(trim($template->renderBlock('subject', $context)));
            }

            if($template->hasBlock('html')) {
                $message->setHtmlBody(trim($template->renderBlock('html', $context)));
            }

            if($template->hasBlock('text')) {
                $message->setTextBody(trim($template->renderBlock('text', $context)));
            }
        }

        return $message;
    }

    /**
     * @param bool $delayed
     * @return \Ruudk\PostmarkBundle\Postmark\Postmark
     */
    public function delayed($delayed = true)
    {
        $this->delayed = $delayed;

        return $this;
    }

    public function send(Message $message = null)
    {
        if($message !== null) {
            if($this->delayed) {
                $this->resque->enqueue(SendJob::create($this->queueName, $message));
            } else {
                return $this->transport->send($message);
            }
        }

        if(empty($this->queue)) {
            return false;
        }

        if($this->delayed) {
            foreach($this->queue AS $message) {
                $this->resque->enqueue(SendJob::create($this->queueName, $message));
            }

            $this->queue = array();

            return 'queued';
        } else {
            $result = $this->transport->send($this->queue);

            $this->queue = array();

            return $result;
        }
    }

    /**
     * @param Message $message
     * @return Postmark
     */
    public function enqueue(Message $message)
    {
        $this->queue[] = $message;

        return $this;
    }

    /**
     * @return Message[]
     */
    public function getQueue()
    {
        return $this->queue;
    }
}