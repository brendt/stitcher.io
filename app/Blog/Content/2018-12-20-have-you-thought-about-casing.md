---
title: 'Have you thought about casing?'
next: comparing-dates
meta:
    description: "Readable code shouldn't be underestimated."
---

<p id="uncamel-button"></p>

I've made the argument [many](/blog/where-a-curly-bracket-belongs) [times](/blog/visual-perception-of-code) [before](/blog/a-programmers-cognitive-load):
programmers shouldn't underestimate the value of code readability.
Today I'll argue once more against a common practice we all seem to take for granted: casing.

It seems like such a minor detail, right?
Using camels or snakes or kebabs to string words together, who cares?
There's more to this question though. 

Code readability shouldn't be neglected. 
It impacts how easy you can navigate and _understand_ your code.
We should talk about the casing conventions we use.

## A little history

Early programming languages didn't have the same conventions we have today.
Languages like Lisp and COBOL originated before widespread ASCII support,
explaining the difference.
Upper- and lower case, and special characters like underscores 
simply weren't supported by compilers back in the 50s and early 60s.

Both Lisp and COBOL allowed for hyphens to split words.
Lisp's parser was smart enough to detect whether a hyphen was between two words, 
or whether it should be used as the subtraction operator. 
COBOL only has full words as operators, eliminating the problem altogether. 
Here's a subtraction in COBOL:

```txt
SUBTRACT data-item-1 FROM data-item-2
```

Because the hyphen isn't a reserved keyword, it can be used to split words.

When programming languages matured in the 80s and 90s, 
it became clear that the hyphen should be reserved for mathematical operations. 
Another issue with Lisp's smart approach was that it didn't scale in modern languages, 
it slowed down tokenisation significantly.

Spaces obviously could never be used, 
as almost every programming language uses them as the boundary between tokens.
So what's left? How could we write multiple words as one, while keeping these words readable?

## Conventions today

This is why we're left with two major conventions today: camel case, either lower- or upper; and snake case.
As a sidenote: upper camel case is also called pascal case.

Most of the time, a language tends to favourite one of the two casings.
We _could_ say it's a matter of community guidelines, and be done with it.

It's my opinion that there's more to it. 
There is one better, more readable way of writing words. 
You can probably guess which one, based on the start of this blog post.
camel case makes text more difficult to read, compared to snake case.

Given the word `user id`, compare the two ways of writing it:

```txt
userId
user_id
```

It's true: camel case is more compact: you don't have to write as much.
But which style is the closest to how the human brain actually reads a text?

This is the only argument that matters to me in this discussion:

> How can we make it as easy as possible for our brain to read and understand code?

Readable code, reduces cognitive load. 
Less cognitive load means more memory space for humans to think about other things,
things like writing business logic.

"All of that just by using underscores?"
No, not just because of underscores.
There's much more to writing readable code than naming conventions.
But all small things help in getting a bigger solution.

<div id="camelcase-end"></div>

<script>
    /**/
    const blog = document.querySelector('.blog');
    const startDiv = blog.querySelector('#uncamel-button');
    const endDiv = blog.querySelector('#camelcase-end');
    const normalParagraphs = blog.querySelectorAll('.blog > p');
    const otherElements = blog.querySelectorAll('.blog > *:not(p):not(script):not(h1):not(aside)');
    const camelParagraphs = [];

    for (let normalParagraph of normalParagraphs) {
        const camelParagraph = document.createElement('p');

        camelParagraph.innerHTML = camelize(normalParagraph.textContent);
        camelParagraph.style.cssText = 'display: block; word-break: break-all;';
        normalParagraph.style.cssText = 'display: none;';

        endDiv.append(camelParagraph);

        camelParagraphs.push(camelParagraph);
    }
    
    for (let otherElement of otherElements) {
        if (otherElement.getAttribute('id') === 'camelcase-end') {
            continue;
        }
        
        otherElement.style.cssText = 'display:none;';
    }

    startDiv.append(createButton());
    startDiv.style.cssText = 'margin-bottom: 1em;font-size:1em; text-align:center;'

    function createButton() {
        const uncamelButton = document.createElement('button');

        uncamelButton.classList.add('cta');
        uncamelButton.classList.add('cta-light');

        uncamelButton.innerHTML = 'Uncammelise';

        uncamelButton.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            for (let normalParagraph of normalParagraphs) {
                normalParagraph.style.cssText = 'display: block;';
            }

            for (let camelParagraph of camelParagraphs) {
                camelParagraph.style.cssText = 'display: none;';
            }
    
            for (let otherElement of otherElements) {
                otherElement.style.cssText = 'display:block;';
            }

            uncamelButton.style.cssText = 'display: none;';
        });

        return uncamelButton;
    }

    function camelize(str) {
        return str.replace(/(?:^\w|[A-Z]|\b\w|\s+)/g, function (match, index) {
            if (+match === 0) {
                return "";
            }

            return match.toUpperCase();
        });
    }
    /**/
</script>
