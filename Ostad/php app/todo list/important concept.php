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


🟢 3. include_once
    include_once 'config.php';


🔹 Kaj:
    File ta include korbe ekbar matro.
    Jodi same file abar include kora hoy, skip kore.
    Jodi file na thake → warning dibe (just like include).

🔹 Example:
    include_once 'config.php';
    include_once 'config.php';


🔸 Output:
    Warning: include_once(config.php): failed to open stream...
    Hello World!


🔴 4. require_once 
    require_once 'config.php';

🔹 Kaj:
    File ta ekbar matro include kore.
    Jodi file na thake → fatal error dibe.
    Best choice for database config, class file, etc.

🔹 Example:
    require_once 'config.php';
    require_once 'config.php';

🔸 Output:
    Fatal error: require_once(): Failed opening required 'config.php'


🔄 🔍 Summary Table:
Statement	    File Missing → Error Type	Ekbar Include	    Use Case
include	        ⚠️ Warning	                ✅ Multiple	      Optional file
require	        ❌ Fatal Error	           ✅ Multiple	     Mandatory file
include_once	⚠️ Warning	                ✅ Only Once	      Optional but avoid duplicate
require_once	❌ Fatal Error  	           ✅ Only Once	     Most common for configs, classes






