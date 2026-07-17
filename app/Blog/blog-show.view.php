<x-base :meta="$post->meta ?? null" :title="$post->title">
    <x-container class="grid gap-4">
        <x-menu />

        <div class="relative">
            <div class="mb-4 xl:mb-0 xl:absolute xl:left-full xl:top-0 xl:ml-6 xl:w-44 xl:h-full">
                <div class="xl:sticky xl:top-8">
                    <span class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Sponsors</span>

                    <div class="flex gap-3 flex-wrap xl:flex-col">
                        <x-sponsor href="/click/ploi">
                            Deploy your next server in a few clicks: <span class="underline group-hover:no-underline text-[#5b8bfb] font-bold">ploi.io</span>
                        </x-sponsor>

<!--                        <x-sponsor :if="random_int(0, 1) === 1" href="/click/tdw1">-->
<!--                            Turn slow pages into fast fixes with <strong class="text-[#001f4d]">Tideways</strong> performance insights. <span class="underline group-hover:no-underline">Start trial →</span>-->
<!--                        </x-sponsor>-->
<!--                        <x-sponsor :else href="/click/tdw2">-->
<!--                            <strong class="text-[#001f4d]">Tideways</strong>: from slow request to root cause in minutes. <span class="underline group-hover:no-underline">Start trial →</span>-->
<!--                        </x-sponsor>-->
                    </div>
                </div>
            </div>

            <x-article>
                <div>
                    <h1 class="text-4xl font-bold text-primary">{{ $post->title }}</h1>
                    <small class="pl-1">Written on {{ $post->date->format('YYYY-MM-dd') }}</small>
                </div>

                {!! $post->content !!}
            </x-article>
        </div>

        <x-footer class="mt-4">
            <x-button href="/">Back</x-button>
            <x-button :if="$post->next ?? null" :href="$post->next->uri">Up next: {{ $post->next->title }}</x-button>
        </x-footer>

        <x-book-card title="Things I wish I knew when I started programming" img="/img/static/books/things-cover.png" href="https://things-i-wish-i-knew.com/">
            <p>
                This is my newest book aimed at programmers of any skill level. This book isn't about patterns, principles, or best practices; there's actually barely any code in it. It's about the many things I've learned along the way being a professional programmer, and about the many, many mistakes I made along that way as well. It's what I wish someone would have told me years ago, and I hope it might inspire you.
            </p>
        </x-book-card>

        <x-card :post="$post">
            <h2 hx-trigger="load" :hx-get="'/blog/' . $post->slug . '/comments'" hx-target="#comments">Comments</h2>
            <x-comments :post="$post" :initial="true" />
        </x-card>
    </x-container>
</x-base>
