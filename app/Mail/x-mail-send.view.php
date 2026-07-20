<?php

use App\Mail\MailController;

use function Tempest\Router\uri;

$sent ??= false;

?>

<div
    id="mail-send"
    :hx-get="uri([MailController::class, 'status'], slug: $mail->slug)"
    hx-trigger="every 1s"
    hx-target="this"
    hx-swap="outerHTML"
    class="inline-grid"
>
<button
    :if="$campaign === null"
    :hx-post="uri([MailController::class, 'send'], slug: $mail->slug)"
    hx-target="#mail-send"
    hx-swap="outerHTML"
    hx-confirm="Are you sure you want to send this mail?"
    class="text-center bg-primary rounded-full text-white font-bold shadow-sm underline hover:no-underline hover:shadow-lg p-3 px-5 cursor-pointer"
>
    Send
</button>
<span :elseif="$campaign->processedAt === null" class="text-center bg-blue-200 rounded-full  font-bold p-3 px-5">
        Processing…
</span>
<span :elseif="$campaign->isSending" class="text-center bg-blue-200 rounded-full  font-bold p-3 px-5">
    Sending: {{ $campaign->sentCount }} / {{ $campaign->totalCount }}
</span>
<span :else class="text-center bg-green-200 rounded-full  font-bold p-3 px-5">
    Done: {{ $campaign->sentCount }} / {{ $campaign->totalCount }}
</span>
</div>
