We're working on a large API project at [Spatie](*https://www.spatie.be) in which UUIDs are used instead of normal ones.
The benefit of UUIDs is that they're unique over your whole database, and that you're not exposing internal functionality to the outside.

Our first approach was to store the UUID as `VARCHAR(36)` variables in the database. 
There were some performance concerns though.
Some people pointed out that querying large amounts of data based on UUIDs had a serious performance cost.
That's because of the random nature of a UUID, making it impossible for MySQL to efficiently index that data.

We found some interesting reads about optimised UUIDs for database searches. 
By saving the UUIDs as binary data, and shifting some bits around, MySQL could do a much better indexing job.
You can read an explanation of the hows and whys here: [http://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/](*http://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/).

Before making changes to our codebase though, we needed to be sure it would impact our use case.
I've made some benchmarks to verify this "optimised UUID" claim, you can check the repository out here:
[https://github.com/spatie/uuid-mysql-performance](*https://github.com/spatie/uuid-mysql-performance).

Sure thing, optimised UUIDs make a difference! You can read more in-depth conclusions in the repository's README, 
and run the benchmarks yourself.

---

This optimisation was something completely different than the area I was comfortable with, but it was so much fun figuring this out!
I was also happy to see that there would almost be no impact on our existing codebase, 
we can gracefully add this feature and not worry about breaking changes or data migrations.
