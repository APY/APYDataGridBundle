Number Column
=============

This column convert numbers based on the locale (e.g. `en`). The country is also used for currency number(e.g. `en_US`).

**Note**: If you use a full locale denomination (e.g. `en_US`), don't set the translation fallback to this locale. It's avoid an temporary exception after clearing your cache.  
E.g. set the fallback to `en` for the locale `en_US`.


## Annotation
#### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

### Additionnal attributes

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|style|string|decimal|decimal, currency, percent, <br />duration, scientific, spellout|Type of the number you want to manage|
|locale|string|'en' for the style `duration`||Set this attribute to force the locale|
|precision|integer|0|Integer|Define the number of number after the comma|
|grouping|boolean|false|true or false|Set to true to group numbers|
|roundingMode|integer|Round half up|See [\NumberFormatter::ROUND_*](http://php.net/manual/en/class.numberformatter.php) values.|How do you want to round a decimal number if precision is defined.|
|ruleSet|string|'%in-numerals' for the style `duration`|A rule set pattern|Set this attribute to define a particular behavior of the NumberFormatter.|
|currencyCode|string|Locale currency code|ISO 4217 Code<br />(e.g. USD, EUR)|Set this attribute to force the currency code|
|fractional|boolean|false|true or false|Set to true if your number is a fractional number (e.g. 0.5 instead of 50 for 50%)|

## Filter
#### Valid values

Numeric values.

#### Default Operator: `eq`

#### Available Operators

|Operator|Meaning|
|:--:|:--|
|eq|Equals|
|neq|Not equal to|
|lt|Lower than|
|lte|Lower than or equal to|
|gt|Greater than|
|gte|Greater than or equal to|
|btw|Between exclusive|
|btwe|Between inclusive|
|isNull|Is not defined|
|isNotNull|Is defined|





