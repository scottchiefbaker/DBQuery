DBQuery
=======

DBQuery is a PHP database library to simply fetching data from your database.

Installation
------------

~~~
require("db_query.inc.php");

$dsn = "sqlite://path/to/dir/database.sqlite";
$dbq = new db_query($dsn);

$sql  = "SELECT First, Last FROM Customers;";
$data = $dbq->query($sql);
~~~

DBQuery simplifies the act of sending queries and iterating over the results.
The core of DBQuery is in the `query()` function, which handles sending
queries and building an interable recordset. DBQuery does its best job to 
give you the datatype you want, but you can provide it hints.

Return Hints
------------
**info_hash**
**info_list**
**one_data**
**key_value**
**one_value**
**one_column**
