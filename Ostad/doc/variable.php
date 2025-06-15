

-> variable hocce ekta patro
-> mane ram er moddhe ekta jayga jekhane kicu data tenporary vabe rakhte pari.
-> variable changeable.
.. but constant changable nah.

-> php hocce losely type language, se nijey type bujte pare.


**************
PHP Variable Roules:
**************

**-> echo "\n"; // new line

✅ Valid Examples (Shothik)

    <?php
        $name = "Masum";          // letter diye start, valid
        $age = 25;                // letter diye start, valid
        $_status = "active";      // underscore diye start, valid
        $user_1 = "Admin";        // letter + underscore + number, valid

        echo $name;               // Output: Masum
    ?>


❌ Invalid Examples (Vul)
    <?php
        // $1user = "Masum";     // ❌ number diye start kora jabe na
        // $user-name = "Ali";   // ❌ hyphen ('-') allowed na
        // $ = "Empty";          // ❌ variable name dorkar
        // $class = "Science";   // ❌ class holo PHP keyword, avoid kora uchit

        // echo $1user;
    ?>

📝 Case Sensitivity Example

    <?php
        $name = "Masum";
        $Name = "Rana";

        echo $name; // Output: Masum
        echo $Name; // Output: Rana
    ?>


✅ Best Practice Example

    <?php
        $studentName = "Masum Islam";  // camelCase
        $student_age = 23;             // snake_case
        $is_logged_in = true;

        echo "$studentName is $student_age years old.";
        echo " Here {$studentName}, your age is {$student_age}."; // best practice ***
    ?>