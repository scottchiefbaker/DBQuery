<?php
// Report all PHP errors
error_reporting(E_ALL);

$dbq = init_db();
$dbq->show_errors = false;

///////////////////////////////////////
// Select - InfoHash
///////////////////////////////////////
$sql  = "SELECT * FROM Customer LIMIT 8;";
// We don't specify a return type
$data = $dbq->query($sql);

$last_info   = $dbq->last_info();
$return_type = $last_info['return_type'];

// Make sure the default return type is InfoHash
unit_test($return_type === 'info_hash'    , "SELECT: InfoHashDefault has correct return type");
unit_test(is_numeric_array($data) === true, "SELECT: InfoHashDefault returns numeric array");
unit_test(is_assoc($data[0])              , "SELECT: InfoHashDefault returns associate array as the first item");

$data = $dbq->query($sql,'info_hash');

$first = $data[0];
$cols  = sizeof(array_keys($first));
$rows  = sizeof($data);

// Make sure we get back the correct number of rows
unit_test($rows === 8               , "SELECT: InfoHash correct rows");
unit_test($cols === 6               , "SELECT: InfoHash correct cols");
// Make sure it's not an assoc array
unit_test(is_numeric_array($data)   , "SELECT: InfoHash returns numeric array");
unit_test(is_assoc($data[0])        , "SELECT: InfoHash returns associate array as the first item");

///////////////////////////////////////
// Select - InfoHash with Key
///////////////////////////////////////

$sql = "SELECT * FROM Customer WHERE CustID > 3 LIMIT 5;";
$data = $dbq->query($sql,'info_hash|CustID');

$last_info   = $dbq->last_info();
$return_type = $last_info['return_type'];

$count = sizeof($data);
$first = array_slice($data,0,1);
$fkey  = key($data);
$fval  = $data[$fkey];

// Make sure we get back a numeric array
unit_test(is_numeric_array($data)                 , "SELECT: InfoHashKey returns a numeric array");
// The first item should not be an array by itself
unit_test(is_array($first)                        , "SELECT: InfoHashKey first element is an array");
// Make sure we get more than 5 items
unit_test($count > 4                              , "SELECT: InfoHashKey returned valid data");
unit_test($fkey > 3                               , "SELECT: InfoHashKey first element is correct");
unit_test($return_type === 'info_hash_with_key'   , "SELECT: InfoHashKey has correct return type");

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
unit_test($rows == 4                               , "SELECT: InfoList correct rows");
unit_test($cols == 6                               , "SELECT: InfoList correct cols");
// Make sure it's not an assoc array
unit_test(is_numeric_array($data) === true         , "SELECT: InfoList is numeric array");
unit_test(is_numeric_array($first) === true        , "SELECT: InfoList first element is a numeric array");

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

$key   = key($first);
$value = $first[$key];

// Make sure we get right number of items back
unit_test($rows === 5                            , "SELECT: KeyValue correct rows");
unit_test($cols === 1                            , "SELECT: KeyValue correct cols");

// Make sure we get the right type of things back
unit_test(is_string($key)                        , "SELECT: KeyValue key is string");
unit_test(is_numeric($value)                     , "SELECT: KeyValue value is number");
// This should be an assoc (not numeric) array
unit_test(is_assoc($data)                        , "SELECT: KeyValue return associative array");

// Test float keys
$sql2  = "SELECT ItemCost, ItemID FROM Items LIMIT 5;";
$data2 = $dbq->query($sql2,'key_value');
$val   = $data2['15.79'] ?? "";
unit_test($val == 4, "SELECT: KeyValue on a float key");

///////////////////////////////////////
// Select - OneData
///////////////////////////////////////
print "\n";

$sql = "SELECT First FROM Customer WHERE Last = 'Doolis' ORDER BY CustID;";
$data = $dbq->query($sql,'one_data');

if (is_array($data)) {
	$count = sizeof($data);
} else {
	$count = 1;
}

