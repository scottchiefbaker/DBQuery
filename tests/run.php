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
unit_test($data === null, "SELECT: OneData with no matches returns null");

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
unit_test($data === null                      , "SELECT: Prepared statement with one_data no match returns null");

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
unit_test($data === 1, "REPLACE: auto-detected returns insert id");

$data = $dbq->query("REPLACE INTO Customer (First, Last, City, State, Zipcode, CustID) VALUES ('ReplaceTest', 'User', 'Nowhere', 'XX', 12345, 1);", 'affected_rows');
unit_test($data === 1, "REPLACE: explicit affected_rows returns affected rows");

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

$call_db = new DBQuery("sqlite::memory:");
$call_db->show_errors = false;
$call_db->query("SELECT 1 AS n;", 'info_hash');
$call_info = $call_db->last_info();
unit_test(isset($call_info['called_from_file']) && is_string($call_info['called_from_file']), "call location: file is present and a string");
unit_test(isset($call_info['called_from_line']) && is_int($call_info['called_from_line']) && $call_info['called_from_line'] > 0, "call location: line is present and positive");

$location_method = new ReflectionMethod(DBQuery::class, 'get_call_location');
$fallback_location = $location_method->invoke($call_db);
unit_test(isset($fallback_location['file'], $fallback_location['line']) && is_string($fallback_location['file']) && is_int($fallback_location['line']), "call location: helper always returns guarded file and line keys");

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
	return is_string($msg) && strpos($msg, 'Unable to create') !== false;
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

// Normal path must not write to stdout for non-array return values
$db_quiet = new DBQuery("sqlite::memory:");
$db_quiet->query("CREATE TABLE _quiet (id INTEGER PRIMARY KEY, v TEXT);");
ob_start();
$quiet_id   = $db_quiet->query("INSERT INTO _quiet (v) VALUES ('a');", "insert_id");
$quiet_one  = $db_quiet->query("SELECT v FROM _quiet WHERE id = 1;", "one_data");
$quiet_aff  = $db_quiet->query("UPDATE _quiet SET v = 'b';", "affected_rows");
$quiet_out  = ob_get_clean();
unit_test($quiet_out === "", "normal path: no stdout output for scalar return types");
unit_test($quiet_id === 1 && $quiet_one === 'a' && $quiet_aff === 1, "normal path: scalar return values still correct");

// sql_clean escapes SQL before highlighting so user input cannot inject markup
$xss_html = $db_quiet->sql_clean("SELECT 1 WHERE v = '<script>x</script>'");
unit_test(strpos($xss_html, "<script>") === false && strpos($xss_html, "&lt;script&gt;") !== false, "sql_clean: html-escapes SQL when htmlizing");
unit_test(strpos($db_quiet->sql_clean("SELECT 1 WHERE v = '<b>'", 0), "<b>") !== false, "sql_clean: plain text mode is not escaped");

// Bound parameter values are html-escaped before being shown in error output
$param_html = htmlentities(join(", ", array_map('strval', array("<script>x</script>", null, 5))), ENT_QUOTES, 'UTF-8');
unit_test(strpos($param_html, "<script>") === false && strpos($param_html, "&lt;script&gt;") !== false, "params: bound values are html-escaped");

// Password is only retained when delay_connect needs it later
$db_cred = new DBQuery("sqlite::memory:", "", "secret");
$cred_prop = new ReflectionProperty(DBQuery::class, 'pass');
unit_test($cred_prop->getValue($db_cred) === "", "credentials: password not kept for eager connect");
$db_cred_delay = new DBQuery("sqlite::memory:", "", "secret", ['delay_connect' => true]);
unit_test($cred_prop->getValue($db_cred_delay) === "secret", "credentials: password kept for delayed connect");

///////////////////////////////////////
// Chunked fetch
///////////////////////////////////////
print "\n";

