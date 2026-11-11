<?php
/** Synthetic fixed-backup SQL reader tests. No site, database or private file. */
declare(strict_types=1);
require __DIR__ . '/inspect-live30-before.php';

$passed = 0;
function reader_check(bool $condition, string $name): void {
    global $passed;
    if (!$condition) { throw new RuntimeException('test failed: ' . $name); }
    ++$passed;
}
function reader_refuses(string $sql, string $name, ?string $reason = null): void {
    try { live30_extract($sql); }
    catch (Throwable $error) {
        reader_check(!str_contains($error->getMessage(), 'PRIVATE_') && ($reason === null || $error->getMessage() === $reason), $name);
        return;
    }
    throw new RuntimeException('test did not refuse: ' . $name);
}

$header = "/*!40101 SET NAMES utf8mb4 */;\n/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
$schema = "CREATE TABLE `fixture_postmeta` (`meta_id` bigint unsigned NOT NULL, `post_id` bigint unsigned NOT NULL, `meta_key` varchar(255) DEFAULT NULL, `meta_value` longtext, PRIMARY KEY (`meta_id`), KEY `post_id` (`post_id`));\n";
$insert = "INSERT INTO `fixture_postmeta` VALUES (4701,1107,'_uag_page_assets','plain'),(4702,1293,NULL,NULL);\n";
$base = $header . $schema . $insert;
$rows = live30_extract($base);
reader_check(count($rows) === 2 && $rows[0]['meta_id'] === 4701 && $rows[1]['meta_id'] === 4702, 'exact target identities');
$rowHash = hash('sha256', '{"meta_id":"4701","meta_key":"_uag_page_assets","meta_value":"plain","post_id":"1107"}');
$withoutHash = hash('sha256', '{"meta_key":"_uag_page_assets","meta_value":"plain","post_id":"1107"}');
reader_check($rows[0]['row_sha256'] === $rowHash && $rows[0]['without_meta_id_sha256'] === $withoutHash, 'canonical raw row hashes');
reader_check($rows[0]['witness_descriptor_sha256'] === hash('sha256', 'string:"' . $rowHash . '"'), 'installer witness descriptor binding');
reader_check($rows[0]['meta_key_sha256'] === hash('sha256', '_uag_page_assets') && $rows[0]['meta_value_bytes'] === 5 && $rows[0]['meta_value_sha256'] === hash('sha256', 'plain'), 'raw string descriptors');
reader_check($rows[1]['meta_key_sha256'] === null && $rows[1]['meta_value_bytes'] === null && $rows[1]['meta_value_sha256'] === null, 'SQL NULL preserved');

$cases = array(
    array("'a,b);(c;-- not comment /* data */'", 'a,b);(c;-- not comment /* data */'),
    array("'literal\nnewline'", "literal\nnewline"),
    array("'it\\'s'", "it's"),
    array("'it''s'", "it's"),
    array("'a\\\\b'", 'a\\b'),
    array("'\\0\\b\\n\\r\\t\\Z'", "\0\x08\n\r\t\x1a"),
    array("'a\\\"b'", 'a"b'),
    array("'\\%\\_'", '\\%\\_'),
    array("''", ''),
    array("'NULL'", 'NULL'),
    array("'ไทย 😀 /    '", "ไทย 😀 /    "),
    array('"double quoted"', 'double quoted'),
);
foreach ($cases as $index => [$literal, $expected]) {
    $actual = live30_extract($header . $schema . "INSERT INTO `fixture_postmeta` VALUES (4701,1107,'key',$literal),(4702,1293,NULL,NULL);\n");
    reader_check($actual[0]['meta_value_bytes'] === strlen($expected) && $actual[0]['meta_value_sha256'] === hash('sha256', $expected), 'quoted decode ' . $index);
}

$explicit = "INSERT INTO `fixture_postmeta` (`meta_id`,`post_id`,`meta_key`,`meta_value`) VALUES (4701,1107,'_uag_page_assets','plain');\nINSERT INTO `fixture_postmeta` VALUES (4702,1293,NULL,NULL);\n";
reader_check(live30_extract($header . $schema . $explicit) === $rows, 'explicit columns and separate inserts');
reader_check(live30_extract($base . "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n") === $rows, 'normal SQL mode restoration');
reader_check(live30_extract("DROP TABLE IF EXISTS `fixture_postmeta`;\n" . $header . $schema . "LOCK TABLES `fixture_postmeta` WRITE;\n/*!40000 ALTER TABLE `fixture_postmeta` DISABLE KEYS */;\n" . $insert . "/*!40000 ALTER TABLE `fixture_postmeta` ENABLE KEYS */;\nUNLOCK TABLES;\n") === $rows, 'normal dump locks and key toggles');
reader_check(live30_extract(str_replace("SQL_MODE='NO_AUTO_VALUE_ON_ZERO'", "SQL_MODE=''", $base)) === $rows, 'empty supported SQL mode');
$noise = "# INSERT INTO `fixture_postmeta` VALUES (4701,1,'PRIVATE_KEY','PRIVATE_VALUE');\n-- another comment\n/* INSERT INTO `fixture_postmeta` VALUES (4702,1,'PRIVATE_KEY','PRIVATE_VALUE'); */\nINSERT INTO `fixture_other` VALUES (4701,1,'PRIVATE_KEY','PRIVATE_VALUE');\n";
reader_check(live30_extract($noise . $base) === $rows, 'comments and other tables ignored');
$nonTargets = str_replace('VALUES (4701,', "VALUES (1,1,'4701','4702'),(4701,", $base);
reader_check(live30_extract($nonTargets) === $rows, 'target identities inside non-target text ignored');
$tokens = iterator_to_array(live30_tokens("`a``b` 'a''b' 'x;/*y*/' -- comment\n NULL 4701"));
reader_check($tokens === array(array('identifier','a`b'),array('string',"a'b"),array('string','x;/*y*/'),array('word','NULL'),array('number','4701')), 'token boundaries and escaped identifier');

