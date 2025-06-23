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
class Person
{
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



✅ PHP Data Types

🔹 Scalar Types:
String
Integer
Float / Double
Boolean

🔹 Compound Types:
Array
Object

🔹 Special Types:
NULL
Resource



<?php

// 1. String
$name = "Masum";
var_dump($name);
// Output: string(5) "Masum"

echo "\n";

// 2. Integer
$age = 25;
var_dump($age);
// Output: int(25)

echo "\n";

// 3. Float / Double
$height = 5.9;
var_dump($height);
// Output: float(5.9)

echo "\n";

// 4. Boolean
$isAdmin = true;
var_dump($isAdmin);
// Output: bool(true)

echo "\n";

// 5. Array
$colors = ["red", "green", "blue"];
var_dump($colors);
/* Output:
array(3) {
  [0]=> string(3) "red"
  [1]=> string(5) "green"
  [2]=> string(4) "blue"
}
*/

echo "\n";

// 6. Associative Array (still array type)
$user = ["name" => "Masum", "role" => "Admin"];
var_dump($user);
/* Output:
array(2) {
  ["name"]=> string(5) "Masum"
  ["role"]=> string(5) "Admin"
}
*/

echo "\n";

// 7. Object
class Person
{
    public $name = "Masum";
}
$p = new Person();
var_dump($p);
/* Output:
object(Person)#1 (1) {
  ["name"]=> string(5) "Masum"
}
*/

echo "\n";

// 8. NULL
$data = null;
var_dump($data);
// Output: NULL

echo "\n";

// 9. Resource
$file = fopen("php://temp", "r");
var_dump($file);
// Output: resource(stream)

fclose($file); // resource close kora valo

?>





✅ Syntax:

printf(format_string, arg1, arg2, ...);
format_string — ekta string ja te special placeholders thake (e.g. %s, %d, %f)

arg1, arg2 — je value gulo oi placeholder-er jaygay boshbe



🧪 Examples:

1. String output
<?php
$name = "Masum";
printf("Hello %s", $name);
// Output: Hello Masum

// 2. Integer format
$age = 25;
printf("I am %d years old", $age);
// Output: I am 25 years old

// 3. Float with decimals
$price = 49.999;
printf("Price: %.2f", $price);
// Output: Price: 50.00


// 4. Zero-padding
$invoice = 7;
printf("Invoice No: %04d", $invoice);
// Output: Invoice No: 0007



                         