// Make sure we only get ONE piece of scalar data back
unit_test($data === 'Jason', "SELECT: OneData correct return value");
unit_test($count === 1     , "SELECT: OneData only one returned item");
unit_test(is_scalar($data) , "SELECT: OneData returned item is scalar");

///////////////////////////////////////
// Select - OneRow
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_row');
$fkey = key($data);
$fval = $data[$fkey];

// Make sure we get back an assoc array that's one dimensional
unit_test(is_assoc($data) === true                            , "SELECT: OneRow returns an associative array");
unit_test(!is_array($fkey)                                    , "SELECT: OneRow first key is not an array");
unit_test(!is_array($fval)                                    , "SELECT: OneRow first value is not an array");

$sql = "SELECT * FROM Customer WHERE CustID = 9802413;";
$data = $dbq->query($sql, 'one_row');

unit_test(is_array($data) && empty($data)                     , "SELECT: OneRow with WHERE clause with no matches returns an empty array");

///////////////////////////////////////
// Select - OneColumn
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_column');

$count = sizeof($data);

// Make sure we get back a numeric array
unit_test(is_numeric_array($data)                 , "SELECT: OneColumn returns a numeric array");
// The first item should not be an array by itself
unit_test(!is_array($data[0])                     , "SELECT: OneColumn first element is not an array");
// Make sure we get more than 5 items
unit_test($count > 5                              , "SELECT: OneColumn returned at least five elements");
unit_test(isset($data[0])                         , "SELECT: OneColumn returned valid data");

///////////////////////////////////////
// Select - OneRowList
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer;";
$data = $dbq->query($sql,'one_row_list');

$count = sizeof($data);

// Make sure we get back a numeric array
unit_test(is_numeric_array($data)                 , "SELECT: OneRowList returns a numeric array");
// The first item should not be an array by itself
unit_test(!is_array($data[0])                     , "SELECT: OneRowList first element is not an array");
// Make sure we get more than 5 items
unit_test(isset($data[0])                         , "SELECT: OneRowList returned valid data");

///////////////////////////////////////
// Select - Broken SQL
///////////////////////////////////////
print "\n";

$sql  = "INVALID SQL;";
$data = $dbq->query($sql,'no_error');

unit_test($data === false, "SELECT: Invalid SQL returns false");

///////////////////////////////////////
// No error testing
///////////////////////////////////////

print "\n";

$affected = $dbq->query("INSERT INTO 'items' VALUES(?,?,?);",[null,'Twinkies',3.75],"no_error");
unit_test(is_numeric($affected) && $affected > 10, "NOERROR: Returns valid data");

$affected = $dbq->query("INSERT INTO 'items' VALUES(1,'Duplicate ID',2.76);","no_error");
unit_test($affected === false,                     "NOERROR: Second param returns false");

$affected = $dbq->query("INSERT INTO 'items' VALUES(?,?,?);",[1,'Duplicate ID',3.75],"no_error");
unit_test($affected === false,                     "NOERROR: Third param returns false");

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

unit_test($id > 3, "INSERT: Returns valid InsertID");

///////////////////////////////////////
// UPDATE
///////////////////////////////////////
print "\n";

$affected = $dbq->query("UPDATE orders SET ItemCount = ItemCount + 1 WHERE CustID = 10;");
unit_test($affected > 2,   "UPDATE: Return correct number of affected rows");

$affected = $dbq->query("UPDATE orders SET ItemCount = ItemCount + 1 WHERE CustID = 1000;");
unit_test($affected === 0, "UPDATE: Return correct number of affected rows for missing CustID");

///////////////////////////////////////
// DELETE:
///////////////////////////////////////
print "\n";

// Put in several orders
$affected = $dbq->query("DELETE FROM orders WHERE CustID = 99999");
unit_test($affected === 0, "DELETE: Removing a non-item returns 0");

$affected = $dbq->query("DELETE FROM orders WHERE CustID = 8");
unit_test($affected > 4,   "DELETE: Removing known order returns the correct amount");

