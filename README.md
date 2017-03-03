# Transformer

<a href="https://travis-ci.org/deefour/transformer"><img src="https://travis-ci.org/deefour/transformer.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/deefour/transformer"><img src="https://poser.pugx.org/deefour/transformer/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/deefour/transformer"><img src="https://poser.pugx.org/deefour/transformer/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/deefour/transformer"><img src="https://poser.pugx.org/deefour/transformer/license.svg" alt="License"></a>

Transform raw input data into consistent, immutable data transfer objects.

## Getting Started

Run the following to add Transfromer to your project's `composer.json`. See [Packagist](https://packagist.org/packages/deefour/transformer) for specific versions.

```bash
composer require deefour/transformer
```

**`>=PHP5.5.0` is required.**

## Overview

 - All transformers extend the abstract `Deefour\Transformer\Transformer` class.
 - A tranformer accepts a single array of data during instantiation.
 - Attributes on the input source can be cast into specific types.
 - A getter can be created for each attribute to define a transformation of it's raw value.
 - Methods can be created to provide additional, custom attributes.
 - The input source on the transformer is immutable.
 - The transformer can be queried to retrieve transformed versions of individual attributes from the source data or the entire data set.

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

Let's also say that we want to be sure the title of the book has been properly titleized, the price is a float value, and the publication date is a `Carbon\Carbon` datetime object. The attributes of this raw `$input` can be formatted in a specific, consistent format using a transformer.

```php
use Deefour\Transformer\Transformer;
use Carbon\Carbon;

class BookTransformer extends Transformer
{
    protected $casts [
        'price' => 'float',
    ];

    public function title()
    {
        return trim(ucwords($this->raw('title')));
    }

    public function publicationDate()
    {
        return Carbon::parse($this->raw('publication_date'));
    }
}
```

The methods are optional, each having public visibility and being named after a camel-cased version of an attribute. These methods will be called whenever those attributes are requested from the transformer.

```php
$transform = new BookTransformer($input);

$transform->get('title');            //=> 'A Whole New World'
$transform->get('price');            //=> 29.95 (cast to a float)
$transform->get('publication_date'); //=> Carbon\Carbon instance
```

### Casts

A protected `$casts` property can be added to a transformer, composed of attribute names as its keys and the scalar type the attribute should be cast into by the transformer as its values. This mapping will be checked as attributes are returned from a transformer, casting them to the desired type.

```php
class BookTransformer extends Transformer
{
    protected $casts [
        'price' => 'float',
    ];
}

$attributes  = [ 'price' => '3.23' ];
$transformer = new BookTransformer($attributes);

$transformer->price; //=> 3.23 (cast to a float)
```

> **Note:** Casts to type 'object' and 'array' will be converted to JSON using [`json_encode`](https://php.net/json_encode).

### Hiding Raw Input

A protected `$hidden` property can be added to a transformer, listing attributes that will be omitted from bulk requests for information like `toArray()`, `all()`, and `jsonSerialize()`.

```php
class CarTransformer extends Transformer
{
    protected $hidden = [ 'cylinders' ];
}

$attributes  = [ 'make' => 'Subaru', 'model' => 'WRX', 'cylinders' => 4 ];
$transformer = new Transformer($attributes);

$transformer->cylinders; //=> 4
$transformer->has('cylinders'); //=> true
$transformer->all(); //=> [ 'make' => 'Subaru', 'model' => 'WRX' ]
$transform->except('make'); // [ 'model' => 'WRX' ]
```

### Fallbacks (Default Values)

A protected `$fallbacks` property can be added to a transformer, composed of attribute names as its keys and default values as its values. This mapping will be checked as attributes are requested from a transformer but cannot be found on the source data **or whose value is `NULL`**.


#### Accepting `NULL` Values

If an attributes on the source with a `NULL` value should generally be accepted in favor of a default value in the `$fallbacks` mapping, this can be enabled for the lifecycle of a request on all transformers by running the following:

```php
Deefour\Transformer\Transformer::preferNullValues();
```

To illustrate the difference:

```php
class BookTransformer extends Transformer
{
    protected $fallbacks [
        'category' => 'Miscellaneous',
    ];
}


$attributes  = [ 'category' => null ];
$transformer = new BookTransformer($attributes);

$transformer->category; //=> Miscellaneous

Deefour\Transformer\Transformer::preferNullValues();

$transformer->category; //=> null
```

### Method Attributes

Public methods marked with `@attribute` in their docblock are be treated as attributes on the transformer's `$attributes` source.

```php
class BookTransformer extends Transformer
{
    /**
     * Is the book considered old?
     *
     * @attribute
     * @return string
     */
    public function isOld()
    {
        return $this->publication_date < Carbon::now()->subYears(10);
    }

    /**
     * Is the book nonfiction?
     *
     * @return boolean
     */
    public function internalSlug()
    {
        return sha1($this->title . (string)$this->publication_date);
    }
}
```

The `isOld` method is marked with an `@attribute` annotation in the docblock, causing the transformer to behave as though an `is_old` attribute exists on the source data. `internalSlug()` can be called directly, but it will not be treated as some `internal_slug` attribute because it has not been marked properly with a docblock annotation.

```php
$transform = new BookTransformer([ 'title' => 'A Whole New World' ]);

$transform->get('title');          //=> 'A Whole New World'
$transform->get('is_old')          //=> false
$transformer->get('internal_slug') //=> null

$transform->all();                 //=> [ 'title' => 'A Whole New World', 'is_old' => false ]
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
$transform->has('title');
$transform->contains('title');
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
$transform->only('title', 'price', 'internal_slug'); //=> [ 'title' => 'A Whole New World', 'price' => 29.95, 'internal_slug' => null ]
```

```php
$transform->intersect('title', 'price', 'internal_slug'); //=> [ 'title' => 'A Whole New World', 'price' => 29.95 ]
```

```php
$transform->except('secret_key'); //=> everything except the 'secret_key' attribute.
$transform->omit('secret_key');
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

#### 1.6.0 - February 14, 2017

 - `jsonSerialize()` will now call `jsonSerialize()` on attributes implementing `JsonSerializable`, allowing transformers to recursively be encoded to JSON.

#### 1.5.0 - October 20, 2016

 - Rename `default()` to `fallback()` throughout the library for compatibility with all PHP versions.

#### 1.4.0 - October 20, 2016

 - Support for default attributes being set on a class' new `$fallbacks` property. This set of defaults will be checked when an attribute is requested which does not exist or is `NULL`. Thanks to [@dgallinari](https://github.com/dgallinari) [#2](https://github.com/deefour/transformer/pull/2)

#### 1.3.0 - October 16, 2016

 - The `@attribute` annotation only needs to be set on methods you wish to be treated as attributes that are not camel-cased versions of attributes that exist on the raw input source.
 - `omit()` and `without()` have been added as aliases for `except()`.
 - `has()` and `contains()` have been added as aliases for `exists()`.

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

Copyright (c) 2016 [Jason Daly](http://www.deefour.me) ([deefour](https://github.com/deefour)). Released under the [MIT License](http://deefour.mit-license.org/).
