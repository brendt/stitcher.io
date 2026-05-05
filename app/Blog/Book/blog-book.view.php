<html lang="en">
<head>
    <title>{{ $title }}</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <script>
        function createTocItem(title, pageNumber) {
            const row = document.createElement('div');
            row.classList.add('toc-row');
            //
            // const chapterDiv = document.createElement('div');
            // chapterDiv.classList.add('toc-chapter');
            // chapterDiv.innerHTML = title.match(/^\d+/)[0];
            // row.appendChild(chapterDiv);

            const titleDiv = document.createElement('div');
            titleDiv.classList.add('toc-title');
            titleDiv.innerHTML = title;
            row.appendChild(titleDiv);

            const leaderDiv = document.createElement('div');
            leaderDiv.classList.add('toc-leader');
            leaderDiv.innerHTML = '';
            row.appendChild(leaderDiv);

            const pageNumberDiv = document.createElement('div');
            pageNumberDiv.classList.add('toc-page');
            pageNumberDiv.innerHTML = pageNumber;
            row.appendChild(pageNumberDiv);

            return row;
        }
    </script>

    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Blog/Book/pdf.entrypoint.css"/>
    <x-vite-tags entrypoint="app/Blog/Book/pdf-ui.entrypoint.css"/>
    <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>

    <script>
        class MyHandler extends Paged.Handler {
            constructor(chunker, polisher, caller) {
                super(chunker, polisher, caller);
            }

            afterPageLayout(pageFragment, page) {
                const toc = document.getElementById('toc');

                if (!toc) {
                    return;
                }

                const title = pageFragment.querySelector('.chapter-title');

                if (!title) {
                    return;
                }

                const offset = document.body.getAttribute('page-offset');

                const pageNumber = parseInt(pageFragment.getAttribute('data-page-number')) - offset;

                toc.appendChild(createTocItem(title.innerHTML, pageNumber));
            }
        }

        Paged.registerHandlers(MyHandler);
    </script>
</head>
<body :page-offset="6">

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

<!--<section class="chapter credits clean">-->
<!--    <div class="revision">-->
<!--        <em>"Opinions are my own"</em> — Me<br/>-->
<!--        https://stitcher.io<br/>-->
<!--        Revision {{ \Tempest\DateTime\DateTime::now()->format('YYY-MM-dd') }}<br/>-->
<!--        &copy; {{ \Tempest\DateTime\DateTime::now()->format('YYY') }} Brent Roose-->
<!--    </div>-->
<!--</section>-->

<!--<section class="chapter toc clean">-->
<!--    <h1>Table of Contents</h1>-->
<!--    <div id="toc"></div>-->
<!--</section>-->

<x-chapter :foreach="$chapters as $chapter"/>

<!--<section class="chapter cover">-->
<!--    <img src="/img/back.png">-->
<!--</section>-->

</body>
</html>