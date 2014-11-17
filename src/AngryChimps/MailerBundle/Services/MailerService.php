<?php


namespace AngryChimps\MailerBundle\Services;


class MailerService {
    /** @var  \Swift_Mailer */
    protected $mailer;

    public function __construct(Swift_mailer $mailer) {
        $this->mailer = $mailer;
    }

} 