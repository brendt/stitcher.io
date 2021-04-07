---

We're going to start with something that's non-blocking. Contrary to threads or processes, there's only ever one fiber running at the same time within a given PHP process. That means that asynchronous behaviour must come from somewhere else.

We're going to create two sockets that can communicate with each other, and can do so in a non-blocking way.

```php
[$read, $write] = <hljs prop>stream_socket_pair</hljs>(
    <hljs prop>STREAM_PF_UNIX</hljs>,
    <hljs prop>STREAM_SOCK_STREAM</hljs>,
    <hljs prop>STREAM_IPPROTO_IP</hljs>
);

// Set streams to non-blocking mode.
<hljs prop>stream_set_blocking</hljs>($read, false);
<hljs prop>stream_set_blocking</hljs>($write, false);
```

Let's take a minute to understand what `<hljs prop>stream_set_blocking</hljs>()` does exactly. It's a function that takes a stream object and that makes that stream "non-blocking". Imagine a file stream for example, say you want to write a big blob of data to a file. PHP isn't actually writing to that file directly, it's calling low-level OS functionality. It's passing that blob of data to the OS, which in turn writes it to the file. Let's for example say that it takes the OS ten seconds to write all that data (it's a large file!), PHP itself isn't doing anything during those ten seconds, it's waiting for the OS to finish its write operations.

Now image you have to write not only one but ten files, each taking ten seconds to save. In a blocking mode (the default one), PHP would wait ten times ten seconds. 

The non-blocking way, however, is to instruct the OS to start writing to ten files at the same time, and wait for them all at once to finish. This won't be done within ten seconds, mind you, there are physical limitations like how many cores are available and other low-level OS stuff, but you can be certain it'll take a lot less than the 100 seconds with the blocking way. 

Back to our example: instead of writing to a file, we've created two sockets that can communicate with each other in a non blocking way. One socket (the `$read` socket) is used from by the "parent process" and the other one (the `$write` socket) is used by individual fibers. Whenever a fiber writes something to a socket, it will arrive in the parent process, and writing between those sockets can be non-blocking as well. 

So think of it as on-the-fly data writing, without needing any files. 
