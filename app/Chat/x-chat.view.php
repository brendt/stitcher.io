<div class="text-white p-4 flex flex-col gap-2 overflow-clip wrap-break-word">
    <div :foreach="$messages ?? [] as $message" class="flex gap-2 items-start">
        <span><span class="font-bold" style="color: {{ $message->color }}">{{ $message->user }}</span> {{ $message->content }}</span>
    </div>
</div>