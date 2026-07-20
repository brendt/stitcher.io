<?php

use Tempest\Mail\EmailAddress;
use Tempest\Mail\Transports\Smtp\SmtpMailerConfig;
use Tempest\Mail\Transports\Smtp\SmtpScheme;

use function Tempest\env;

$defaultSender = null;
$senderName = env('MAIL_SENDER_NAME');
$senderEmail = env('MAIL_SENDER_EMAIL');

if (is_string($senderName) && $senderName !== '' && is_string($senderEmail) && $senderEmail !== '') {
    $defaultSender = new EmailAddress(
        email: $senderEmail,
        name: $senderName,
    );
}

$smtpScheme = env('MAIL_SMTP_SCHEME', default: 'smtp');
$scheme = strtolower(is_string($smtpScheme) ? $smtpScheme : 'smtp');

return new SmtpMailerConfig(
    scheme: match ($scheme) {
        'smtps' => SmtpScheme::SMTPS,
        'smtp' => SmtpScheme::SMTP,
        default => throw new InvalidArgumentException(sprintf('Unsupported SMTP scheme "%s". Supported schemes: smtp, smtps.', $scheme)),
    },
    host: env('MAIL_SMTP_HOST', default: '127.0.0.1'),
    port: env('MAIL_SMTP_PORT', default: 2525),
    username: env('MAIL_SMTP_USERNAME', default: ''),
    password: env('MAIL_SMTP_PASSWORD', default: ''),
    defaultSender: $defaultSender,
);
