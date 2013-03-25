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

use Symfony\Component\HttpFoundation\File\File;

class Message
{
    /**
     * @var array
     */
    protected $payload = array();

    /**
     * @var array
     */
    protected $to = array();

    /**
     * @var array
     */
    protected $cc = array();

    /**
     * @var array
     */
    protected $bcc = array();

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string      $email
     * @param string|null $name
     * @return $this
     */
    public function setFrom($email, $name = null)
    {
        if(!empty($name)) {
            $this->payload['From'] = sprintf("%s <%s>",
                $name,
                $email
            );
        } else {
            $this->payload['From'] = $email;
        }

        return $this;
    }

    public function addTo($email, $name = null)
    {
        if(!empty($name)) {
            $this->to[] = sprintf("%s <%s>",
                $name,
                $email
            );
        } else {
            $this->to[] = $email;
        }

        $this->payload['To'] = implode(",", $this->to);

        return $this;
    }

    public function addCc($email, $name = null)
    {
        if(!empty($name)) {
            $this->cc[] = sprintf("%s <%s>",
                $name,
                $email
            );
        } else {
            $this->cc[] = $email;
        }

        $this->payload['Cc'] = implode(",", $this->cc);

        return $this;
    }

    public function addBcc($email, $name = null)
    {
        if(!empty($name)) {
            $this->bcc[] = sprintf("%s <%s>",
                $name,
                $email
            );
        } else {
            $this->bcc[] = $email;
        }

        $this->payload['Bcc'] = implode(",", $this->bcc);

        return $this;
    }

    public function setReplyTo($email, $name = null)
    {
        if(!empty($name)) {
            $this->payload['ReplyTo'] = sprintf("%s <%s>",
                $name,
                $email
            );
        } else {
            $this->payload['ReplyTo'] = $email;
        }

        return $this;
    }

    public function setSubject($subject)
    {
        $this->payload['Subject'] = $subject;

        return $this;
    }

    public function setTextBody($body)
    {
        $this->payload['TextBody'] = $body;

        return $this;
    }

    public function setHtmlBody($body)
    {
        $this->payload['HtmlBody'] = $body;

        return $this;
    }

    public function setTag($tag)
    {
        $this->payload['Tag'] = $tag;

        return $this;
    }

    public function setHeader($name, $value)
    {
        if(!isset($this->payload['Headers'])) {
            $this->payload['Headers'] = array();
        }

        $this->payload['Headers'][] = array(
            'Name'  => $name,
            'Value' => $value
        );

        return $this;
    }

    /**
     * Add attachment
     *
     * @param File $file
     * @param string $filename  null
     * @param string $mimeType  null
     * @return Message
     */
    public function addAttachment(File $file, $filename = null, $mimeType = null)
    {
        if(empty($filename)) {
            $filename = $file->getFilename();
        }

        if(empty($mimeType)) {
            $mimeType = $file->getMimeType();
        }

        if(!isset($this->payload['Attachments'])) {
            $this->payload['Attachments'] = array();
        }

        $this->payload['Attachments'][] = array(
            'Name'        => $filename,
            'Content'     => base64_encode(file_get_contents($file->getRealPath())),
            'ContentType' => $mimeType
        );

        return $this;
    }
}