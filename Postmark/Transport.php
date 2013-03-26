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
use Buzz\Exception\ClientException;
use Ruudk\PostmarkBundle\Postmark\Exception\PostmarkException;

class Transport
{
    /**
     * @var \Buzz\Client\Curl
     */
    protected $curl;

    /**
     * Postmark API token
     *
     * @var string
     */
    protected $token;

    /**
     * @param string $token
     */
    public function __construct(Curl $curl, $token)
    {
        $this->curl = $curl;
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

        if(count($data) === 1) {
            return $this->post('email', $data[0]);
        } elseif(count($data) > 500) {
            $results = array();
            $chunks = array_chunk($data, 500);
            foreach($chunks AS $chunk) {
                $results += $this->post('email/batch', $chunk);
            }
            return $results;
        } else {
            return $this->post('email/batch', $data);
        }
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    protected function post($path, array $data)
    {
        try {
            $browser = new Browser($this->curl);

            /**
             * @var \Buzz\Message\Response $response
             */
            $response = $browser->post('https://api.postmarkapp.com/' . $path, array(
                'X-Postmark-Server-Token' => $this->token,
                'Content-Type'            => 'application/json',
                'Accept'                  => 'application/json'
            ), json_encode($data));

            $json = json_decode($response->getContent(), true);

            if($json['ErrorCode'] > 0) {
                if($json['ErrorCode'] !== 406) {
                    /**
                     * ErrorCode 406 means Inactive recipient, no need to throw an exception for that.
                     * For the rest, throw an exception.
                     */
                    throw new PostmarkException($json['Message'], $json['ErrorCode']);
                }
            }

            return $json;
        } catch(ClientException $exception) {
            throw new PostmarkException($exception->getMessage(), $exception->getCode(), $exception);
        } catch(\Exception $exception) {
            throw new PostmarkException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}