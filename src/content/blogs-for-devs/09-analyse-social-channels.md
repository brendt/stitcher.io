When I had been blogging for around 2 years, a simple thought occurred to me: I had written around 50 posts up until then, and most of them were still relevant today. Yet I didn't share the old ones anymore. I shared them once in the past, and that was it. I had lots of content laying around, and didn't do anything with it anymore. That had to change. 

While tools like Google Analytics give great insights within the context of your site, they give little information about how your content is doing on social channels like Reddit, Twitter or Hacker News. Those channels often only result in short hype-spikes, but there's lots of potential audience waiting for your content, they just need to discover it.

Success on those channels often depends on a number of factors, and not just the quality of your post: the time of day, the relevance to the community at the moment, even the time of year can have an influence. There's also the matter of reposts: people on the internet tend to forget about what they read rather quickly. If a post did well a year ago, chances are it'll do well again if you repost it. 

If I were to share all my content efficiently, even the older posts, I'd need more data to work with.

---

Being a developer, I did what I know best: write my own tools to help automate this process. I had a list of questions in my mind that I wanted to be answered:

- Which posts have I shared already, and where?
- Which types of posts were popular on which channels?
- Which posts might be up for a repost (eg. after a year or month, depending on the channel)?

My three main social channels are Twitter, Reddit (with several subreddits) and Hacker News. Those are the ones I focussed on when writing this tool. I wrote a little script that looks for my own submissions on Twitter and Reddit, and for links to my content on Hacker News. I synced them to a local data store, and generated some tables from them. Here's for example part of the Reddit analysis:

```txt
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| URL                                                         | Subreddit     | Title                                    | Score  | Date             |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/front-line-php                                        | r/PHP         | Front Line PHP                           | 0      | 2020-10-05 19:00 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/what-a-good-pr-looks-like                             | r/PHP         | Some guidelines on making good PRs, esp… | 8      | 2020-10-02 13:25 |
|                                                             | r/webdev      | What a good PR looks like                | 0      | 2020-10-15 13:17 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/shorthand-comparisons-in-php                          | r/PHP         | Looking at two basic operators: shortha… | 63     | 2017-11-20 13:44 |
|                                                             | r/PHP         | Shorthand comparisons in PHP, now also … | 74     | 2018-08-20 16:54 |
|                                                             | r/PHP         | A refresher about shorthand comparisons  | 6      | 2020-10-15 13:02 |
|                                                             | r/laravel     | Shorthand comparisons in PHP             | 63     | 2020-10-15 13:17 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/phpstorm-scopes                                       | r/PHP         | A tip for phpstorm users: coloured scop… | 149    | 2018-06-07 12:39 |
|                                                             | r/PHP         | Using file scopes in PhpStorm            | 87     | 2020-09-25 12:39 |
|                                                             | r/webdev      | Scopes in any JetBrains IDE              | 10     | 2020-09-26 16:39 |
|                                                             | r/javascript  | Scopes in any JetBrains IDE              | 116    | 2020-09-26 16:39 |
|                                                             | r/laravel     | Configuring PhpStorm scopes              | 29     | 2020-10-19 12:34 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/php-8-before-and-after                                | r/PHP         | PHP 8: before and after                  | ⭐️ 121 | 2020-07-20 17:35 |
|                                                             | r/laravel     | PHP 8: before and after                  | ⭐️ 50  | 2020-10-23 13:05 |
|                                                             | r/symfony     | PHP 8: before and after                  | 1      | 2020-10-23 13:05 |
|                                                             | r/webdev      | PHP 8: before and after                  | 13     | 2020-10-23 13:05 |
|                                                             | r/Wordpress   | PHP 8: before and after                  | 30     | 2020-10-23 13:06 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
| /blog/php-8-jit-setup                                       | r/PHP         | How to setup the JIT                     | 42     | 2020-11-04 18:17 |
+-------------------------------------------------------------+---------------+------------------------------------------+--------+------------------+
```

This table shows links to my content and the subreddits they were posted on, as well as their score. Based on this data I can decide whether it's worth reposting in the same subreddit, or whether I could share a specific post to a new one.

I also added some insights on my overall status per subreddit: the ratio of own content vs other and how much upvotes in total I received:

