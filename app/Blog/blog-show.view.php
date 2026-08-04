<x-base :meta="$post->meta ?? null" :title="$post->title">
    <x-container class="grid gap-4">
        <x-menu/>

        <div class="relative">
            <div :if="$post->showSections" class="xl:absolute xl:right-full xl:top-0 xl:mr-6 xl:w-52 xl:h-full hidden xl:block">
                <div class="xl:sticky xl:top-8">
                    <span class="text-xs text-gray-400 uppercase tracking-widest block mb-2">In this post</span>

                    <div class="squircle-sm grid gap-2 min-w-48 bg-white px-4 py-5 shadow-sm rounded-xs text-sm">
                        <a :foreach="$post->sections as $uri => $title" :href="$uri" class="hover:underline decoration-primary decoration-2">{!! $title !!}</a>
                    </div>
                </div>
            </div>
            <div class="mb-1 sm:mb-3 xl:mb-0 xl:absolute xl:left-full xl:top-0 xl:ml-6 xl:w-44 xl:h-full">
                <div class="xl:sticky xl:top-8">
                    <span class="text-xs text-gray-400 uppercase tracking-widest block mb-2">Sponsors</span>

                    <div class="flex gap-1 sm:gap-3 flex-wrap xl:flex-col">
                        <?php
                        $sponsors = [
                            '/click/ploi' => 'Deploy your next server in a few clicks: <span class="underline group-hover:no-underline text-[#5b8bfb] font-bold">ploi.io</span>',
                            '/click/ohdear' => '<span class="text-[#ff3901] font-bold">Oh Dear</span>: Health checks, scheduled tasks, uptime and SSL — all checked every minute, in one dashboard. <span class="text-[#ff3901] font-bold underline group-hover:no-underline">Start monitoring →</span>'
                        ];

                        if (random_int(0, 1) === 1) {
                            $sponsors['/click/tdw1'] = '<strong class="text-[#001f4d]">Tideways</strong>: Speed up your application with actionable performance insights. <span class="text-[#001f4d] font-bold underline group-hover:no-underline">Start trial →</span>';
                        } else {
                            $sponsors['/click/tdw2'] = '<strong class="text-[#001f4d]">Tideways</strong>: Performance insights for every request. <span class="text-[#001f4d] font-bold underline group-hover:no-underline">Start trial →</span>';
                        }

                        uksort($sponsors, fn ($a, $b) => random_int(-1, 1));
                        ?>

                        <x-template :foreach="$sponsors as $uri => $message">
                            <x-sponsor :href="$uri" :message="$message">
                                {!! $message !!}
                            </x-sponsor>
                        </x-template>
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
            <x-comments :post="$post" :initial="true"/>
        </x-card>
    </x-container>
</x-base>
