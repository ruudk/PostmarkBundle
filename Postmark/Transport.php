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

use Buzz\Browser;
use Buzz\Client\Curl;

class Transport
{
    /**
     * Postmark API token
     *
     * @var string
     */
    protected $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param Message|array $input Array of messages or just one message
     * @return mixed
     */
    public function send($input)
    {
        if($input instanceof Message) {
            /**
             * @var \Ruudk\PostmarkBundle\Postmark\Message $input
             */
            return $this->post('email', $input->getPayload());
        }

        $data = array();
        foreach($input AS $message) {
            /**
             * @var \Ruudk\PostmarkBundle\Postmark\Message $message
             */
            $data[] = $message->getPayload();
        }

        return $this->post('email/batch', $data);
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    protected function post($path, array $data)
    {
        $browser = new Browser(new Curl());

        /**
         * @var \Buzz\Message\Response $response
         */
        $response = $browser->post('https://api.postmarkapp.com/' . $path, array(
            'X-Postmark-Server-Token' => $this->token,
            'Content-Type'            => 'application/json',
            'Accept'                  => 'application/json'
        ), json_encode($data));

        $json = json_decode($response->getContent(), true);

        return $json;
    }
}