# CommonMark Template Renderer

### Render your markdown by your favorite template library

![Test Suite](https://github.com/pomodocs/commonmark-template-renderer/actions/workflows/test.yml/badge.svg)
![Static Analysis](https://github.com/pomodocs/commonmark-template-renderer/actions/workflows/analysis.yml/badge.svg)
[![Maintainability](https://qlty.sh/gh/pomodocs/projects/commonmark-template-renderer/maintainability.svg)](https://qlty.sh/gh/pomodocs/projects/commonmark-template-renderer)
[![Code Coverage](https://qlty.sh/gh/pomodocs/projects/commonmark-template-renderer/coverage.svg)](https://qlty.sh/gh/pomodocs/projects/commonmark-template-renderer)
![GitHub](https://img.shields.io/github/license/pomodocs/commonmark-template-renderer)

CommonMark Template Renderer Extension is an extension for [League CommonmMark](https://commonmark.thephpleague.com/) to render the html elements via your favorite template engine.

> [!Warning]
> At the moment, we support only [Twig](https://twig.symfony.com/).
> We scheduled [Latte](https://latte.nette.org/), [Blade](https://github.com/EFTEC/BladeOne) and [Plates](https://platesphp.com/) support to version 1.0


## Why render html elements via templates?

The answer is simple: easy html customization.
And it's the only reason to use this library. In fact, the [defaul templates](https://github.com/pomodocs/commonmark-template-renderer/tree/master/resources/templates/default) generate exactly the same output as standard renderers. But template engines introduce some complexity and a little overhead so if you don't customize your html __you don't need to use this library__.


## Installation

Install the extension via [Composer](https://getcomposer.org):

```bash
composer require pomodocs/commonmark-template-renderer
```


## Basic Usage

After installing it, register the extension in your [Commonmark Environment](https://commonmark.thephpleague.com/2.7/basic-usage/):

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateRendererExtension;

$config = [
    'templateRenderer' => [
        'engine' => 'twig',
        'templates_dirs' => [
            '/my/templates/dir',
            '/another/templates/dir',
        ],
    ]
];

$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());
$environment->addExtension(.....); // add your favorite extensions
$environment->addExtension(new TemplateRendrerExtension());

$converter = new TemplateConverter($environment);
```

Now you're ready to convert your html.


## Configuration

You can configure the library by passing an array with `templateRenderer` key to the `Environment` object:

```php
<?php

use League\CommonMark\Environment\Environment;


$config = [
    'templateRenderer' => [
        'engine' => 'twig',
        'templates_dirs' => [
            '/my/templates/dir',
            '/another/templates/dir',
        ],
    ]
];

$environment = new Environment($config);
```

Let's explain the configuration keys:

-  `engine`: the template engine by which we render our templates. Supported engines are: _twig_, _latte_, _blade_, _plates_. Default to __twig__.
-  `templates_dirs`: an array of directories where the engine looks for the templates. The dafult directory, containing the templates shipped by the library, is automatically included.

## Customize your html

Let's introduce the goal of this library: easy customization of your html fragments. We try to explain it via a simple example.

Let's suppose we wanto to use [Bulma](https://bulma.io) css framework and we want to style headings nodes by [Bulma title class](https://bulma.io/documentation/elements/title/).
When we write the following markdown:

```markdown
# Title 1
## Title 2
### Title 3
```

our resulting html should be:

```html
<h1 class="title is-1">Title 1</h1>
<h2 class="title is-2">Title 2</h2>
<h3 class="title is-3">Title 3</h3>
```

Every node is rendered by a small template. You can see the full templates list in https://github.com/pomodocs/commonmark-template-renderer/tree/master/resources/templates/default/twig.

Heading node is rendered via `heading.html.twig` template so we create a directory where our custom template resides (i.e. `resources/templates`) and create the `heading.html.twig` file. For the first shot, you can copy-paste https://github.com/pomodocs/commonmark-template-renderer/blob/master/resources/templates/default/twig/heading.html.twig.

The content of the copied file is the following:

```twig
<h{{ node.level }}{{ node|render_attributes }}>{{ node|render_children }}</h{{ node.level }}>
```

Our engine passes two variables to the templates: `node` and `configuration`.
-  `node` variable contains a [Node](https://github.com/thephpleague/commonmark/blob/2.8/src/Node/Node.php) object, in our example a [Heading](https://github.com/thephpleague/commonmark/blob/2.8/src/Extension/CommonMark/Node/Block/Heading.php) node instance.
-  `configuration`variable contains a [complete configuration](https://commonmark.thephpleague.com/2.x/configuration/) object.

The content of our custom template `resources\templates\heading.html.twig` could be the following:

```twig
<h{{ node.level }} class="title is-{{ node.level }}>{{ node|render_children }}</h{{ node.level }}>
```

> [!IMPORTANT]
> This library ships two filters we can apply to **node objects**:
>
> -  `render_children` filter renders all the children of the given node.
> -  `render_attributes` filter renders all the attributes (class, name, type etc.) of the given node.

Now, you can convert your markdown into html:

```php
<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use PomoDocs\CommonMark\TemplateRenderer\TemplateConverter;
use PomoDocs\CommonMark\TemplateRenderer\TemplateRendererExtension;

$config = [
    'templateRenderer' => [
        'engine' => 'twig',
        'templates_dirs' => [
            __DIR__ . '/resources/templates',
        ],
    ]
];

$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());
$environment->addExtension(new TemplateRendrerExtension());

$converter = new TemplateConverter($environment);

$converter->convert("
# Title 1
## Title 2
### Title 3"
);

// The result is:
// <h1 class="title is-1">Title 1</h1>
// <h2 class="title is-2">Title 2</h2>
// <h3 class="title is-3">Title 3</h3>
```

## Issues

Please, open an issue on [Github repository](https://github.com/pomodocs/commonmark-template-renderer/issues).

## Contributing

Please, see our [Contributing guide](CONTRIBUTING.md).

## Licensing

This library is released under [MIT license](LICENSE).