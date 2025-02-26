This file is used to record the integration with our core application & laravel

# What has already been done

* Initialized the laravel application.
* Delegated the handling of session to laravel
* Delegated the user credential verification to laravel.
* Configured the laravel application on the route `/v3` - You can run `php artisan v3:link` to install the route for you.
* Implemented the Metronic 8 theme.
* Rewrote the system permissions and groups. Now, all of the permissions are stored in a App\Permissions object and the sections are stored in a App\PermissionGroups object instead of global array

# Background:
Since, the laravel work in an isolated container (Service Container),
It is easy to use the laravel without affecting the legacy core.
We can just use the service container a.k.a IoC container of laravel
and with the correct composer configuration, we can autoload the files
that we require at any part of the application.

So that is how the integration is done at the moment.

## How does the laravel work?
The laravel works: as mentioned previously - using the IoC container.
So the application itself is configured inside the container. In practice,
this means we can use the modules like `validation`, `migration`, `artisan`,
`model`, `events`, `jobs`, `notification`, `worker_queue`, `pub_sub`,
`routes`, `session`, `mail` and more inside the legacy code as part of the switching.
since all of them will be available after the application is booted up.

The things that we will not be able to use inside the legacy code
is the handling of `request` & `response` isolation, `middleware` and things that
laravel use to handle the specific request. However, we can still use the request
object that the laravel constructs.

Why we are not able to use these things? Lets get to that now.


## How does the legacy code work & why we are limited ?
The legacy code relies on the routing provided by apache webserver, unlike laravel
where the routing is handled by laravel itself.

In the case of laravel, all of it is handled by a single page: the index.php inside
the public directory.

The legacy code - since it relies on the apache, it have multiple entry points.

to have a visualization this is a graphical analysis of the routes in
the legacy code

```
├── sales
│   ├── allocations
│   │   ├── customer_allocate.php
│   │   ├── customer_allocation_main.php
│   │   └── index.php
│   ├── cafeteria.php
│   ├── create_recurrent_invoices.php
│   ├── credit_note_entry.php
│   ├── export_from_url
│   │   └── index.php
│   ├── functions.php
│   ├── includes
│   │   ├── db
│   │   ├── index.php
│   │   └── ui
│   ├── index.php
│   ├── inquiry
│   │   ├── account_balance_report.php
│   │   ├── account_balances.php
│   │   ├── categorywise_customer_report.php
│   │   ├── categorywise_employee_report.php
│   │   ├── categorywise_sales_inquiry.php
│   │   ├── credit_invoice_inquiry.php
│   │   ├── credit_request_list.php
```
As you can see foreach specific route we have a file residing in the
corresponding directory. in this case to go to a route for example
say `sales/inquiry/account_balances.php` there needs to be a file physically
located literally in the directory sales/inquiry.

However in case of laravel, all of the routes are handled inside the application.
so everything goes to public/index.php and then it is handled from inside.

## Then how can we integrate the laravel? we would have to do this in all files no? that's a lot of duplication. don't you think?

Well not exactly. why? because in almost all the files requires the user to
be logged in. So literally everywhere - one of the files that is being
included is the session.inc at the earliest possible. So whenever a page is
being accessed, we are including the session.inc to bootstrap the user's session.

So we take advantage of the file and initialize laravel there - giving us the ability
to use laravel's service container inside the legacy code.