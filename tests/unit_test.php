<?PHP

$dbq = init_db();
$dbq->show_errors = false;

assert_options(ASSERT_WARNING,  false);

///////////////////////////////////////
// Select - InfoHash
///////////////////////////////////////
$sql  = "SELECT * FROM Customer LIMIT 8;";
// We don't specify a return type
$data = $dbq->query($sql);

$last_info   = $dbq->last_info();
$return_type = $last_info['return_type'];

// Make sure the default return type is InfoHash
unit_test("SELECT: InfoHashDefault has correct return type", $return_type === 'info_hash');
unit_test("SELECT: InfoHashDefault returns numeric array", is_numeric_array($data) === true);
unit_test("SELECT: InfoHashDefault returns associate array as the first item",is_assoc($data[0]));

$data = $dbq->query($sql,'info_hash');

$first = $data[0];
$cols  = sizeof(array_keys($first));
$rows  = sizeof($data);

// Make sure we get back the correct number of rows
unit_test("SELECT: InfoHash correct rows", $rows === 8);
unit_test("SELECT: InfoHash correct cols", $cols === 6);
// Make sure it's not an assoc array
unit_test("SELECT: InfoHash returns numeric array",is_numeric_array($data));
unit_test("SELECT: InfoHash returns associate array as the first item",is_assoc($data[0]));

///////////////////////////////////////
// Select - InfoHash with Key
///////////////////////////////////////

$sql = "SELECT * FROM Customer WHERE CustID > 3 LIMIT 5;";
$data = $dbq->query($sql,'info_hash|CustID');

$last_info   = $dbq->last_info();
$return_type = $last_info['return_type'];

$count = sizeof($data);
$first = array_slice($data,0,1);
list($fkey,$fval) = each($data);

// Make sure we get back a numeric array
unit_test("SELECT: InfoHashKey returns a numeric array",is_numeric_array($data));
// The first item should not be an array by itself
unit_test("SELECT: InfoHashKey first element is an array",is_array($first));
// Make sure we get more than 5 items
unit_test("SELECT: InfoHashKey returned valid data",$count > 4);
unit_test("SELECT: InfoHashKey first element is correct",$fkey > 3);
unit_test("SELECT: InfoHashKey has correct return type", $return_type === 'info_hash_with_key');

///////////////////////////////////////
// Select - InfoList
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer LIMIT 4;";
$data = $dbq->query($sql,'info_list');

$first = $data[0];
$cols  = sizeof($first);
$rows  = sizeof($data);

// Make sure we get back the correct number of rows
unit_test("SELECT: InfoList correct rows",$rows == 4);
unit_test("SELECT: InfoList correct cols",$cols == 6);
// Make sure it's not an assoc array
unit_test("SELECT: InfoList is numeric array",is_numeric_array($data) === true);
unit_test("SELECT: InfoList first element is a numeric array",is_numeric_array($first) === true);

///////////////////////////////////////
// Select - KeyValue
///////////////////////////////////////
print "\n";

$sql = "SELECT Last, CustID FROM Customer LIMIT 5;";
$data = $dbq->query($sql,'key_value');

$first = array_slice($data,0,1);
$last  = array_slice($data,4,1);
$rows  = sizeof($data);
$cols  = sizeof(array_values($first));

list($key,$value) = each($first);

// Make sure we get right number of items back
unit_test("SELECT: KeyValue correct rows",$rows === 5);
unit_test("SELECT: KeyValue correct cols",$cols === 1);

// Make sure we get the right type of things back
unit_test("SELECT: KeyValue key is string",is_string($key));
unit_test("SELECT: KeyValue value is number",is_numeric($value));
// This should be an assoc (not numeric) array
unit_test("SELECT: KeyValue return associative array",is_assoc($data));

///////////////////////////////////////
// Select - OneData
///////////////////////////////////////
print "\n";

$sql = "SELECT First FROM Customer WHERE Last = 'Doolis' ORDER BY CustID;";
$data = $dbq->query($sql,'one_data');

$count = sizeof($data);

// Make sure we only get ONE piece of scalar data back
unit_test("SELECT: OneData correct return value",$data === 'Jason');
unit_test("SELECT: OneData only one returned item",$count === 1);
unit_test("SELECT: OneData returned item is scalar",is_scalar($data));

///////////////////////////////////////
// Select - OneRow
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_row');

list($fkey,$fval) = each($data);

// Make sure we get back an assoc array that's one dimensional
unit_test("SELECT: OneRow returns an associative array",is_assoc($data) === true);
unit_test("SELECT: OneRow first key is not an array",!is_array($fkey));
unit_test("SELECT: OneRow first value is not an array",!is_array($fval));

