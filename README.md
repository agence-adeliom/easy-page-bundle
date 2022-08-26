
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-page-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-page-bundle)

# Easy Page Bundle

A basic CMS system for Easyadmin.

## Installation with Symfony Flex

Add our recipes endpoint

```json
{
  "extra": {
    "symfony": {
      "endpoint": [
        "https://api.github.com/repos/agence-adeliom/symfony-recipes/contents/index.json?ref=flex/main",
        ...
        "flex://defaults"
      ],
      "allow-contrib": true
    }
  }
}
```

Install with composer

```bash
composer require agence-adeliom/easy-page-bundle
```

## Versions

| Repository Branch | Version | Symfony Compatibility | PHP Compatibility | Status                     |
|-------------------|---------|-----------------------|-------------------|----------------------------|
| `2.x`             | `2.x`   | `5.4`, and `6.x`      | `8.0.2` or higher | New features and bug fixes |
| `1.x`             | `1.x`   | `4.4`, and `5.x`      | `7.2.5` or higher | No longer maintained       |


### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```

## Documentation

### Manage pages in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use App\Entity\EasyPage\Page;

class DashboardController extends AbstractDashboardController
{
    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield MenuItem::section('easy.page.admin.menu.contents'); // (Optional)
        yield MenuItem::linkToCrud('easy.page.admin.menu.pages', 'fa fa-file-alt', Page::class);

        ...
```

### View page

The PageController handles some methods to view pages with a single index().

The URI for both is simply /{slug} where slug is the... page.

If your page has one parent, then the URI is the following: /{parentSlug}/{slug}.

You can notice that we respect the pages hierarchy in the generated url.

You can navigate through a complex list of pages, as long as they are related as parent and child.

This allows you to have such urls like this one : http://www.mysite.com/about/company/team/members for instance, will show only the members page, but its parent has a parent, that has a parent, and so on, until you reach the "root" parent. And it's the same behavior for categories.

Note: this behavior is the precise reason why you have to use a specific rules for your routing, unless you may have many "404" errors.

### Generate a route based on a single page

If you have a `Page` object in a view or in a controller, you can get the whole arborescence by using the `getTree()` method, which will navigate through all parents and return a string based on a separator argument (default `/`, for urls).

Let's get an example with this kind of tree:

```
/ - Home (root url)
├─ /welcome       - Welcome page (set as "homepage", so "Home" will be the same)
│  ├─ /welcome/our-company            - Our company
│  ├─ /welcome/our-company/financial  - Financial
│  └─ /welcome/our-company/team       - Team
└─ Contact
```

Imagine we want to generate the url for the "Team" page. You have this `Page` object in your view/controller.

```php
{# Page : "Team" #}
{{ path('easy_page_index', {"slugs": page.tree}) }}
{# Will show : /welcome/our-company/team #}
```

Or in a controller:

```php
// Page : "Team"
$url = $this->generateUrl('easy_page_index', ['slugs' => $page->getTree()]);
// $url === /welcome/our-company/team
```

### Homepage

The homepage is always the first `Page` object with its `template` attribute set to `homepage`. Be sure to have only one element defined as homepage, or you may have unexpected results.

### Design

You have some options to customize the design of your simple CMS.

#### Using different layouts

Obviously, the default layout has no style.

To change the layout, simply change the OrbitaleCmsBundle configuration to add your own layout:

```yaml
# config/packages/easy_page.yml
easy_page:
    layouts:
        front: { resource: @App/layout.html.twig } # The Twig path to your layout
```

Without overriding anything, you can easily change the layout for your CMS!

Take a look at the [default layout](src/Resources/views/front/pages/default.html.twig) to see which Twig blocks are mandatory to render correctly the pages.

#### Advanced layout configuration

The basic configuration for a layout is to specify a template to extend.

But if you look at the Configuration reference you will see that there are many other parameters you can use to define a layout:

Prototype of a layout configuration:

* **name** (attribute used as key for the layouts list):<br>
  The name of your layout. Simply for readability issues, and maybe to get it
  directly from the config (if you need it).
* **resource**:<br>
  The Twig template used to render all the pages (see the [above](#using-different-layouts) section)
* **assets_css** and *assets_js*:<br>
  Any asset to send to the Twig `asset()` function. The CSS is rendered in the
  `stylesheets` block, and js in the `javascripts` block.
* **host**:<br>
  The exact domain name you want the layout to match with.
* **pattern**:<br>
  The regexp of the path you want to match with for this layout.
  It's nice if you want to use a different layout for pages. For
  example, you can specify a layout for the `^/page/` pattern, and another for
  `^/about-us/`.
  If you specify a very deep pattern, you can even change the layout for a single
  page!

Take another look on the [config reference](#configuration-reference) if you
need to get the prototype defaults.

:warning: **Warning!** The **first matching** layout will be used, as well as
routing would do, so be sure to configure them in the right order!<br>
Empty values won't be taken in account.

## Configuration reference

```yml
# config/packages/easy_page.yml
easy_page:
    page_class: ~              # Required, must extend Easy Page class
    page_repository:      Adeliom\EasyPageBundle\Repository\PageRepository
    page_controller:      Adeliom\EasyPageBundle\Controller\PageController
    layouts:
        # Prototype
        name:
            name: ~             # Optional, it's automatically set from the key if it's a string
            resource: ~         # Required, must be a valid twig template
            assets_css: []      # Injected with the `asset()` twig function
            assets_js: []       # Injected with the `asset()` twig function
            assets_webpack: []  # Injected with the `encore_entry_link_tags()` and `encore_entry_script_tags()` twig functions
            pattern: ~          # Regexp
            host: ~             # Exact value
    cache:
        enabled: false
        ttl: 300
```

## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)


## Thanks to

[Orbitale/CmsBundle](https://github.com/Orbitale/CmsBundle)