$affected = $dbq->query("DELETE FROM orders WHERE CustID = 8");
unit_test($affected === 0, "DELETE: Removing the same order returns 0");

$affected = $dbq->query("DELETE FROM orders");
unit_test($affected > 0,   "DELETE: Removing everything returns more than 0");

///////////////////////////////////////
// Raw PDO
///////////////////////////////////////

print "\n";
$ok = $dbq->dbh->exec("VACUUM");
unit_test($ok > 0, "RAW PDO Command OK '$ok'");

///////////////////////////////////////
// Select - 'one' alias
///////////////////////////////////////
print "\n";

$sql  = "SELECT First FROM Customer WHERE Last = 'Doolis' ORDER BY CustID;";
$data = $dbq->query($sql,'one');
unit_test($data === 'Jason', "SELECT: 'one' alias same as one_data");

///////////////////////////////////////
// Select - OneData with no results
///////////////////////////////////////
print "\n";

$sql = "SELECT First FROM Customer WHERE CustID = 99999;";
$data = $dbq->query($sql,'one_data');
unit_test($data === '', "SELECT: OneData with no matches returns empty string");

///////////////////////////////////////
// Select - InfoHash with no results
///////////////////////////////////////
print "\n";

$sql = "SELECT * FROM Customer WHERE CustID = 99999;";
$data = $dbq->query($sql,'info_hash');
unit_test(is_array($data) && empty($data), "SELECT: InfoHash with no matches returns empty array");

///////////////////////////////////////
// Select - KeyValue with no results
///////////////////////////////////////
print "\n";

$sql = "SELECT Last, CustID FROM Customer WHERE CustID = 99999;";
$data = $dbq->query($sql,'key_value');
unit_test(is_array($data) && empty($data), "SELECT: KeyValue with no matches returns empty array");

///////////////////////////////////////
// Select - KeyPair
///////////////////////////////////////
print "\n";

$sql  = "SELECT * FROM Customer LIMIT 5;";
$data = $dbq->query($sql,'key_pair:CustID,First');

$last_info = $dbq->last_info();
unit_test($last_info['return_type'] === 'key_pair', "SELECT: KeyPair has correct return type");
unit_test(is_assoc($data)                         , "SELECT: KeyPair returns associative array");
unit_test(sizeof($data) === 5                     , "SELECT: KeyPair correct rows");
unit_test($data[1] === 'Jason'                    , "SELECT: KeyPair first value correct");

///////////////////////////////////////
// Select - InfoHash with Key array mode
///////////////////////////////////////
print "\n";

$sql  = "SELECT * FROM Customer WHERE State IN ('OR','TX') ORDER BY State;";
$data = $dbq->query($sql,'info_hash|State[]');

$last_info = $dbq->last_info();
unit_test($last_info['return_type'] === 'info_hash_with_key', "SELECT: InfoHashKey[] has correct return type");
unit_test(is_array($data['OR'])                             , "SELECT: InfoHashKey[] OR key is an array");
unit_test(sizeof($data['OR']) === 3                         , "SELECT: InfoHashKey[] OR has 3 entries");

///////////////////////////////////////
// Select - LIMIT 1 auto-detected as one_row
///////////////////////////////////////
print "\n";

$sql  = "SELECT * FROM Customer LIMIT 1;";
$data = $dbq->query($sql);
$last_info = $dbq->last_info();
unit_test($last_info['return_type'] === 'one_row', "SELECT: Auto-detect LIMIT 1 returns one_row");
unit_test(is_assoc($data) && !empty($data)       , "SELECT: Auto-detect LIMIT 1 data is assoc and non-empty");

///////////////////////////////////////
// Explicit return types
///////////////////////////////////////
print "\n";

$data = $dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (1,2,10);", 'insert_id');
unit_test(is_int($data) && $data > 0, "INSERT: Explicit insert_id return type");

