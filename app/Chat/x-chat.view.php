<?php $modal ??= false ?>

<div
        id="chat-messages"
        class="text-white p-4 gap-2 wrap-break-word flex flex-col"
        :class="$modal ? 'bg-[#1b1429DD] overflow-y-auto rounded-lg max-h-[500px]' : 'bg-[#1b1429] h-full overflow-y-auto'"
>
    <div :foreach="$messages ?? [] as $message" class="flex gap-2 items-start text-shadow-gray-700 text-shadow-md">
        <span><span class="font-bold" style="color: {{ $message->color }}">{{ $message->user }}</span> {{ $message->content }}</span>
    </div>
</div>
