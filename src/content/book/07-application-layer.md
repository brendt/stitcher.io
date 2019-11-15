Back in chapter 1, I stated that one of the characteristics of domain oriented Laravel projects is the following:

> […] most important is that you start thinking in groups of related business concepts, rather than in groups of code with the same technical properties.

Group your code based on the concepts it resembles in the real world, instead of their technical properties.

We also identified that domain groups and applications are two separate things, applications can use the domain, several groups at once if they want to; to expose the domain functionality to the end user.

What exactly belongs in this application layer? How do we group code over there? These questions will be answered in this chapter. 

We're entering the application layer.

## Several applications

The first important thing to understand is that one project can have several applications. In fact, every Laravel project already has two by default: the HTTP- and console apps. Still there are several more pieces of your project that can be thought of as a standalone app: every third party in


```
Admin
├── <hljs purple>Controllers</hljs>
│   ├── <hljs textgrey>█████████</hljs>
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████</hljs>
│   │   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████</hljs>
│   │   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>███</hljs>
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>
│   │   │   ├── <hljs textgrey>█████████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████████████████████</hljs>.php
│   │   │   └── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   │   ├── <hljs blue>Invoices</hljs>
│   │   │   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   │   ├── <hljs blue>IgnoreMissedInvoicesController</hljs>.php
│   │   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   │   ├── <hljs blue>InvoiceStatusController</hljs>.php
│   │   │   ├── <hljs blue>InvoicesController</hljs>.php
│   │   │   ├── <hljs blue>MissedInvoicesController</hljs>.php
│   │   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   │   └── <hljs blue>RefreshMissedInvoicesController</hljs>.php
│   │   ├── <hljs textgrey>████████</hljs>
│   │   │   └── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   └── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   └── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████</hljs>
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   │   └── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████</hljs>
│   │   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>██████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████</hljs>.php
│   │   ├── <hljs textgrey>████████████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████████</hljs>.php
│   │   ├── <hljs textgrey>███████████████</hljs>.php
│   │   └── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████</hljs>
│   │   └── <hljs textgrey>████████████████</hljs>.php
│   └── <hljs textgrey>███████████████</hljs>.php
├── <hljs darkblue>Filters</hljs>
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs blue>InvoiceMonthFilter</hljs>.php
│   ├── <hljs blue>InvoiceOfferFilter</hljs>.php
│   ├── <hljs blue>InvoiceStatusFilter</hljs>.php
│   ├── <hljs blue>InvoiceYearFilter</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   └── <hljs textgrey>███████████████████</hljs>.php
├── <hljs grey>Middleware</hljs>
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs blue>EnsureValidHabitantInvoiceCollectionSettingsMiddleware</hljs>.php
│   ├── <hljs blue>EnsureValidInvoiceDraftSettingsMiddleware</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs blue>EnsureValidOwnerInvoiceCollectionSettingsMiddleware</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   └── <hljs textgrey>█████████████████</hljs>.php
├── <hljs cyan>Queries</hljs>
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs blue>InvoiceCollectionIndexQuery</hljs>.php
│   ├── <hljs blue>InvoiceIndexQuery</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   └── <hljs textgrey>███████████████</hljs>.php
├── <hljs yellow>Requests</hljs>
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs blue>InvoiceRequest</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>███████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>███████████</hljs>.php
│   └── <hljs textgrey>████████████████████████</hljs>.php
├── <hljs green>Resources</hljs>
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs blue>Invoices</hljs>
│   │   ├── <hljs blue>InvoiceCollectionDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceCollectionResource</hljs>.php
│   │   ├── <hljs blue>InvoiceDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceDraftResource</hljs>.php
│   │   ├── <hljs blue>InvoiceLineDataResource</hljs>.php
│   │   ├── <hljs blue>InvoiceLineResource</hljs>.php
│   │   ├── <hljs blue>InvoiceResource</hljs>.php
│   │   ├── <hljs textgrey>██████████████████</hljs>.php
│   │   ├── <hljs textgrey>█████████████████</hljs>.php
│   │   └── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs blue>InvoiceIndexResource</hljs>.php
│   ├── <hljs blue>InvoiceLabelResource</hljs>.php
│   ├── <hljs blue>InvoiceMainOverviewResource</hljs>.php
│   ├── <hljs blue>InvoiceeResource</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>███████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████</hljs>.php
│   ├── <hljs textgrey>████████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>██████████████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████</hljs>.php
│   ├── <hljs textgrey>█████████████████████</hljs>.php
│   ├── <hljs textgrey>███████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   ├── <hljs textgrey>████████████████</hljs>.php
│   ├── <hljs textgrey>████████████</hljs>.php
│   └── <hljs textgrey>█████████████████████</hljs>.php
└── <hljs red>ViewModels</hljs>
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>
    │   ├── <hljs textgrey>████████████████</hljs>.php
    │   ├── <hljs textgrey>█████████████████</hljs>.php
    │   ├── <hljs textgrey>██████████████████</hljs>.php
    │   └── <hljs textgrey>███████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs blue>InvoiceCollectionHabitantContractPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceCollectionOwnerContractPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceCollectionPreviewViewModel</hljs>.php
    ├── <hljs blue>InvoiceDraftViewModel</hljs>.php
    ├── <hljs blue>InvoiceIndexViewModel</hljs>.php
    ├── <hljs blue>InvoiceLabelsViewModel</hljs>.php
    ├── <hljs blue>InvoiceStatusViewModel</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████</hljs>.php
    ├── <hljs textgrey>██████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>███████████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>███████████████</hljs>.php
    ├── <hljs textgrey>██████████████</hljs>.php
    ├── <hljs textgrey>████████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>█████████████</hljs>.php
    ├── <hljs textgrey>██████████████████████</hljs>.php
    ├── <hljs textgrey>█████████</hljs>.php
    └── <hljs textgrey>█████████████████</hljs>.php
```

