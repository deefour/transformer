# Transformer

[![Build Status](https://travis-ci.org/deefour/presenter.svg)](https://travis-ci.org/deefour/transformer)
[![Packagist Version](http://img.shields.io/packagist/v/deefour/presenter.svg)](https://packagist.org/packages/deefour/transformer)
[![Code Climate](https://codeclimate.com/github/deefour/presenter/badges/gpa.svg)](https://codeclimate.com/github/deefour/transformer)

Transform raw input data into consistent, immutable data transfer objects.

## Getting Started

Add Tranformer to your `composer.json` file and run `composer update`. See [Packagist](https://packagist.org/packages/deefour/transformer) for specific versions.

```
"deefour/transformer": "~0.1.0"
```

**`>=PHP5.5.0` is required.**

## Overview

 - All transformers extend the abstract `Deefour\Transformer\Transformer` class.
 - A tranformer accepts a single array of data during instantiation.
 - Attributes on the input source can be cast into specific types.
 - A method can be created for each attribute to define a transformation of it's raw value.
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

class BookTransformer extends Transformer {

  protected $casts [
    'price' => 'float',
  ];

  protected function title() {
    return trim(ucwords($this->raw('title')));
  }

  protected function publicationDate() {
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

## Accessing Data

Individual transformed attributes can be retrieved with `get()`.

```php
$transform->get('title');
```

A magic `__get()` implementation provides property access to the transformed attributes

```php
$transform->title;
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

The `JsonSerializable` interface is also implemented.

```php
json_encode($transform); //=> "{'title':'A Whole New World', 'price':29.95, 'publication_date':'2010-12-09 00:00:00', 'author':'Jason Daly'}"
```

Finally, individual raw attributes or the entire raw source can be retrieved.

```php
$transform->raw('title'); //=> 'a whole new world'
$transform->raw(); //=>  [ 'title' => 'a whole new world', 'price' => '29.95', 'publication_date' => '2010-12-09', 'author' => 'Jason Daly' ]
```
