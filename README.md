# Cat2

* Author: [Mark Croxton](http://hallmark-design.co.uk/)

## Version 1.1.1

* Requires: ExpressionEngine 2

## Description

Convert between category name, category id and category url title.
Query results are cached, so you can use the same tag multiple times
in your template without additional overhead.

## Installation

1. Create a folder called 'cat2' inside ./system/expressionengine/third_party/
2. Move the file pi.cat2.php into the folder

## Usage

### Tags
```php
{exp:cat2:id} // Get category_id
{exp:cat2:name} // Get category_name
{exp:cat2:url_title} // Get category_url_title
```

### Parameters

#### Required
    category_url_title=
    category_name=
    category_id=

One of these must be present in order to find the correct category.

#### Optional
`category_group=`

Filter category results by a specific category group ID, or multiple category group IDs.

Examples:
```php
category_group="2"
category_group="5|6"
```

`debug=`

Output error messages if tag is used incorrectly. Can be "yes" or "no" (default is "no").

### Examples

#### Getting category_id

From category_url_title: `{exp:cat2:id category_url_title="my_category"}`

From category_name: `{exp:cat2:id category_name="my category"}`

#### Getting category_name

From category_id: `{exp:cat2:name category_id="25"}`

From category_url_title: `{exp:cat2:name category_url_title="my_category"}`

#### Getting category_url_title

From category_id: `{exp:cat2:url_title category_id="25"}`

From category_name: `{exp:cat2:url_title category_name="my category"}`

#### Can also be used as a tag pair, e.g.:

    {exp:cat2:id category_url_title="my_category" parse="inward"}
      {category_id}
    {/exp:cat2:id}

    {exp:cat2:name category_id="25" parse="inward"}
      {category_name}
    {/exp:cat2:name}

    {exp:cat2:url_title category_id="25" parse="inward"}
      {category_url_title}
    {/exp:cat2:url_title}