///////////////////////////////////////
// Select - OneColumn
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_column');

$count = sizeof($data);

// Make sure we get back a numeric array
unit_test("SELECT: OneColumn returns a numeric array",is_numeric_array($data));
// The first item should not be an array by itself
unit_test("SELECT: OneColumn first element is not an array",!is_array($data[0]));
// Make sure we get more than 5 items
unit_test("SELECT: OneColumn returned at least five elements",$count > 5);
unit_test("SELECT: OneColumn returned valid data",isset($data[0]));

///////////////////////////////////////
// Select - OneRowList
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_row_list');

$count = sizeof($data);

// Make sure we get back a numeric array
unit_test("SELECT: OneRowList returns a numeric array",is_numeric_array($data));
// The first item should not be an array by itself
unit_test("SELECT: OneRowList first element is not an array",!is_array($data[0]));
// Make sure we get more than 5 items
unit_test("SELECT: OneRowList returned valid data",isset($data[0]));

///////////////////////////////////////
// Select - Broken SQL
///////////////////////////////////////
print "\n";

$sql  = "INVALID SQL;";
$data = $dbq->query($sql);

unit_test("SELECT: Invalid SQL returns false", $data === false);

///////////////////////////////////////
// INSERT
///////////////////////////////////////
print "\n";

// Put in several orders
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (1,2,10);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (8,3,1);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (8,8,1);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (8,4,10);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (8,9,15);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (8,11,1000);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (10,12,5);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (10,11,500);");
$id = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (10,7,5);");

unit_test("INSERT: Returns valid InsertID", $id > 3);

///////////////////////////////////////
// UPDATE
///////////////////////////////////////
print "\n";

$affected = $dbq->query("UPDATE orders SET ItemCount = ItemCount + 1 WHERE CustID = 10;");
unit_test("UPDATE: Return correct number of affected rows", $affected > 2);

$affected = $dbq->query("UPDATE orders SET ItemCount = ItemCount + 1 WHERE CustID = 1000;");
unit_test("UPDATE: Return correct number of affected rows for missing CustID", $affected === 0);

///////////////////////////////////////
// DELETE:
///////////////////////////////////////
print "\n";

// Put in several orders
$affected = $dbq->query("DELETE FROM orders WHERE CustID = 99999");
unit_test("DELETE: Removing a non-item returns 0", $affected === 0);

$affected = $dbq->query("DELETE FROM orders WHERE CustID = 8");
unit_test("DELETE: Removing known order returns the correct amount", $affected > 4);

$affected = $dbq->query("DELETE FROM orders WHERE CustID = 8");
unit_test("DELETE: Removing the same order returns 0", $affected === 0);

$affected = $dbq->query("DELETE FROM orders");
unit_test("DELETE: Removing everything returns more than 0", $affected > 0);

///////////////////////////////////////
// Raw PDO
///////////////////////////////////////

print "\n";
$ok = $dbq->dbh->exec("VACUUM");
unit_test("RAW PDO Command Ok $affected", $ok > 0);

print "\n";
unit_test(-1,-1);

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

function init_db() {
	$dir = dirname(__FILE__);

	$file = "$dir/db/test.sqlite";

	if (!is_readable($file)) {
		print "DB is not readable: '$file'\n";
		exit;
	}

	require("$dir/../db_query.inc.php");
	$dsn = "sqlite:$file";
	$dbq = new db_query($dsn);

	if (!$dbq) {
		print "Couldn't connect to the DB";
		exit;
	}

	return $dbq;
}

function is_assoc($arr) {
	if (!is_array($arr)) { return false; }

    return array_keys($arr) !== range(0, count($arr) - 1);
}

function is_numeric_array($array) {
	if (!is_array($array)) { return false; }

	foreach ($array as $a=>$b) {
		if (!is_int($a)) {
			return false;
		}
	}
	return true;
}

function unit_test($name,$code) {
	static $count = 0;
	static $good  = 0;
	static $bad   = 0;

	if ($name === -1) {
		printf("Summary: %d of %d tests passed (%0.2f%% failure rate)\n",$good,$count,($bad / $count) * 100);

		return true;
	}

	$ok = assert($code);

	$color_ok    = "\033[38;5;2m";
	$color_bad   = "\033[38;5;1m";
	$color_reset = "\033[0m";

	if ($ok) {
		printf(" %sOK%s - %s\n",$color_ok,$color_reset,$name);
		$good++;
	} else {
		printf("%sBad%s - %s\n",$color_bad,$color_reset,$name);
		$bad++;
	}

	$count++;

	return $ok;
}

?>
