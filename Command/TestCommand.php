<?php

namespace Ruudk\PostmarkBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;

class TestCommand extends Command
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getKernel()->getContainer();
    }

    protected function configure()
    {
        $this->setName('postmark:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Ruudk\PostmarkBundle\Postmark\Postmark $postmark
         */
        $postmark = $this->container->get('ruudk_postmark.postmark');

        $message = $postmark->compose();
        $message->addTo('ruudk@mphuis.com');
        $message->setSubject('Test onderwerp');
        $message->setTextBody('Test');

        $postmark->delayed()->send($message);
    }
}