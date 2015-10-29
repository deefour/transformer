# Transformer

[![Build Status](https://travis-ci.org/deefour/transformer.svg)](https://travis-ci.org/deefour/transformer)
[![Packagist Version](http://img.shields.io/packagist/v/deefour/transformer.svg)](https://packagist.org/packages/deefour/transformer)
[![Code Climate](https://codeclimate.com/github/deefour/transformer/badges/gpa.svg)](https://codeclimate.com/github/deefour/transformer)
[![License](https://poser.pugx.org/deefour/transformer/license)](https://packagist.org/packages/deefour/transformer)

Transform raw input data into consistent, immutable data transfer objects.

## Getting Started

Run the following to add Transfromer to your project's `composer.json`. See [Packagist](https://packagist.org/packages/deefour/transformer) for specific versions.

```bash
composer require deefour/transformer
```

**`>=PHP5.5.0` is required.**

> **Note:** A work-in-progress attempt to explain how I use this package along with [`deefour/interactor`](https://github.com/deefour/interactor) and [`deefour/authorizer`](https://github.com/deefour/authorizer) to aide me in application development **[is available at this gist](https://gist.github.com/deefour/c6cfcebe808216a874f5)**.

## Overview

 - All transformers extend the abstract `Deefour\Transformer\Transformer` class.
 - A tranformer accepts a single array of data during instantiation.
 - Attributes on the input source can be cast into specific types.
 - A method can be created for each attribute to define a transformation of it's raw value.
 - Methods can be created to provide additional, custom attributes.
 - The input source on the transformer is immutable.
 - The transformer can be queried to retrieve "transformed" versions of individual attributes from the source data or the entire data set.

## Example

Let's say the following input data is submitted via a `POST` request to create a new `Book`.

```php
$input = [
    'title'            => 'a whole new world',
    'price'            => '29.95',
    'publication_date' => '2010-12-09',
    'author'           => 'Jason Daly',
];
```

Let's also say that we want to be sure the title of the book has been properly titleized, the price is a float value, and the publication date is a `Carbon\Carbon` datetime object. The attributes of this raw `$input` can be converted into a DTO containing attributes formatted in a specific, consistent format. These conversions can be done easily by passing the raw data into a transformer for the book.

```php
use Deefour\Transformer\Transformer;
use Carbon\Carbon;

class BookTransformer extends Transformer
{
    protected $casts [
        'price' => 'float',
    ];

    protected function title()
    {
        return trim(ucwords($this->raw('title')));
    }

    protected function publicationDate()
    {
        return Carbon::parse($this->raw('publication_date'));
    }
}
```

The `$casts` property is an array composed of attribute names as its keys and the scalar type the attribute should be cast into by the transformer as the values.

The methods are optional, each having protected visibility and being named after a camel-cased version of an attribute. These methods will be called whenever those attributes are requested from the transformer.

```php
$transform = new BookTransformer($input);

$transform->get('title');            //=> 'A Whole New World'
$transform->get('price');            //=> 29.95 (cast to a float)
$transform->get('publication_date'); //=> Carbon\Carbon instance
```

### Method Attributes

Any `protected` method added to a transformer is treated as an attribute, even if the snake-cased version of the method name is not found in the `$attributes` source on the class. An `@internal` tag can be added to the docblock of any `protected` method that should **not** be treated as an attribute on the transformer.

```php
class BookTransformer extends Transformer
{
    /**
     * Fetch the cover image URL from a web service.
     *
     * @return string
     */
    protected function coverImage()
    {
        return $this->fetchTheImage()['thumbnail'];
    }

    /**
     * Get the raw JSON response from the web service for the image.
     *
     * @internal
     * @return boolean
     */
    protected function fetchTheImage()
    {
        return true;
    }
}
```

Although a `'cover_image'` attribute is not provided to the transformer during construction, it functions as any other attribute. `fetchTheImage` will be ignored.

```php
$transform = new BookTransformer([ 'title' => 'A Whole New World' ]);

$transform->get('title');      //=> 'A Whole New World'
$transform->get('cover_image') //=> 'http://some.cdn.path/to/an/image.png'

$transform->all(); //=> [ 'title' => 'A Whole New World', 'cover_image' => 'http://some.cdn.path/to/an/image.png' ]
```



## Accessing Data

Individual transformed attributes can be retrieved with `get()`.

```php
$transform->get('title');
```

A magic `__get()` implementation provides property access to the transformed attributes

```php
$transform->title;
```

A magic `__call()` implementation provides method access

```php
$transformer->title();
```

The existince of a property can be checked through `__isset()` or the api

```php
isset($transform->title);

$transform->exists('title');
```

Transformers also implement `ArrayAccess` *(attempting to set or unset throws an exception)*.

```php
$transform['title'];
```

All transformed attributes can be retrieved at once.

```php
$transform->all();
```

and a specific set of keys can be plucked all at once.

```php
$transform->only('title', 'price'); //=> [ 'title' => 'A Whole New World', 'price' => 29.95 ]
```

```php
$transform->except('secret_key'); //=> everything except the 'secret_key' attribute.
```

The `JsonSerializable` interface is also implemented.

```php
json_encode($transform); //=> "{'title':'A Whole New World', 'price':29.95, 'publication_date':'2010-12-09 00:00:00', 'author':'Jason Daly'}"
```

Individual raw attributes or the entire raw source can be retrieved.

```php
$transform->raw('title'); //=> 'a whole new world'
$transform->raw(); //=>  [ 'title' => 'a whole new world', 'price' => '29.95', 'publication_date' => '2010-12-09', 'author' => 'Jason Daly' ]
```

A default value can be provided to `get()` as a second parameter. If the default is a callable, it will be evaluated before returning.

```php
$transformer->get('invalid-attribute', 'Not Available'); //=> 'Not Available'
$transformer->get('invalid-attribte', function() { return 'Oops!'; }); //=> 'Oops!'
```

## Mutable Transformers

In the base transformer, `__set()`, `offsetSet`, and `offsetUnset` are all null methods. This (lack of) behavior keeps the underlying source data immutable.

A `MutableTransformer` class exists which does implement these methods, allowing additional properties to be added to, or existing properties to be modified on the transformer instance.

The `__call()` method can also be used to set/modify attributes on the transformer.

```php
$transformer = new MutableTransformer([ 'foo' => '1234' ]);

$transformer->foo('abcd');

$transformer->get('foo'); //=> 'abcd'
```

Instantiation and data access are otherwise identical to the base transformer.

### Tracking Changes

When an attribute is modified on a mutable transformer, it's original value is maintained. The transformer can be queried to determine if an attribute has been modified after construction or to retrieve a list of changes.

```php
$transformer = new MutableTransformer([ 'foo' => 'AAA', 'bar' => 'BBB' ]);

$transformer->isDirty(); //=> false

$transformer->foo = 'new value';

$transformer->isDirty(); //=> true
$transformer->dirty(); //=> [ 'foo' ]
$transformer->get('foo'); //=> 'new value'
$transformer->original('foo'); //=> 'AAA'

$transformer->changes(); //=> [ 'foo' => 'new value' ]
```

## Contribute

- Issue Tracker: https://github.com/deefour/transformer/issues
- Source Code: https://github.com/deefour/transformer

## Changelog

#### 1.0.1 - October 29, 2015

 - Added `except()` method.

#### 1.0.0 - October 7, 2015

 - Release 1.0.0.

#### 0.4.0 - September 7, 2015

 - Support added for "attribute methods" - methods who's snake-cased equivalent name is not present in the `$attributes` source, but who are still treated as any other attribute that *is* present in the `$attributes` source.
 - `protected` methods that should not be treated as "attribute methods" should now be tagged `@internal` in their docblock.

#### 0.3.0 - September 4, 2015

 - New change tracking, inspired by [yammer/model_attribute](https://github.com/yammer/model_attribute)

#### 0.2.6 - June 5, 2015

 - Now following PSR-2.

#### 0.2.5 - June 3, 2015

 - New `__call()` functionality providing magic method access to attributes.
 - `get()` now handles default values, including closures.

#### 0.2.4 - June 2, 2015

 - Fixed bugs in the `only()` method related to nested attributes.

#### 0.2.2 - May 30, 2015

 - `raw()` will now return the complete, non-transformed source if no `$attribute` is specified.
 - `MutableTransformer` can now be instantiated without any arguments passed to the constructor.

#### 0.2.1 - May 25, 2015

 - Improved code formatting.

#### 0.2.0 - May 5, 2015

 - Made the base transformer a regular class (it used to be abstract).
 - Added new `MutableTransformer`.

#### 0.1.0 - April 22, 2015

 - Initial release.

## License

Copyright (c) 2015 [Jason Daly](http://www.deefour.me) ([deefour](https://github.com/deefour)). Released under the [MIT License](http://deefour.mit-license.org/).
