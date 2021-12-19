<?php

declare(strict_types=1);

namespace App\Utils\Notifications;

use App\Service\AwsCliService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class SnsService
{
    public function __construct(
        private LoggerInterface $logger,
        private AwsCliService $cli,
        private string $notificationSnsTopicArn,
    ) {
        if (pattern('^(arn:aws:sns:[-a-z0-9]+:\d+:[-_a-z0-9]+)?$', 'i')->fails($notificationSnsTopicArn)) {
            throw new InvalidArgumentException("$notificationSnsTopicArn is not a valid SNS topic ARN");
        }
    }

    public function send(Notification $notification): bool
    {
        if ('' === $this->notificationSnsTopicArn) {
            $this->logger->warning('Unable to send SNS notification - the URL is not configured');

            return false;
        }

        return $this->cli->execute(['aws', 'sns', 'publish', '--topic-arn', $this->notificationSnsTopicArn,
            '--subject', $notification->subject, '--message', $notification->contents, ], "Sending SNS notification: $notification->subject");
    }
}