function fetch_db() {
	$d = new DBQuery("sqlite::memory:");
	$d->show_errors = false;
	$d->query("CREATE TABLE _fetch (id INTEGER PRIMARY KEY, g TEXT);");
	$d->query("INSERT INTO _fetch (g) VALUES ('a'),('b'),('c'),('d'),('e');");
	return $d;
}

$fd = fetch_db();
$started = $fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
unit_test($started === true, "fetch: starting a fetch returns true");

$chunk1 = $fd->query("", 'info_hash');
$chunk2 = $fd->query("", 'info_hash');
$chunk3 = $fd->query("", 'info_hash');
unit_test(sizeof($chunk1) === 2 && $chunk1[0]['g'] === 'a', "fetch: first chunk honours fetch:2 limit");
unit_test(sizeof($chunk2) === 2 && $chunk2[0]['g'] === 'c', "fetch: second chunk continues from where it left off");
unit_test(sizeof($chunk3) === 1 && $chunk3[0]['g'] === 'e', "fetch: final partial chunk returns remaining rows");

$drained = $fd->query("", 'info_hash');
unit_test($drained === [], "fetch: pulling past the end returns an empty array");

$after = $fd->query("SELECT COUNT(*) AS n FROM _fetch;", 'info_hash');
unit_test($after[0]['n'] == 5, "fetch: state is cleared so a new query runs after draining");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$fd->query("", 'info_hash');
$fd->query("", 'info_hash');
$fd->query("", 'info_hash');
$fd->query("", 'info_hash');
$fd->query("", 'info_hash');
$cur = $fd->query("SELECT COUNT(*) AS n FROM _fetch;", 'info_hash');
unit_test($cur[0]['n'] == 5, "fetch: pulling past end repeatedly does not break later queries");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$blocked = $fd->query("SELECT 1;", 'info_hash');
unit_test($blocked === false, "fetch: starting new SQL mid-fetch is rejected");
$still = $fd->query("", 'info_hash');
unit_test(sizeof($still) === 2 && $still[0]['g'] === 'a', "fetch: rejected SQL does not disturb the in-progress fetch");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$wrong = $fd->query("", 'info_list');
unit_test($wrong === false, "fetch: non-info_hash pull mid-fetch is rejected");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch');
$all = $fd->query("", 'info_hash');
unit_test(sizeof($all) === 5, "fetch: bare 'fetch' uses default chunk size of 500");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$k1 = $fd->query("", 'info_hash|g');
unit_test(is_assoc($k1) && array_keys($k1) === ['a','b'], "fetch: keyed chunk is keyed by column value");
$k2 = $fd->query("", 'info_hash|g');
unit_test(array_keys($k2) === ['c','d'], "fetch: keyed second chunk continues");
$k3 = $fd->query("", 'info_hash|g');
unit_test(array_keys($k3) === ['e'], "fetch: keyed final partial chunk");
$kend = $fd->query("", 'info_hash|g');
unit_test($kend === [], "fetch: keyed pull past end returns empty array");

$fd = fetch_db();
$fd->query("INSERT INTO _fetch (g) VALUES ('a');");
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$dup1 = $fd->query("", 'info_hash|g');
unit_test(sizeof($dup1) === 2, "fetch: keyed chunk with duplicate keys returns distinct keys");
$fd->query("", 'info_hash|g');
$fd->query("", 'info_hash|g');
$fd->query("", 'info_hash|g');
$dupcheck = $fd->query("SELECT COUNT(*) AS n FROM _fetch;", 'info_hash');
unit_test($dupcheck[0]['n'] == 6, "fetch: keyed fetch with duplicate keys still clears state after draining");

$fd = fetch_db();
$fd->query("SELECT * FROM _fetch ORDER BY id;", 'fetch:2');
$arr_mode = $fd->query("", 'info_hash|g[]');
unit_test(is_array($arr_mode['a']) && sizeof($arr_mode['a']) === 1, "fetch: keyed [] mode collects rows into arrays");

///////////////////////////////////////
// Return types: success paths and record_limit errors
///////////////////////////////////////
print "\n";

