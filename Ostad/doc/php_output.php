/**
php output

*/



<?php
echo "Hello World!";
?>




<?php

// String variable
$name = "Masum";
var_dump($name); 
// Output: string(5) "Masum"

echo "\n";

// Integer variable
$age = 25;
var_dump($age); 
// Output: int(25)

echo "\n";

// Float variable
$height = 5.9;
var_dump($height); 
// Output: float(5.9)

echo "\n";

// Simple indexed array
$colors = ["red", "green", "blue"];
var_dump($colors); 
/* Output: 
array(3) {
  [0]=>
  string(3) "red"
  [1]=>
  string(5) "green"
  [2]=>
  string(4) "blue"
}
*/

echo "\n";

// Associative array
$user = [
    "name" => "Masum",
    "age" => 25
];
var_dump($user);
/* Output:
array(2) {
  ["name"]=>
  string(5) "Masum"
  ["age"]=>
  int(25)
}
*/

echo "\n";

// Object
class Person {
    public $name = "Masum";
    public $age = 25;
}

$person = new Person();
var_dump($person);
/* Output:
object(Person)#1 (2) {
  ["name"]=>
  string(5) "Masum"
  ["age"]=>
  int(25)
}
*/

?>
















