<div id="chat-messages" class="text-white p-4 flex flex-col-reverse gap-2 overflow-y-auto wrap-break-word bg-[#1b1429DD] rounded-lg max-h-[500px]">
    <div :foreach="$messages ?? [] as $message" class="flex gap-2 items-start text-shadow-gray-700 text-shadow-md">
        <span><span class="font-bold" style="color: {{ $message->color }}">{{ $message->user }}</span> {{ $message->content }}</span>
    </div>
</div>