$rejects = array(
    'missing target' => str_replace(",(4702,1293,NULL,NULL)", '', $base),
    'duplicate target' => $base . "INSERT INTO `fixture_postmeta` VALUES (4701,1,'PRIVATE_KEY','PRIVATE_VALUE');\n",
    'wrong insert columns' => $header . $schema . str_replace('(`meta_id`,`post_id`,`meta_key`,`meta_value`)', '(`post_id`,`meta_id`,`meta_key`,`meta_value`)', $explicit),
    'wrong schema columns' => str_replace('`meta_value` longtext', '`PRIVATE_COLUMN` longtext', $base),
    'extra row field' => str_replace("'plain')", "'plain','PRIVATE_EXTRA')", $base),
    'missing row field' => str_replace("1107,'_uag_page_assets',", "1107,", $base),
    'quoted numeric ID' => str_replace('(4701,', "('4701',", $base),
    'negative ID' => str_replace('(4701,', '(-4701,', $base),
    'leading zero ID' => str_replace('(4701,', '(04701,', $base),
    'unsupported expression' => str_replace("'plain'", "CONCAT('PRIVATE_VALUE','x')", $base),
    'unsupported hexadecimal literal' => str_replace("'plain'", '0xABCD', $base),
    'unknown escape' => str_replace("'plain'", "'PRIVATE_VALUE\\x41'", $base),
    'unterminated quote' => $header . $schema . "INSERT INTO `fixture_postmeta` VALUES (4701,1,'key','PRIVATE_VALUE",
    'dangling escape' => $header . $schema . "INSERT INTO `fixture_postmeta` VALUES (4701,1,'key','PRIVATE_VALUE\\",
    'unterminated comment' => $base . '/* PRIVATE_COMMENT',
    'unterminated statement' => rtrim($base, "\n;"),
    'truncated tuple' => $header . $schema . "INSERT INTO `fixture_postmeta` VALUES (4701,1107,'key');",
    'trailing insert junk' => str_replace('NULL,NULL);', 'NULL,NULL) PRIVATE_TRAILING;', $base),
    'missing charset' => str_replace("/*!40101 SET NAMES utf8mb4 */;\n", '', $base),
    'unsupported charset' => str_replace('NAMES utf8mb4', 'NAMES latin1', $base),
    'missing SQL mode' => str_replace("/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n", '', $base),
    'unsupported SQL mode' => str_replace("SQL_MODE='NO_AUTO_VALUE_ON_ZERO'", "SQL_MODE='NO_BACKSLASH_ESCAPES'", $base),
    'unsupported executable comment' => '/*M!100100 SET x=1 */;' . $base,
    'ambiguous schema' => $base . str_replace('fixture_postmeta', 'second_postmeta', $schema),
    'wrong table insert' => $header . $schema . str_replace('fixture_postmeta', 'second_postmeta', $insert),
    'missing schema' => $header . $insert,
    'post ID overflow' => str_replace('4701,1107,', '4701,99999999999999999999,', $base),
    'invalid UTF8 value' => str_replace("'plain'", "'\xff'", $base),
    'later delete ambiguity' => $base . 'DELETE FROM `fixture_postmeta` WHERE meta_id=4701;',
    'replace ambiguity' => $base . "REPLACE INTO `fixture_postmeta` VALUES (4701,1,'PRIVATE_KEY','PRIVATE_VALUE');",
    'insert ignore ambiguity' => str_replace('INSERT INTO', 'INSERT IGNORE INTO', $base),
    'delimiter change' => $base . 'DELIMITER $$;',
);
foreach ($rejects as $name => $sql) { reader_refuses($sql, $name); }

// Invalid CLI arguments are rejected before any attempt to access the fixed file.
$process = proc_open(array(PHP_BINARY, __DIR__ . '/inspect-live30-before.php', 'PRIVATE_ARGUMENT'), array(0 => array('pipe','r'), 1 => array('pipe','w'), 2 => array('pipe','w')), $pipes);
reader_check(is_resource($process), 'fixed-error subprocess starts');
fclose($pipes[0]); $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]); $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]); $status = proc_close($process);
reader_check($status === 1 && $stdout === '' && $stderr === "LIVE30-BEFORE-INVALID cli-no-arguments-required\n", 'fixed CLI failure is redacted');
echo "LIVE30 reader tests: $passed passed; synthetic SQL only, no database, site or private backup access.\n";
