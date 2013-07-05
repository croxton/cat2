#cat2

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

##Tags:
* {exp:cat2:id}
* {exp:cat2:name}
* {exp:cat2:url_title}

##Parameters:
* category_url_title=
* category_name=
* category_id=
* category_group=
* debug="yes|no"

##Example use:

category_id from category_url_title:

	{exp:cat2:id category_url_title="my_category" category_group="5"}

category_id from category_name:

	{exp:cat2:id category_name="my category" category_group="5"}

category_name from category_id:

	{exp:cat2:name category_id="25" category_group="5"}

category_name from category_url_title:

	{exp:cat2:name category_url_title="my_category" category_group="5"}

category_url_title from category_id:

	{exp:cat2:url_title category_id="25" category_group="5"}

category_url_title from category_name:

	{exp:cat2:url_title category_name="my category" category_group="5"}

Can also be used as a tag pair, e.g.:

	{exp:cat2:id category_url_title="my_category" category_group="5" parse="inward"}
		{category_id}
	{/exp:cat2:id}

	{exp:cat2:name category_id="25" category_group="5" parse="inward"}
		{category__name}
	{/exp:cat2:name}

	{exp:cat2:url_title category_id="25" category_group="5" parse="inward"}
		{category_url_title}
	{/exp:cat2:url_title}