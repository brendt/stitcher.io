<html lang="en">
<head>
    <title>{{ $title }}</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Blog/Book/pdf.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Blog/Book/pdf-ui.entrypoint.css"/>
    <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
    <script>
        class TocPageNumberHandler extends Paged.Handler {
            constructor(chunker, polisher, caller) {
                super(chunker, polisher, caller);
                this.tocIndex = 0;
            }

            afterPageLayout(pageFragment) {
                const chapterTitle = pageFragment.querySelector('.chapter-title');

                if (!chapterTitle) {
                    return;
                }

                const offset = parseInt(
                    document.body.getAttribute('page-offset') ?? document.body.getAttribute(':page-offset') ?? '0',
                    10,
                );

                const pageNumber = parseInt(pageFragment.getAttribute('data-page-number'), 10) + offset;
                const tocPageNumbers = document.querySelectorAll('.toc-page-number');
                const tocPageNumber = tocPageNumbers[this.tocIndex];

                if (tocPageNumber) {
                    tocPageNumber.textContent = pageNumber.toString();
                }

                this.tocIndex++;
            }
        }

        Paged.registerHandlers(TocPageNumberHandler);
    </script>
</head>
<body :page-offset="$pageOffset">

<!--<section class="chapter cover clean">-->
<!--    <img src="/img/front.png">-->
<!--</section>-->

<!--<section class="chapter intro clean">-->
<!--    <div>-->
<!--        <div>-->
<!--            <h1>-->
<!--                stitcher.io — the book-->
<!--            </h1>-->
<!--            <span class="author">Brent Roose</span>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<section class="chapter credits clean">
    <div class="revision">
        <em>"Opinions are my own"</em> — Me<br/>
        https://stitcher.io<br/>
        Revision {{ \Tempest\DateTime\DateTime::now()->format('YYY-MM-dd') }}<br/>
        &copy; {{ \Tempest\DateTime\DateTime::now()->format('YYY') }} Brent Roose
    </div>
</section>

<section :foreach="$toc as $tocPage => $tocChapter">
    <h1 :if="$tocPage === 0" class="chapter-title mb-4">Table of Contents</h1>

    <a :foreach="$tocChapter as $tocChapter" class="toc-item" :href="'#' . $tocChapter->slug">
        <span class="toc-title">{{ $tocChapter->title }}</span>
        <span class="toc-divider"></span>
        <span class="toc-page-number">0</span>
    </a>
</section>

<x-chapter :foreach="$chapters as $chapter"/>

<!--<section class="chapter cover">-->
<!--    <img src="/img/back.png">-->
<!--</section>-->

</body>
</html>