```txt
+---------------------+--------------+-------------+
| Subreddit           | own/all      | Total score |
+---------------------+--------------+-------------+
| r/podcasts          | 0/3 (0%)     | 44          |
| r/guineapigs        | 0/3 (0%)     | 153         |
| r/gaming            | 0/3 (0%)     | 24          |
| r/podcasting        | 0/4 (0%)     | 43          |
| r/AskReddit         | 0/4 (0%)     | 8           |
| r/Minecraft         | 0/6 (0%)     | 447         |
| r/symfony           | 6/7 (86%)    | 7           |
| r/MinecraftDungeons | 1/8 (13%)    | 385         |
| r/Blogging          | 0/11 (0%)    | 64          |
| r/Wordpress         | 10/12 (83%)  | 145         |
| r/javascript        | 5/19 (26%)   | 1040        |
| r/programming       | 25/36 (69%)  | 1413        |
| r/webdev            | 31/47 (66%)  | 749         |
| r/laravel           | 36/50 (72%)  | 1635        |
| r/PHP               | 76/251 (30%) | 13189       |
+---------------------+--------------+-------------+
```

Another example: Twitter. I really don't want to spam my followers by reporting. On the other hand, my follower base grows and grows, so there are always people who haven't seen my older tweets. It's about finding the right balance, and relying on data is a great way of doing so:

```txt
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| URL                                                         | Tweet                                    | Date                        | Likes  | Retweets |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/why-light-themes-are-better-according-to-science      | Light colour schemes are better, based … | Saturday, 2020-09-26 18:13  | ⭐️ 101 | 🔁 23    |
|                                                             | I wrote something new over the weekend.… | Monday, 2020-09-28 12:08    | ⭐️ 20  | 🔁 1     |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/front-line-php                                        | Front Line PHP: some backstory  https:/… | Monday, 2020-10-05 11:00    | ⭐️ 15  | 🔁 2     |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/what-a-good-pr-looks-like                             | It's @hacktoberfest! Here are some tips… | Friday, 2020-10-02 05:20    | ⭐️ 22  | 🔁 5     |
|                                                             | Have you already submitted your hacktob… | Friday, 2020-10-09 15:03    | ⭐️ 15  | 🔁 3     |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/php-8-before-and-after                                | #PHP8 before and after: the impact PHP … | Monday, 2020-07-20 09:34    | ⭐️ 205 | 🔁 79    |
|                                                             | Lots of things I'll change in my code o… | Wednesday, 2020-08-26 06:39 | ⭐️ 54  | 🔁 15    |
|                                                             | Oh how I look forward using all these s… | Friday, 2020-10-16 05:19    | ⭐️ 98  | 🔁 21    |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/constructor-promotion-in-php-8                        | Constructor property promotion in #PHP … | Friday, 2020-06-12 14:10    | ⭐️ 23  | 🔁 8     |
|                                                             | Constructor property promotion in #PHP … | Friday, 2020-06-12 14:10    | ⭐️ 23  | 🔁 8     |
|                                                             | I think promoted properties are my #1 f… | Monday, 2020-10-26 05:40    | ⭐️ 108 | 🔁 24    |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/php-8-in-8-code-blocks                                | PHP 8 in 8 code blocks https://t.co/2We… | Friday, 2020-05-15 05:58    | ⭐️ 244 | 🔁 96    |
|                                                             | RT @brendt_gd: PHP 8 in 8 code blocks h… | Friday, 2020-05-15 13:36    | ⭐️ 0   | 🔁 96    |
|                                                             | #PHP8 will be here next month, November… | Monday, 2020-10-26 09:57    | ⭐️ 53  | 🔁 8     |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
| /blog/php-8-jit-setup                                       | On the topic of PHP 8, if you ever need… | Wednesday, 2020-11-04 10:17 | ⭐️ 26  | 🔁 6     |
+-------------------------------------------------------------+------------------------------------------+-----------------------------+--------+----------+
```

There are of course tools that can do all of this for you, but I enjoy the freedom of being able to do whatever I want with my data, and not paying for it. Unfortunately I'm not able to share these tools with you at the moment, since they aren't built with reusability and configuration in mind, but that might change in the future; depending on whether there's enough interest in them or not. [Let me know](*https://twitter.com/brendt_gd)!

---

When I put this strategy to the test, I discovered a few posts with potential. Posts that I thought might do well on one channel or another. Sure thing, the first day I used this analyser, I already got a post going viral on Hacker News.

I don't check these stats every week, by the way. I figure it's most important to not spam your audience. Still having insights into 

<div class="sidenote">
<h2>In summary</h2>

- Social channels are one-time hype-spikes, but what if you could repeat them from time to time?
- You'll need ways to monitor how your content does on those channels.
- This kind of data is another source that you can use to determine what content is worth writing, and what not.
</div>

---

{{ cta:blogs_more }}