$rt = new DBQuery("sqlite::memory:");
$rt->query("CREATE TABLE _rt (id INTEGER, v TEXT);");
$rt->query("INSERT INTO _rt VALUES (1,'x'),(2,'y'),(3,'z');");

$data = $rt->query("SELECT * FROM _rt ORDER BY id;", 'info_list');
unit_test(is_numeric_array($data) && sizeof($data) === 3 && $data[1][1] === 'y', "info_list: returns numeric rows of values");

$data = $rt->query("SELECT id FROM _rt ORDER BY id;", 'one_column');
unit_test($data === [1,2,3], "one_column: returns flat list of first column");

$data = $rt->query("SELECT v, id FROM _rt ORDER BY id;", 'key_value');
unit_test($data === ['x'=>1,'y'=>2,'z'=>3], "key_value: first column is key, second is value");

$rt->show_errors = false;
unit_test($rt->query("SELECT * FROM _rt;", 'xinfo_hash:MissingField') === false, "info_hash: unanchored hint prefix is rejected");
$rt->show_errors = true;

$rt->query("CREATE TABLE _kp (k TEXT, v TEXT);");
$rt->query("INSERT INTO _kp VALUES ('0','zero'), ('','empty'), ('x','letter');");
$data = $rt->query("SELECT * FROM _kp ORDER BY v;", 'key_pair:k,v');
unit_test(array_key_exists(0, $data) && $data[0] === 'zero', "key_pair: numeric string key '0' is retained");
unit_test(array_key_exists('', $data) && $data[''] === 'empty', "key_pair: empty key is retained");

expect_error_out($rt, function() use ($rt) {
	$rt->query("SELECT * FROM _rt;", 'info_hash|MissingField');
}, 38108, "info_hash: missing key field reports a clear shape error", function($msg) {
	return is_string($msg) && strpos($msg, 'MissingField') !== false;
});
$rt->show_errors = false;
$data = $rt->query("SELECT * FROM _rt;", 'info_hash|MissingField');
unit_test($data === false, "info_hash: missing key field returns false silently when show_errors=false");
$rt->show_errors = true;

$rt->show_errors = false;
$data = $rt->query("SELECT id FROM _rt ORDER BY id;", 'key_value');
unit_test($data === false, "key_value: one-column result returns false silently when show_errors=false");
$rt->show_errors = true;
expect_error_out($rt, function() use ($rt) {
	$rt->query("SELECT id FROM _rt ORDER BY id;", 'key_value');
}, 38107, "key_value: one-column result reports a clear shape error", function($msg) {
	return is_string($msg) && strpos($msg, 'at least two columns') !== false;
});

$data = $rt->query("SELECT * FROM _rt WHERE id = 99;", 'one_row_list');
unit_test($data === [], "one_row_list: no match returns empty array");

$data = $rt->query("SELECT * FROM _rt WHERE id = 1;", 'one_row_list');
unit_test($data === [1,'x'], "one_row_list: returns the matching row as a numeric array");

$rl = new DBQuery("sqlite::memory:");
$rl->query("CREATE TABLE _rl (id INTEGER, v TEXT);");
$rl->query("INSERT INTO _rl VALUES (1,'x'),(2,'y'),(3,'z');");
$rl->record_limit = 1;
expect_error_out($rl, function() use ($rl) {
	$rl->query("SELECT * FROM _rl;", 'key_value');
}, 38104, "error_out: key_value exceeds record_limit");

$rl = new DBQuery("sqlite::memory:");
$rl->query("CREATE TABLE _rl (id INTEGER, v TEXT);");
$rl->query("INSERT INTO _rl VALUES (1,'x'),(2,'y'),(3,'z');");
$rl->record_limit = 1;
expect_error_out($rl, function() use ($rl) {
	$rl->query("SELECT * FROM _rl;", 'key_pair:id,v');
}, 38105, "error_out: key_pair exceeds record_limit");

