Adapters are used in page configuration files to change the way pages are rendered. An adapter will take the page's 
 configuration and change or adapt it into one or more new configurations. Currently, these adapters are supported: 
 pagination, collection, order, filter and limit.
 
Adapters can be easily added via the plugin system (we'll discuss plugins further down the line). Custom adapters offer 
 the possibility to add your own logic before rendering a page. An example would be grouping these guide pages by category,
 which is done with a custom adapter.

Each adapter has its own configuration and will be discussed in the next chapters. Note that not all adapters can be 
 combined. The pagination and collection adapter cannot be set for the same page. Furthermore, the order of adapters is 
 also important. Data manipulation adapters should be configured before the pagination or collection adapters. This is 
 because these last two will create multiple pages based on one page configuration file. More info on these adapters 
 will follow.
