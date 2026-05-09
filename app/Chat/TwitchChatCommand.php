<?php

namespace App\Chat;

use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\env;

final class TwitchChatCommand
{
    use HasConsole;

    private const IRC_SERVER = 'irc.chat.twitch.tv';
    private const IRC_PORT = 6667;

    public function __construct(
        private ChatStorage $chatStorage,
    ) {}

    #[ConsoleCommand('chat:twitch')]
    public function __invoke(): void
    {
        $channel = env('TWITCH_CHANNEL');
        $this->info("Connecting to Twitch chat for #{$channel}...");

        $socket = fsockopen(self::IRC_SERVER, self::IRC_PORT, $errno, $errstr, 10);

        if ($socket === false) {
            $this->error("Failed to connect: {$errstr}");
            return;
        }

        stream_set_timeout($socket, 300); // 5 min timeout for reads

        // Anonymous login
        $nick = 'justinfan' . random_int(10000, 99999);
        fwrite($socket, "NICK {$nick}\r\n");
        fwrite($socket, "USER {$nick} 8 * :{$nick}\r\n");

        $joined = false;

        while (true) {
            $line = fgets($socket, 1024);

            if ($line === false) {
                $info = stream_get_meta_data($socket);
                if ($info['timed_out']) {
                    // Send a ping to keep connection alive
                    fwrite($socket, "PING :keepalive\r\n");
                    continue;
                }
                $this->error('Connection lost');
                break;
            }

            // Respond to PING
            if (str_starts_with($line, 'PING')) {
                fwrite($socket, "PONG :tmi.twitch.tv\r\n");
                continue;
            }

            // Join channel after welcome message (001)
            if (str_contains($line, '001') && !$joined) {
                fwrite($socket, "JOIN #{$channel}\r\n");
                $joined = true;
                $this->success("Joined #{$channel}");
                continue;
            }

            // Parse PRIVMSG format: :username!username@username.tmi.twitch.tv PRIVMSG #channel :message
            if (preg_match('/:(\w+)!\w+@\w+\.tmi\.twitch\.tv PRIVMSG #\w+ :(.+)/', $line, $matches)) {
                $user = $matches[1];

                $message = new Message(
                    user: $user,
                    content: trim($matches[2]),
                    platform: 'twitch',
                    timestamp: new DateTimeImmutable(),
                    color: $this->chatStorage->getUserColor($user),
                );

                $this->chatStorage->appendMessage($message);
                $this->writeln("[{$message->user}] {$message->content}");
            }
        }

        fclose($socket);
    }
}