$data = $dbq->query("DELETE FROM orders WHERE 1=1;", 'affected_rows');
unit_test(is_int($data), "DELETE: Explicit affected_rows return type");

///////////////////////////////////////
// Prepared statements with info_hash
///////////////////////////////////////
print "\n";

$sql  = "SELECT * FROM Customer WHERE CustID = ?;";
$data = $dbq->query($sql,[1],'info_hash');
unit_test(sizeof($data) === 1                 , "SELECT: Prepared statement with info_hash correct rows");
unit_test($data[0]['First'] === 'Jason'       , "SELECT: Prepared statement with info_hash correct data");

$data = $dbq->query($sql,[99999],'one_data');
unit_test($data === ''                        , "SELECT: Prepared statement with one_data no match returns empty string");

///////////////////////////////////////
// delay_connect
///////////////////////////////////////
print "\n";

$dbq2 = new DBQuery("sqlite::memory:", "", "", ['delay_connect' => true]);
unit_test($dbq2->dbh === null, "delay_connect: dbh is null before first query");

$dbq2->query("CREATE TABLE _delay_test (id INTEGER PRIMARY KEY, val TEXT);");
unit_test($dbq2->dbh !== null, "delay_connect: dbh is connected after first query");

$dbq2->query("INSERT INTO _delay_test (val) VALUES ('hello');");
$data = $dbq2->query("SELECT * FROM _delay_test;");
unit_test(sizeof($data) === 1 && $data[0]['val'] === 'hello', "delay_connect: query works after auto-connect");

///////////////////////////////////////
// quote()
///////////////////////////////////////
print "\n";

$quoted = $dbq->quote("it's a test");
unit_test(is_string($quoted) && strlen($quoted) > strlen("it's a test"), "quote: returns a quoted string");

///////////////////////////////////////
// begin / commit / rollback
///////////////////////////////////////
print "\n";

$ok = $dbq->begin();
unit_test($ok === true, "Transaction: begin() returns true");

$dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (1,1,5);");
$dbq->rollback();

$data = $dbq->query("SELECT * FROM orders WHERE CustID = 1;");
unit_test(empty($data), "Transaction: rollback undoes insert");

$dbq->begin();
$dbq->query("INSERT INTO orders (CustID,ItemID,ItemCount) VALUES (2,2,3);");
$dbq->commit();

$data = $dbq->query("SELECT * FROM orders WHERE CustID = 2;");
unit_test(sizeof($data) === 1, "Transaction: commit persists insert");

///////////////////////////////////////
// CREATE / DROP
///////////////////////////////////////
print "\n";

$data = $dbq->query("CREATE TABLE _tmp_test (x INTEGER);");
unit_test($data === 1, "CREATE: returns 1");

$data = $dbq->query("DROP TABLE _tmp_test;");
unit_test($data === 1, "DROP: returns 1");

///////////////////////////////////////
// REPLACE
///////////////////////////////////////
print "\n";

$data = $dbq->query("REPLACE INTO Customer (First, Last, City, State, Zipcode, CustID) VALUES ('ReplaceTest', 'User', 'Nowhere', 'XX', 12345, 1);");
unit_test($data === 1, "REPLACE: returns affected rows");

$data = $dbq->query("SELECT * FROM Customer WHERE CustID = 1;");
unit_test($data[0]['First'] === 'ReplaceTest', "REPLACE: data was actually replaced");

///////////////////////////////////////
// query_summary()
///////////////////////////////////////
print "\n";

$summary = $dbq->query_summary();
unit_test(is_string($summary) && !empty($summary)          , "query_summary: returns non-empty string");
unit_test(strpos($summary, 'Total Queries') !== false       , "query_summary: contains total queries");

///////////////////////////////////////
// sql_clean()
///////////////////////////////////////
print "\n";

$cleaned = $dbq->sql_clean("SELECT * FROM Customer WHERE CustID = 1;", 0);
unit_test(strpos($cleaned, 'SELECT') !== false              , "sql_clean: preserves SELECT (non-html mode)");