$rl = new DBQuery("sqlite::memory:");
$rl->query("CREATE TABLE _rl (id INTEGER, v TEXT);");
$rl->query("INSERT INTO _rl VALUES (1,'x'),(2,'y'),(3,'z');");
$rl->record_limit = 1;
expect_error_out($rl, function() use ($rl) {
	$rl->query("SELECT * FROM _rl;", 'one_column');
}, 38106, "error_out: one_column exceeds record_limit");

$rl->show_errors = false;
$rl->record_limit = 0;
$zero_limit = $rl->query("SELECT * FROM _rl ORDER BY id;", 'info_hash');
unit_test(sizeof($zero_limit) === 3, "record_limit: zero is treated as unlimited");

$rl->record_limit = -1;
$negative_limit = $rl->query("SELECT * FROM _rl ORDER BY id;", 'info_hash');
unit_test(sizeof($negative_limit) === 3, "record_limit: negative values are treated as unlimited");

///////////////////////////////////////
// Auto-detect SHOW / EXECUTE and LOCK / UNLOCK
///////////////////////////////////////
print "\n";

$lk = new DBQuery("sqlite::memory:");
$lk->show_errors = false;
unit_test($lk->query("LOCK TABLES _x;") === false, "LOCK: unsupported by SQLite, returns false silently");
unit_test($lk->query("UNLOCK TABLES;") === false, "UNLOCK: unsupported by SQLite, returns false silently");

///////////////////////////////////////
// show_errors=false on exception paths (no exit, returns false)
///////////////////////////////////////
print "\n";

$se = new DBQuery("sqlite::memory:");
$se->show_errors = false;
unit_test($se->query("INVALID SQL;") === false, "show_errors=false: prepare exception returns false");

$se->query("CREATE TABLE _se (id INTEGER NOT NULL);");
unit_test($se->query("INSERT INTO _se VALUES (NULL);") === false, "show_errors=false: execute exception returns false");

$se->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
unit_test($se->query("INVALID SQL;") === false, "show_errors=false: unable to create STH returns false");
unit_test(!empty($se->last_info()) && $se->last_info()['error_code'] !== 0, "show_errors=false: failed prepare is recorded in query info");

$se2 = new DBQuery("sqlite::memory:");
$se2->show_errors = false;
$se2->query("CREATE TABLE _se2 (id INTEGER NOT NULL);");
$se2->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
unit_test($se2->query("INSERT INTO _se2 VALUES (NULL);") === false, "show_errors=false: syntax error from errorInfo returns false");

///////////////////////////////////////
// external_error_function hook fires on error when show_errors is true
///////////////////////////////////////
print "\n";

$hook = new DBQuery("sqlite::memory:");
$hook->query("CREATE TABLE _hook (id INTEGER, v TEXT);");
$hook->query("INSERT INTO _hook VALUES (1,'a'),(2,'b');");
$hook->record_limit = 1;
$hook_calls = [];
$hook->external_error_function = function($m,$n) use (&$hook_calls) {
	$hook_calls[] = $n;
	throw new ErrorOutException($m,$n);
};
try {
	$hook->query("SELECT * FROM _hook;", 'info_hash');
} catch (ErrorOutException $e) {}
unit_test($hook_calls === [12940], "hook: external_error_function is invoked with the error number");

///////////////////////////////////////
// query_summary() rendering
///////////////////////////////////////
print "\n";

$qs = new DBQuery("sqlite::memory:");
$qs->query("CREATE TABLE _qs (id INTEGER, v TEXT);");
$qs->query("INSERT INTO _qs VALUES (1,'a');");
for ($i = 0; $i < 2; $i++) {
	$qs->query("SELECT * FROM _qs WHERE id = ?;", [1], 'info_hash');
}

$summary = $qs->query_summary();
unit_test(strpos($summary, '2nd call') !== false, "query_summary: repeated call from the same line shows ordinal");
unit_test(strpos($summary, 'Values') !== false, "query_summary: bound parameter values are shown");
unit_test(strpos($summary, 'Return: <b>info_hash</b>') !== false, "query_summary: shows return type");