```
Admin
└── <hljs blue>Invoices</hljs>
    ├── <hljs purple>Controllers</hljs>
    │   ├── IgnoreMissedInvoicesController.php
    │   ├── InvoiceStatusController.php
    │   ├── InvoicesController.php
    │   ├── MissedInvoicesController.php
    │   └── RefreshMissedInvoicesController.php
    ├── <hljs darkblue>Filters</hljs>
    │   ├── InvoiceMonthFilter.php
    │   ├── InvoiceOfferFilter.php
    │   ├── InvoiceStatusFilter.php
    │   └── InvoiceYearFilter.php
    ├── <hljs grey>Middleware</hljs>
    │   ├── EnsureValidHabitantInvoiceCollectionSettingsMiddleware.php
    │   ├── EnsureValidInvoiceDraftSettingsMiddleware.php
    │   └── EnsureValidOwnerInvoiceCollectionSettingsMiddleware.php
    ├── <hljs cyan>Queries</hljs>
    │   ├── InvoiceCollectionIndexQuery.php
    │   └── InvoiceIndexQuery.php
    ├── <hljs yellow>Requests</hljs>
    │   └── InvoiceRequest.php
    ├── <hljs green>Resources</hljs>
    │   ├── InvoiceCollectionDataResource.php
    │   ├── InvoiceCollectionResource.php
    │   ├── InvoiceDataResource.php
    │   ├── InvoiceDraftResource.php
    │   ├── InvoiceIndexResource.php
    │   ├── InvoiceLabelResource.php
    │   ├── InvoiceLineDataResource.php
    │   ├── InvoiceLineResource.php
    │   ├── InvoiceMainOverviewResource.php
    │   ├── InvoiceResource.php
    │   └── InvoiceeResource.php
    └── <hljs red>ViewModels</hljs>
        ├── InvoiceCollectionHabitantContractPreviewViewModel.php
        ├── InvoiceCollectionOwnerContractPreviewViewModel.php
        ├── InvoiceCollectionPreviewViewModel.php
        ├── InvoiceDraftViewModel.php
        ├── InvoiceIndexViewModel.php
        ├── InvoiceLabelsViewModel.php
        └── InvoiceStatusViewModel.php
```