$cleaned_html = $dbq->sql_clean("SELECT * FROM Customer;", 1);
unit_test(strpos($cleaned_html, '<span') !== false           , "sql_clean: html mode adds spans");

///////////////////////////////////////
// is_cli()
///////////////////////////////////////
print "\n";

unit_test($dbq->is_cli() === true, "is_cli: returns true in CLI environment");

///////////////////////////////////////
// last_info() with no queries
///////////////////////////////////////
print "\n";

$dbq3 = new DBQuery("sqlite::memory:");
$info = $dbq3->last_info();
unit_test($info === [], "last_info: returns empty array with no queries run");

///////////////////////////////////////
// number_ordinal
///////////////////////////////////////
print "\n";

unit_test($dbq->number_ordinal(1)  === 'st', "number_ordinal: 1st");
unit_test($dbq->number_ordinal(2)  === 'nd', "number_ordinal: 2nd");
unit_test($dbq->number_ordinal(3)  === 'rd', "number_ordinal: 3rd");
unit_test($dbq->number_ordinal(4)  === 'th', "number_ordinal: 4th");
unit_test($dbq->number_ordinal(10) === 'th', "number_ordinal: 10th");
unit_test($dbq->number_ordinal(11) === 'th', "number_ordinal: 11th");
unit_test($dbq->number_ordinal(12) === 'th', "number_ordinal: 12th");
unit_test($dbq->number_ordinal(13) === 'th', "number_ordinal: 13th");
unit_test($dbq->number_ordinal(21) === 'st', "number_ordinal: 21st");
unit_test($dbq->number_ordinal(22) === 'nd', "number_ordinal: 22nd");
unit_test($dbq->number_ordinal(23) === 'rd', "number_ordinal: 23rd");
unit_test($dbq->number_ordinal(100) === 'th', "number_ordinal: 100th");
unit_test($dbq->number_ordinal(111) === 'th', "number_ordinal: 111th");
unit_test($dbq->number_ordinal(112) === 'th', "number_ordinal: 112th");
unit_test($dbq->number_ordinal(113) === 'th', "number_ordinal: 113th");

///////////////////////////////////////
// error_out()
///////////////////////////////////////
print "\n";

// No DBH connection present
$db_err = new DBQuery("sqlite::memory:");
$db_err->dbh = null;
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("SELECT 1;");
}, 15990, "error_out: DBH connection not present");

// Exception during prepare (PHP 8 PDO defaults to ERRMODE_EXCEPTION)
$db_err = new DBQuery("sqlite::memory:");
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("INVALID SQL;");
}, 23489, "error_out: prepare exception");

// Exception during execute
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_notnull (id INTEGER NOT NULL);");
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("INSERT INTO _err_notnull VALUES (NULL);");
}, 48203, "error_out: execute exception");

// record_limit exceeded - info_hash
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_rows (id INTEGER);");
$db_err->query("INSERT INTO _err_rows VALUES (1),(2),(3);");
$db_err->record_limit = 1;
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("SELECT * FROM _err_rows;", 'info_hash');
}, 12940, "error_out: info_hash exceeds record_limit");

// record_limit exceeded - info_hash with key
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_rows (id INTEGER);");
$db_err->query("INSERT INTO _err_rows VALUES (1),(2),(3);");
$db_err->record_limit = 1;
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("SELECT * FROM _err_rows;", 'info_hash|id');
}, 13039, "error_out: info_hash key exceeds record_limit");

// record_limit exceeded - info_list
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_rows (id INTEGER);");
$db_err->query("INSERT INTO _err_rows VALUES (1),(2),(3);");
$db_err->record_limit = 1;
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("SELECT * FROM _err_rows;", 'info_list');
}, 38103, "error_out: info_list exceeds record_limit");

