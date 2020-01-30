# Scrapy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/scrapy/scrapy.svg?style=flat-square)](https://packagist.org/packages/aleksa-sukovic/scrapy)
[![Build Status](https://travis-ci.com/aleksa-sukovic/scrapy.svg?token=zCspA5s4zGkRNiq8zzR1&branch=master)](https://travis-ci.com/aleksa-sukovic/scrapy)

PHP web scraping made easy.

Please note: *Documentation is always a work in progress, please excuse any errors.*

## Installation

You can install the package via composer:

```bash
composer require scrapy/scrapy
```

## Table of contents

- [Basic Usage](#documentation)

- [Parsers](#parsers)

    - [Parser definition](#parser-definition)
    
    - [Adding parser](#adding-parsers)
    
    - [Inline parsers](#inline-parsers)
    
    - [Passing additional parameters](#passing-additional-parameters-to-parsers)

- [Crawly](#crawly)

    - [Initialisation](#crawler-initialisation)
    
    - [Methods](#crawling-methods)
    
        - [Filter](#filter)
        
        - [First](#first)
        
        - [Nth](#nth)
        
        - [Raw](#raw)
        
        - [Trim](#trim)
        
        - [Pluck](#pluck)
        
        - [Count](#count)
        
        - [Int](#int)
        
        - [Float](#float)
        
        - [String](#string)
        
        - [Html](#html)
        
        - [Inner HTML](#inner-html)
        
        - [Exists](#exists)
        
        - [Reset](#reset)
        
        - [Map](#map)
        
        - [Node](#node)

- [Readers](#readers)

    - [Using built in readers](#using-built-in-readers)
    
    - [Writing custom readers](#writing-custom-readers)

- [User Agents](#user-agents)

    - [Why use custom agents](#why-use-custom-user-agents)
    
    - [Using built in agents](#using-built-in-agents)
    
    - [Writing custom user agents](#writing-custom-agents)

- [Build steps precedence](#precedence-of-parameters)

- [Exception Handling](#exception-handling)

- [Testing](#testing)

- [Changelog](#changelog)

- [Credits](#credits)

- [License](#license)

## Basic usage

Scrapy is essentially a reader which can modify read data trough series of tasks. To simply read an url you can do the following.

```php
    use Scrapy\Builders\ScrapyBuilder;

    $html = ScrapyBuilder::make()
        ->url('https://www.some-url.com')
        ->build()
        ->scrape();
```

### Parsers

Just reading HTML from some source is not a lot of fun. Scrapy allows you to crawl HTML with simple yet expressive API relying on Symphony's DOM crawler.

You can think of parsers as actions meant to extract data valuable to you from HTML.

#### Parser definition

Parsers are meant to be self-containing scraping rules allowing you to extract data from HTML string.

```php
    use Scrapy\Parsers\Parser;
    use Scrapy\Crawlers\Crawly;

    class ImageParser extends Parser
    {
         public function process(Crawly $crawly, array $output): array
         {
            $output['hello'] = $crawly->filter('h1')->string();

            return $output;
         }
    }
```

#### Adding parsers

Once you have your parsers defined, it's time to add them to Scrapy.

```php
    use Scrapy\Builders\ScrapyBuilder;

    // Add by class reference
    ScrapyBuilder::make()
        ->parser(ImageParser::class);
    
    // Add concrete instance
    ScrapyBuilder::make()
        ->parser(new ImageParser());
    
    // Add multiple parsers
    ScrapyBuilder::make()
        ->parsers([ImageParser::class, new ImageParser()]);
```

#### Inline parsers

You don't have to write a class for each parser, you can also do inline parsing. Let's see how would that look.

```php
    use Scrapy\Crawlers\Crawly;
    use Scrapy\Builders\ScrapyBuilder;

    ScrapyBuilder::make()
        ->parser(function (Crawly $crawly, array $output) {
            $output['count'] = $crawly->filter('li')->count();
            
            return $output;
        });
```

#### Passing additional parameters to parsers

Sometimes you want to pass some extra context to your parsers. 
With Scrapy, you can pass an associative array of parameters which would become available to every parser.

```php
    use Scrapy\Crawlers\Crawly;
    use Scrapy\Builders\ScrapyBuilder;

    ScrapyBuilder::make()
        ->params(['foo' => 'bar'])
        ->parser(function (Crawly $crawly, array $output) {
                $output['foo'] = $this->param('foo'); // 'bar'
                $output['baz'] = $this->has('baz');   // false
                $output['bar'] = $this->param('baz'); // null
         });
```

The same principle applies no matter if you define parsers as separate classes or inline them with functions.

## Crawly

You might noticed that first argument to parser's *process* method is instance *Crawly* class.

Crawly is an HTML crawling tool. It is based on [Symphony's DOM Crawler](https://symfony.com/doc/current/components/dom_crawler.html).

#### Crawler initialisation

Instance of Crawly can be made from any string.

```php
    use Scrapy\Crawlers\Crawly;

    $crawly1 = new Crawly('<ul><li>Hello World!</li></ul>');
    $crawly2 = new Crawly('Hello World!');

    $crawly1->html(); // '<ul><li>Hello World!</li></ul>'
    $crawly2->html(); // '<body>Hello World!</body>'
```

#### Crawling methods

Crawly provides few helper methods allowing you to more easily get the wanted data from HTML.

##### Filter

Allows you to filter elements with CSS selector. Similar to what `document.querySelector('...')` does.

```php
    $crawly = new Crawly('<ul><li>Hello World!</li></ul>');

    $crawly->filter('li')->html(); // <li>Hello World!</li>
```

##### First

Narrow your selection by taking the first element from it.

```php
    $crawly = new Crawly('<ul><li>Hello</li><li>World!</li></ul>');

    $crawly->filter('li')->first()->html(); // <li>Hello</li>
```

##### Nth

Narrow your selection by taking the nth element from it. Note that indices are 0-based;

```php
    $crawly = new Crawly('<ul><li>Hello</li><li>World!</li></ul>');

    $crawly->filter('li')->nth(1)->html(); // <li>World!</li>
```

##### Raw

Get access to Symphony's DOM crawler.

Crawly does not aim to replace Symphony's DOM crawler, rather just to make it's usage more pleasant. That's why not all methods are exposed directly trough Crawly.

Using `raw` method allows you to utilise the underlying Symphony's crawler.

```php
    $crawly = new Crawly('<ul><li>Hello</li><li>World!</li></ul>');

    $crawly->filter('li')->first()->raw()->html(); // Hello
```

##### Trim

Trims the output string.

```php
    $crawly = new Crawly('<div><span>    Hello!     </span></div>');

    $crawly->filter('span')->trim()->string(); // 'Hello!'
```

##### Pluck

Extract attributes from selection.

```php
    $crawly = new Crawly('<ul><li attr="1">1</li><li attr="2">2</li></ul>');
    $crawly->filter('li')->pluck(['attr']); // ["1","2"]

    $crawly = new Crawly('<img width="200" height="300"></img><img width="400" height="500"></img>');
    $crawly->filter('img')->pluck(['width', 'height']); // [ ["200", "300"], ["400", "500"] ]
```

##### Count

Returns the count of currently selected nodes.

```php
    $crawly = new Crawly('<ul><li>1</li><li>2</li></ul>');

    $crawly->filter('li')->count(); // 2
```

##### Int

Returns the integer value of current selection

```php
    $crawly = new Crawly('<span>123</span>');
    $crawly->filter('span')->int(); // 123

    // Use default if selection is not numeric
    $crawly = new Crawly('');
    $crawly->filter('span')->int(55); // 55
```

##### Float

Returns the integer value of current selection

```php
    $crawly = new Crawly('<span>18.5</span>');
    $crawly->filter('span')->float(); // 18.5

    // Use default if selection is not numeric
    $crawly = new Crawly('');
    $crawly->filter('span')->float(22.4); // 22.4
```

##### String

Returns current selection's inner content as string.

```php
    $crawly = new Crawly('<span>Hello World!</span>');
    $crawly->filter('span')->string(); // 'Hello World!'

    // Use default in case exception arises
    $crawly = new Crawly('');
    $crawly->filter('non-existing-selection')->string('Hello'); // 'Hello'
```

##### Html

Returns HTML string representation of current selection, including the parent element.

```php
    $crawly = new Crawly('<span>Hello World!</span>');
    $crawly->filter('span')->html(); // <span>Hello World!</span>

    // Use default in case exception arises
    $crawly = new Crawly('');
    $crawly->filter('non-existing-selection')->html('<div>Hi</div>'); // <div>Hi</div>
```

##### Inner HTML

Returns HTML string representation of current selection, excluding the parent element.

```php
    $crawly = new Crawly('<span>Hello World!</span>');
    $crawly->filter('span')->innerHtml(); // 'Hello World!'

    // Use default to handle exceptional cases
    $crawly = new Crawly('');
    $crawly->filter('non-existing-selection')->innerHtml('<div>Hi</div>'); // 'Hi'
```

##### Exists

Checks if given selection exists. 

You can get boolean response or raise an exception.

```php
    $crawly = new Crawly('<span>Hello World!</span>');
    $crawly->filter('span')->exists(); // true

    $crawly = new Crawly('');
    $crawly->filter('non-existing-selection')->exists();     // false
    $crawly->filter('non-existing-selection')->exists(true); // new ScrapeException(...)
```

##### Reset

Resets the crawler back to its original HTML.

```php
    $crawly = new Crawly('<ul><li>1</li></ul>');
    $crawly = $crawly->filter('li')->html(); // <li>1</li>

    $crawly->reset()->html(); // <ul><li>1</li></ul>
```

##### Map

This method creates a new array populated with the results of calling a provided function on every node in a selection.

For each node a callback function is called with Crawly intance created from that node. Additionally, callback function
takes second argument which is the 0-based index of a node.

```php
    $crawly = new Crawly('<ul><li>    Hello  </li><li>  World  </li></ul>');

    $crawly->filter('li')->map(function (Crawly $crawly, int $index) {
        return $crawly->trim()->string() . ' - ' . $index;
    }); // ['Hello - 0', 'World - 1']

    // limit the map function
    $crawly->filter('li')->map(function (Crawly $crawly, int $index) {
        return $crawly->trim()->string() . ' - ' . $index;
    }, 1); // ['Hello - 0']
```

##### Node

Returns the first DOMNode of the selection.

```php
    $crawly = new Crawly('<ul><li>1</li></ul>');

    $crawly = $crawly->filter('li')->node(); // DOMNode representing '<li>1</li>' is returned
```

### Readers

Readers are data source classes used by Scrapy to fetch the HTML content.

Scrapy comes with some readers predefined, and you can also write your own if you need to.

#### Using built in readers

Scrapy comes with two built in readers: `UrlReader` and `FileReader`. Lets see how you may use them.

```php
    use Scrapy\Builders\ScrapyBuilder;
    use Scrapy\Readers\UrlReader;
    use Scrapy\Readers\FileReader;

    ScrapyBuilder::make()
        ->reader(new UrlReader('https://www.some-url.com'));
    ScrapyBuilder::make()
        ->reader(new FileReader('path-to-file.html'));
```

As you can see built in readers allow you to use Scrapy by either reading from a url or from a specific file.

#### Writing custom readers

You don't have to be limited to built in readers. Writing you own is a piece of cake.

```php
    use Scrapy\Readers\IReader;

    class CustomReader implements IReader
    {
        public function read(): string
        {
            return '<h1>Hello World!</h1>';
        }
    }
```

And then use it during the build process.

```php
    ScrapyBuilder::make()
        ->reader(new CustomReader());
```

### User agents

A user agent is a computer program representing a person, in this case a Scrapy instance. Scrapy provides several built in user agents for simulating different crawlers.

#### Why use custom user agents

User agents make sense only in a context of readers that fetch their data over HTTP protocol. More precisely, in cases where you want to read a web page that creates its content dynamically using JavaScript.

Scrapy by default can not parse JavaScript files. This is a problem all web crawlers face. There are numerous techniques for overcoming this problem, 
usually by using external services like [Prerender](https://prerender.io/) which redirect crawling bots to cached HTML pages.

Several user agents are provided to allow Scrapy to represent itself as some of the common user agents. Please not  that in case a web page implements more advance crawling security checks
(for example an IP check) than provided checker would fail, since they only modify the HTTP request headers.

If you want to find out more, there is a great article on pre-rendering over at [Netlify](https://www.netlify.com/blog/2016/11/22/prerendering-explained/).

#### Using built in agents

Scrapy comes with few built in agents you can use.

```php
    ScrapyBuilder::make()
        ->agent(new GoogleAgent());                     // Googlebot
    ScrapyBuilder::make()
        ->agent(new GoogleChromeAgent(81, 0, 4043, 0)); // Googlebot
    ScrapyBuilder::make()
        ->agent(new BingUserAgent());                   // Bing
    ScrapyBuilder::make()
        ->agent(new YahooUserAgent());                  // Yahoo
    ScrapyBuilder::make()
        ->agent(new DuckUserAgent());                   // Duck
```

#### Writing custom agents

Just like with readers, you can write your own custom user agents.

```php
    use Scrapy\Agents\IUserAgent;
    use Scrapy\Readers\UrlReader;

    class UserAgent implements IUserAgent
    {
        public function reader(string $url): UrlReader
        {
            $reader = new UrlReader($url);
            $reader->setConfig(['headers' => ['...']]);
            return $reader;
        }
    }
````

And then use it during the build process.

```php
    ScrapyBuilder::make()
        ->agent(new UserAgent());
```

### Precedence of parameters

One thing to note is the precedence of different parameters you may set during the build process.

Setting the url is same as setting the reader to be UrlReader with that url. On the other hand, explicitly setting 
reader will have higher precedence over explicitly setting the url and/or user agent.

```php
    use Scrapy\Readers\UrlReader;
    use Scrapy\Agents\GoogleAgent;
    use Scrapy\Builders\ScrapyBuilder;

    ScrapyBuilder::make()
        ->url('https://www.facebook.com')
        ->agent(new GoogleAgent())
        ->reader(new UrlReader('https://www.youtube.com')); // Youtube will be read without GoogleAgent, Facebook will be ignored.
```

### Exception handling

In general, Scrapy tries to handle all possible exceptions wrapping them in base Scrapy exception class: *ScrapeException*.

What this means is that you can organize your app around a single exception for general error handling.

A more granular system is planned for future release which would allow you to react to a specific parser exceptions.

```php
        use Scrapy\Builders\ScrapyBuilder;
        use Scrapy\Exceptions\ScrapeException;
    
        try {
            $html = ScrapyBuilder::make()
                ->url('https://www.invalid-url.com')
                ->build()
                ->scrape();
        } catch (ScrapeException $e) {
            // 
        }
```

### Testing

To run entire suite of unit tests you can do:

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

- [Aleksa Sukovic](https://github.com/aleksa-sukovic)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
