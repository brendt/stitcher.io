Hi there! It's time for my biyearly summary of [which PHP versions are used across the community](https://aggregate.stitcher.io/links/7f1b0d9b-8210-48e3-89c7-68a63a5b9725)!

Twice a year, I look at composer's download statistics and crunch that data into some nice graphs. It's of course important to note that composer only represents a part of the PHP community, so this data isn't a 100% accurate representation of PHP as a whole, but it gives an interesting look at PHP nevertheless. 

You can [read the full post here](https://aggregate.stitcher.io/links/7f1b0d9b-8210-48e3-89c7-68a63a5b9725), but I also wanted to share some key points in this email.

In this edition, I was pleasantly surprised to see a significant growth in PHP 8.1 adoption. I was wondering how PHP 8.1 adoption would relate to PHP 7.1 adoption when it was released, and I think it's fair to say there's a significant difference.

<p>
<a href="https://aggregate.stitcher.io/links/7f1b0d9b-8210-48e3-89c7-68a63a5b9725">
<img width="550" alt="All time evolution" src="https://user-images.githubusercontent.com/6905297/211761869-704ae03f-d64f-4ab2-a1d7-7b7248f69e88.png">
</a>
</p>

This chart shows the version evolution over time per version, and you can see how the green line of PHP 8.1 rises, and compare it to blue PHP 7.1 line back in 2016. There's a 7% difference between the two, which I would say is a significant number! 

Another interesting takeaway is that more than 25% of projects are still running PHP 7.4 — which isn't supported anymore, as of last year.

<p>
<a href="https://aggregate.stitcher.io/links/7f1b0d9b-8210-48e3-89c7-68a63a5b9725">
<img width="550" alt="Evolution of version usage" src="https://user-images.githubusercontent.com/6905297/211761886-4e48f994-52d8-43f4-a180-4687052ee29f.png">
</a>
</p>

This data beautifully visualizes the division within the PHP community: one part is keeping up with modern PHP, while another one stays helplessly behind. I know there are many reasons to stay behind — often driven by business requirements and constraints — but it's crucial to realise that a lot of PHP projects are in fact running insecure and slow versions in production because of it.

One big misconception I often hear is how difficult it is to upgrade to modern PHP versions. In my experience, it has never been as easy as it is today. We now have tools like [Rector](https://aggregate.stitcher.io/links/e2e3bd0a-e0fd-4bd8-916e-42021972dc9b) to automate the actual upgrade, and static analysers like [PHPStan](https://aggregate.stitcher.io/links/6aa87aba-8bb9-4eb1-a5dd-3939a5f4b555) that'll let you know if anything's wrong after upgrading.

I really believe we need to embrace these tools, much more than we already do today.

I'm doing more than just shouting it from the rooftops though: I sat down and upgraded all of my projects to PHP 8.2 already. I documented the process for all of you to see:

<p>
<a href="https://aggregate.stitcher.io/links/ffc3dfac-c4b4-49b2-a9d4-47e602b3d93b">
<img width="550" alt="" src="https://stitcher.io/resources/img/static/aggregate-82-thumb.png">
</a>
</p>

From what I can tell, the upgrade went super smooth, and I'm really happy to already be on PHP 8.2.

How about you? Are you already on PHP 8.2? What's holding you back? Let me know!

Until next time!

Brent