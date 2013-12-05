<?PHP

$dir = dirname(__FILE__);

require("$dir/../db_query.inc.php");
$file = "$dir/db/test.sqlite";

if (!is_readable($file)) {
	print "DB is not readable: '$file'\n";
	exit;
}

$dsn = "sqlite:$file";
$dbq = new db_query($dsn);

$sql  = "SELECT * FROM Customer LIMIT 3;";
$data = $dbq->query($sql);

print_r($data);
