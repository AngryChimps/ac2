<?php

namespace AngryChimps\MailerBundle\Messages;

class BasicMessage extends \Swift_Message {
    /** @var  string */
    protected static $generic_from;

    /**
     * Create a new Message.
     *
     * Details may be optionally passed into the constructor.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     */
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {
        parent::__construct($subject, $body, $contentType, $charset);

        $this->setFrom(self::$generic_from);
    }

    public static function setGenericFrom($address) {
        self::$generic_from = $address;
    }

}