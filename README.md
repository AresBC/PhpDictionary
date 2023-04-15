# README

## Example

```php
$dic = new Dictionary(Type::String, Type::String);
$dic->add('wow', '3');
$dic->add('1', 'one');
$dic->add('oho', 'noice');

/** @var KeyValuePair $kvp */
$newDic1 = $dic->map(fn($kvp) => $kvp->value .= '_added');

/** @var KeyValuePair $kvp */
$newDic2 = $dic->map(function ($kvp) {
    if (is_numeric($kvp->value)) $kvp->value = (int)$kvp->value;
    else return Type::Undefined;
}, Type::String, Type::Integer);

var_dump($dic->toArray());
var_dump($newDic1->toArray());
var_dump($newDic2->toArray());
```

## Output


```
array(3) {
  ["wow"]=>
  string(1) "3"
  [1]=>
  string(3) "one"
  ["oho"]=>
  string(5) "noice"
}
array(3) {
  ["wow"]=>
  string(7) "3_added"
  [1]=>
  string(9) "one_added"
  ["oho"]=>
  string(11) "noice_added"
}
array(1) {
  ["wow"]=>
  int(3)
}

Process finished with exit code 0
```