// Unknown return type
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_rows (id INTEGER);");
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("SELECT * FROM _err_rows;", 'bogus');
}, 13843, "error_out: unknown return type");

// Unable to create a STH (silent mode, so prepare returns false)
$db_err = new DBQuery("sqlite::memory:");
$db_err->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("INVALID SQL;");
}, 34102, "error_out: unable to create STH", function($msg) {
	return is_array($msg) && strpos($msg[0], 'Unable to create') !== false;
});

// Syntax error detected via errorInfo (silent mode)
$db_err = new DBQuery("sqlite::memory:");
$db_err->query("CREATE TABLE _err_notnull (id INTEGER NOT NULL);");
$db_err->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
expect_error_out($db_err, function() use ($db_err) {
	$db_err->query("INSERT INTO _err_notnull VALUES (NULL);");
}, 34913, "error_out: syntax error from errorInfo");

// Default error number when none is passed
$db_err = new DBQuery("sqlite::memory:");
expect_error_out($db_err, function() use ($db_err) {
	$db_err->error_out("boom");
}, 89317, "error_out: default error number");

// show_errors false returns false without invoking the hook
$db_err = new DBQuery("sqlite::memory:");
$db_err->show_errors = false;
$hook_fired = false;
$db_err->external_error_function = function($m,$n) use (&$hook_fired) {
	$hook_fired = true;
	throw new RuntimeException("should not fire");
};
$ret = $db_err->error_out("x");
unit_test($ret === false && $hook_fired === false, "error_out: show_errors=false returns false and skips hook");

print "\n";
$exit_code = unit_test(-1,-1);
exit($exit_code);

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

function init_db() {
	$dir = dirname(__FILE__);
	$str = file_get_contents("$dir/db/test.sql");

	// Break up the SQL statements in to an array
	$sql = explode(";",$str);
	$sql = array_map('trim',$sql);
	$sql = array_filter($sql);

	require("$dir/../db_query.class.php");

	// Build an in-memory database
	$dsn = "sqlite::memory:";
	$dbq = new DBQuery($dsn);

	if (!$dbq) {
		print "Couldn't connect to the DB";
		exit;
	}

	// Execute each SQL query
	foreach ($sql as $i) {
		$dbq->query($i);
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

function unit_test($code,$name = "") {
	static $count = 0;
	static $good  = 0;
	static $bad   = 0;

	$ok = !!($code);

	$color_ok    = "\033[38;5;2m";
	$color_bad   = "\033[38;5;1m";
	$color_reset = "\033[0m";

	if ($name === -1) {
		if ($bad) {
			printf("%sFail%s: %d of %d tests passed (%0.2f%% failure rate)\n",$color_bad,$color_reset,$good,$count,($bad / $count) * 100);
			$ret = $bad;
		} else {
			printf("%sPass%s: %d of %d tests passed (%0.2f%% failure rate)\n",$color_ok,$color_reset,$good,$count,($bad / $count) * 100);
			$ret = 0;
		}

		return $ret;
	}

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

class ErrorOutException extends RuntimeException {
	public $msg;
	public $num;

	public function __construct($msg, $num) {
		$this->msg = $msg;
		$this->num = $num;
		parent::__construct("ErrorOut #$num");
	}
}

function expect_error_out($dbq, callable $fn, $expected_num, $name = "", $msg_check = null) {
	$prev_hook   = $dbq->external_error_function;
	$prev_errors = $dbq->show_errors;

	$dbq->show_errors = true;
	$dbq->external_error_function = function($m,$n) {
		throw new ErrorOutException($m,$n);
	};

	$caught = null;
	try {
		$fn();
	} catch (ErrorOutException $e) {
		$caught = $e;
	}

	$dbq->external_error_function = $prev_hook;
	$dbq->show_errors             = $prev_errors;

	$ok = ($caught !== null && $caught->num === $expected_num);
	if ($ok && $msg_check) {
		$ok = (bool) $msg_check($caught->msg);
	}

	return unit_test($ok, $name);
}
