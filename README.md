DBQuery
=======

DBQuery is a PHP database library to simplfy fetching data from your database.
It supports any database that PDO supports, and has been tested extensively
on MySQL and SQLite.

Installation
------------

~~~
require("db_query.inc.php");
~~~

Example Usage
-------------

~~~
// SQLite
$dsn = "sqlite://path/to/dir/database.sqlite";
$dbq = new db_query($dsn);

// MySQL
$dsn  = 'mysql:host=server.domain.com;dbname=my_database';
$user = 'john_smith';
$pass = 'sekrit';
$dbq   = new db_query($dsn,$username,$password);

$sql  = "SELECT First, Last, City, State, Zipcode FROM Customers;";
$data = $dbq->query($sql);

foreach ($data as $rec) {
	// Output code here
}
~~~

DBQuery simplifies the act of sending queries and iterating over the results.
The core of DBQuery is in the `query()` function, which handles sending
queries and building an interable recordset. DBQuery does its best job to 
give you the datatype you want, but you can provide it hints to guide it.

If you call `query($sql,$return_hint)` with a second argument DBQuery will 
format the return data appropriately.

Return Hints
------------ 

**info_hash** return an array of associative arrays (Note: this is the default return type)

~~~
$sql  = "SELECT First, Last, City FROM Customers;";
$data = $dbq->query($sql,'info_hash');

foreach ($data as $i) {
	print "Cust: " . $i['First'] . " " . $i['Last'];
}
~~~

**info_list** return an array of numeric arrays

~~~
$sql  = "SELECT First, Last, City FROM Customers;";
$data = $dbq->query($sql,'info_list');

foreach ($data as $i) {
	print "Cust: " . $i[0] . " " . $i[1];
}
~~~

**one_data** return a single scalar

~~~
$sql = "SELECT CustID FROM Customers WHERE Last = 'Doolis'";
$id  = $dbq->query($sql,'one_data');
~~~

**key_value** return an associative array key/value pair

~~~
$sql  = "SELECT ID, Last FROM Customers;";
$data = $dbq->query($sql,'key_value');

print "Customer #17 = " . $data[17];
print "Customer #21 = " . $data[21];
~~~

**one_row** return a single associtive array

~~~
$sql  = "SELECT First, Last FROM Customers WHERE City = 'Chicago';";
$data = $dbq->query($sql,'one_row');

print "Customer: " . $data['First'] . " " . $data['Last'];
~~~

**one_column** return a single numeric array

~~~
$sql = "SELECT ID FROM Customers WHERE City = 'Chicago';";
$ids = $dbq->query($sql,'one_column');

print "Found IDs: " . join(", ", $ids);
~~~
