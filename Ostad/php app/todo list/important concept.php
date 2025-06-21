******************
PHP File Include Statements
******************


🟠 1. include
    include 'config.php';

🔹 Kaj:
    config.php file ke include korbe.
    Jodi file na thake → warning dibe, kintu baki code run hobe.

🔹 Example:
    include 'not_found.php';
    echo "Hello World!";

🔸 Output:
    Warning: include(not_found.php): failed to open stream...
    Hello World!


🔵 2. require
    require 'config.php';


🔹 Kaj:
    File ke include korbe.
    Jodi file na thake → Fatal Error, script bondho hoye jabe.

🔹 Example:
    require 'not_found.php';
    echo "Hello World!";

🔸 Output:
    Fatal error: require(): Failed opening required 'not_found.php'




















    