$qs->slow_query_time = -1;
$slow = $qs->query_summary();
unit_test(strpos($slow, 'Slow Query threshold') !== false, "query_summary: queries above threshold are flagged");

$qs->slow_query_time = 1000000;
$fast = $qs->query_summary();
unit_test(strpos($fast, 'Slow Query threshold') === false, "query_summary: queries below threshold are not flagged");

$qe = new DBQuery("sqlite::memory:");
$qe->query("CREATE TABLE _qe (id INTEGER NOT NULL);");
$qe->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$qe->query("INSERT INTO _qe VALUES (NULL);", 'no_error');
unit_test(count($qe->db_query_info) === 1, "query_summary: failed query under no_error is not recorded in query info");

$qn = new DBQuery("sqlite::memory:");
$qn->query("CREATE TABLE _qn (v TEXT);");
$qn->query("INSERT INTO _qn VALUES (NULL);");
$qn->query("INSERT INTO _qn VALUES (?);", [''], 'affected_rows');
$qn->query("SELECT * FROM _qn WHERE v = ?;", [null], 'info_hash');
unit_test(strpos($qn->query_summary(), 'NULL_STRING') !== false, "query_summary: empty string param shows NULL_STRING");
unit_test(strpos($qn->query_summary(), '<b style="color: #811D0D;">NULL</b>') !== false, "query_summary: null param shows NULL marker");

///////////////////////////////////////
// debug_log()
///////////////////////////////////////
print "\n";

$dl = new DBQuery("sqlite::memory:");
$dl->debug = 1;
ob_start();
$dl->debug_log("hello world");
$out_on = ob_get_clean();
unit_test($out_on === "<div>hello world</div>\n", "debug_log: prints when debug is enabled");

$dl->debug = 0;
ob_start();
$dl->debug_log("silent");
$out_off = ob_get_clean();
unit_test($out_off === "", "debug_log: silent when debug is disabled");

///////////////////////////////////////
// query_log file output
///////////////////////////////////////
print "\n";

$log_file = sys_get_temp_dir() . "/db_query_test_" . getmypid() . ".csv";
@unlink($log_file);
touch($log_file);

$ql = new DBQuery("sqlite::memory:");
$ql->query("CREATE TABLE _ql (id INTEGER);");
$ql->query_log = $log_file;
$ql->query("INSERT INTO _ql VALUES (1);", 'affected_rows');
$log_contents = file_get_contents($log_file);
unit_test(strpos($log_contents, "INSERT INTO _ql VALUES (1);") !== false, "query_log: writes executed SQL to the log file");
unit_test(preg_match('/^"\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}","INSERT INTO _ql VALUES \(1\);","[\d.]+","1"$/m', $log_contents) === 1, "query_log: each line is date,sql,time,records");

$ql->query_log = "/nonexistent_dir_for_test/x.csv";
$unwritable = $ql->query("INSERT INTO _ql VALUES (2);");
unit_test($unwritable === 2, "query_log: unwritable path does not break the query");
unlink($log_file);

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

	$color_pass  = "\033[38;5;2m";
	$color_fail  = "\033[38;5;1m";
	$color_reset = "\033[0m";
	$white       = "\033[1;15m";

	if ($name === -1) {
		if ($bad) {
			printf("%sFail%s: %d of %d tests passed (%0.2f%% failure rate)\n", $color_fail, $color_reset, $good, $count, ($bad / $count) * 100);
			$ret = $bad;
		} else {
			printf("%sPass%s: %d of %d tests passed (%0.2f%% failure rate)\n", $color_pass, $color_reset, $good, $count, ($bad / $count) * 100);
			$ret = 0;
		}

		return $ret;
	}

	if ($ok) {
		printf("%s[ %sPASS%s ]%s - %s\n", $white, $color_pass, $color_reset . $white, $color_reset, $name);
		$good++;
	} else {
		printf("%s[ %sFAIL%s ]%s - %s\n", $white, $color_fail, $color_reset . $white, $color_reset, $name);
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
