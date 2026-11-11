<?php
/** Fixed LIVE #30 pre-write backup reader. CLI only; no WordPress or database. */
declare(strict_types=1);

function live30_require(bool $condition, string $reason): void {
    if (!$condition) { throw new RuntimeException($reason); }
}

/** MySQL dump tokens; quoted delimiters/comments are data, never SQL. */
function live30_tokens(string $sql): Generator {
    $length = strlen($sql);
    for ($i = 0; $i < $length;) {
        $c = $sql[$i];
        if (str_contains(" \t\r\n\f", $c)) { ++$i; continue; }
        if ($c === '#' || ($c === '-' && substr($sql, $i, 2) === '--' && ($i + 2 === $length || ord($sql[$i + 2]) <= 32))) {
            $end = strpos($sql, "\n", $i); $i = $end === false ? $length : $end + 1; continue;
        }
        if (substr($sql, $i, 2) === '/*') {
            $end = strpos($sql, '*/', $i + 2);
            live30_require($end !== false, 'unterminated-comment');
            if (substr($sql, $i, 3) === '/*!') {
                $body = substr($sql, $i + 3, $end - $i - 3);
                live30_require(preg_match('/^[0-9]{5,6}[ \t\r\n]/', $body, $match) === 1, 'unsupported-version-comment');
                yield from live30_tokens(substr($body, strlen($match[0])));
            } elseif (substr($sql, $i, 4) === '/*M!') {
                throw new RuntimeException('unsupported-executable-comment');
            }
            $i = $end + 2; continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c; $value = ''; $closed = false; ++$i;
            while ($i < $length) {
                $c = $sql[$i++];
                if ($c === $quote) {
                    if ($i < $length && $sql[$i] === $quote) { $value .= $quote; ++$i; continue; }
                    $closed = true; break;
                }
                if ($c === '\\') {
                    live30_require($quote !== '`' && $i < $length, 'invalid-quoted-escape');
                    $escaped = $sql[$i++];
                    $map = array('0' => "\0", 'b' => "\x08", 'n' => "\n", 'r' => "\r", 't' => "\t", 'Z' => "\x1a", '\\' => '\\', "'" => "'", '"' => '"', '%' => '\\%', '_' => '\\_');
                    // mysqldump emits this finite set. Refuse other forms rather than guess.
                    live30_require(array_key_exists($escaped, $map), 'unsupported-string-escape');
                    $value .= $map[$escaped];
                } else { $value .= $c; }
            }
            live30_require($closed, 'unterminated-quote');
            yield array($quote === '`' ? 'identifier' : 'string', $value); continue;
        }
        if (preg_match('/\G[A-Za-z_][A-Za-z0-9_$]*/A', $sql, $match, 0, $i)) {
            $i += strlen($match[0]); yield array('word', $match[0]); continue;
        }
        if (preg_match('/\G[0-9]+/A', $sql, $match, 0, $i)) {
            $i += strlen($match[0]); yield array('number', $match[0]); continue;
        }
        ++$i; yield array('symbol', $c);
    }
}

function live30_statements(string $sql): Generator {
    $statement = array();
    foreach (live30_tokens($sql) as $token) {
        if ($token === array('symbol', ';')) {
            if ($statement) { yield $statement; $statement = array(); }
        } else { $statement[] = $token; }
    }
    live30_require(!$statement, 'unterminated-statement');
}

function live30_is(array $tokens, int $index, string $word): bool {
    return isset($tokens[$index]) && $tokens[$index][0] === 'word' && strtoupper($tokens[$index][1]) === $word;
}

function live30_describe(array $row): array {
    ksort($row, SORT_STRING);
    $json_flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR;
    $hash = hash('sha256', json_encode($row, $json_flags));
    $without_id = $row; unset($without_id['meta_id']);
    live30_require((string) (int) $row['post_id'] === $row['post_id'], 'post-id-range');
    return array(
        'meta_id' => (int) $row['meta_id'], 'post_id' => (int) $row['post_id'],
        'meta_key_sha256' => $row['meta_key'] === null ? null : hash('sha256', $row['meta_key']),
        'meta_value_bytes' => $row['meta_value'] === null ? null : strlen($row['meta_value']),
        'meta_value_sha256' => $row['meta_value'] === null ? null : hash('sha256', $row['meta_value']),
        'row_sha256' => $hash,
        'without_meta_id_sha256' => hash('sha256', json_encode($without_id, $json_flags)),
        // Matches the missing witness descriptor in RELEASE-STATE-DIAGNOSTIC.
        'witness_descriptor_sha256' => hash('sha256', 'string:' . json_encode($hash, $json_flags)),
    );
}

function live30_extract(string $sql): array {
    $columns = array('meta_id', 'post_id', 'meta_key', 'meta_value');
    $table = null; $mode = false; $utf8 = false; $found = array();
    foreach (live30_statements($sql) as $tokens) {
        live30_require(!live30_is($tokens, 0, 'DELIMITER'), 'unsupported-delimiter');
        foreach ($tokens as $token) {
            if ($token[0] !== 'identifier' || !preg_match('/^[A-Za-z0-9_]+postmeta$/D', $token[1])) { continue; }
            $plain_insert = live30_is($tokens, 0, 'INSERT') && live30_is($tokens, 1, 'INTO');
            $create = live30_is($tokens, 0, 'CREATE') && live30_is($tokens, 1, 'TABLE');
            $drop = live30_is($tokens, 0, 'DROP') && live30_is($tokens, 1, 'TABLE') && $table === null;
            $lock = live30_is($tokens, 0, 'LOCK') && live30_is($tokens, 1, 'TABLES');
            $keys = live30_is($tokens, 0, 'ALTER') && live30_is($tokens, 1, 'TABLE') &&
                (live30_is($tokens, 3, 'ENABLE') || live30_is($tokens, 3, 'DISABLE')) && live30_is($tokens, 4, 'KEYS') && count($tokens) === 5;
            live30_require($plain_insert || $create || $drop || $lock || $keys, 'unsupported-postmeta-statement');
        }
        if (live30_is($tokens, 0, 'SET')) {
            if (live30_is($tokens, 1, 'NAMES')) {
                live30_require(isset($tokens[2]) && in_array($tokens[2][0], array('word', 'string'), true) && strtolower($tokens[2][1]) === 'utf8mb4', 'unsupported-dump-charset');
                $utf8 = true;
            }
            foreach ($tokens as $i => $token) {
                if (live30_is($tokens, $i, 'SQL_MODE') && ($tokens[$i - 1] ?? null) !== array('symbol', '@') && ($tokens[$i + 1] ?? null) === array('symbol', '=')) {
                    $value = $tokens[$i + 2] ?? null;
                    if ($value === array('symbol', '@') && ($tokens[$i + 3] ?? null) === array('word', 'OLD_SQL_MODE')) { $mode = false; }
                    else {
                        live30_require($value !== null && $value[0] === 'string' && in_array($value[1], array('', 'NO_AUTO_VALUE_ON_ZERO'), true), 'unsupported-sql-mode');
                        $mode = true;
                    }
                }
            }
        }
        if (live30_is($tokens, 0, 'CREATE') && live30_is($tokens, 1, 'TABLE') && ($tokens[2][0] ?? null) === 'identifier' && preg_match('/^[A-Za-z0-9_]+postmeta$/D', $tokens[2][1])) {
            live30_require($table === null && ($tokens[3] ?? null) === array('symbol', '('), 'ambiguous-postmeta-schema');
            $table = $tokens[2][1]; $actual = array(); $depth = 1; $start = true;
            for ($i = 4, $count = count($tokens); $i < $count && $depth > 0; ++$i) {
                $token = $tokens[$i];
                if ($depth === 1 && $start) { if ($token[0] === 'identifier') { $actual[] = $token[1]; } $start = false; }
                if ($token === array('symbol', '(')) { ++$depth; }
                elseif ($token === array('symbol', ')')) { --$depth; }
                elseif ($depth === 1 && $token === array('symbol', ',')) { $start = true; }
            }
            live30_require($depth === 0 && $actual === $columns, 'unsupported-postmeta-columns');
        }
        if (!live30_is($tokens, 0, 'INSERT') || !live30_is($tokens, 1, 'INTO')) { continue; }
        $name = $tokens[2] ?? null;
        if ($name === null || $name[0] !== 'identifier' || !preg_match('/^[A-Za-z0-9_]+postmeta$/D', $name[1])) { continue; }
        live30_require($table === $name[1] && $mode && $utf8, 'unbound-postmeta-insert');
        $i = 3;
        if (($tokens[$i] ?? null) === array('symbol', '(')) {
            $actual = array(); ++$i;
            do {
                live30_require(($tokens[$i][0] ?? null) === 'identifier', 'invalid-insert-columns');
                $actual[] = $tokens[$i++][1];
                if (($tokens[$i] ?? null) !== array('symbol', ',')) { break; } ++$i;
            } while (true);
            live30_require(($tokens[$i++] ?? null) === array('symbol', ')') && $actual === $columns, 'unsupported-insert-columns');
        }
        live30_require(live30_is($tokens, $i++, 'VALUES'), 'unsupported-insert-form');
        do {
            live30_require(($tokens[$i++] ?? null) === array('symbol', '('), 'missing-row-open');
            $row = array();
            foreach ($columns as $position => $column) {
                $token = $tokens[$i++] ?? null;
                if ($position < 2) {
                    live30_require($token !== null && $token[0] === 'number' && preg_match('/^(0|[1-9][0-9]{0,19})$/D', $token[1]) === 1, 'invalid-row-identity');
                    $row[$column] = $token[1];
                } else {
                    live30_require($token !== null && ($token[0] === 'string' || ($token[0] === 'word' && strtoupper($token[1]) === 'NULL')), 'unsupported-row-literal');
                    $row[$column] = $token[0] === 'string' ? $token[1] : null;
                }
                live30_require(($tokens[$i++] ?? null) === array('symbol', $position === 3 ? ')' : ','), 'invalid-row-separator');
            }
            if (in_array($row['meta_id'], array('4701', '4702'), true)) {
                live30_require(!isset($found[$row['meta_id']]), 'duplicate-target-row');
                $found[$row['meta_id']] = live30_describe($row);
            }
            if ($i === count($tokens)) { break; }
            live30_require(($tokens[$i++] ?? null) === array('symbol', ','), 'unexpected-insert-tail');
        } while (true);
    }
    live30_require($table !== null && count($found) === 2 && isset($found[4701], $found[4702]), 'missing-target-rows');
    ksort($found, SORT_NUMERIC); return array_values($found);
}

function live30_main(array $args): int {
    try {
        live30_require(PHP_SAPI === 'cli' && count($args) === 1, 'cli-no-arguments-required');
        $path = '/home/customer/private-backups/live-before-peek-release-20261111-162615-74851.sql';
        $sha = '4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0';
        $size = 3919398;
        live30_require(!is_link($path) && realpath($path) === $path && is_file($path), 'backup-path-invalid');
        $sql = @file_get_contents($path, false, null, 0, $size + 1);
        live30_require(is_string($sql) && strlen($sql) === $size && hash('sha256', $sql) === $sha, 'backup-bytes-differ');
        $rows = live30_extract($sql);
        echo 'LIVE30-BEFORE ' . json_encode(array('backup_sha256' => $sha, 'backup_bytes' => $size, 'rows' => $rows), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        return 0;
    } catch (Throwable $error) {
        // Exceptions are fixed parser reasons only; never expose SQL/token/value text.
        $reason = $error instanceof RuntimeException ? $error->getMessage() : 'parse-or-encoding-failure';
        fwrite(STDERR, 'LIVE30-BEFORE-INVALID ' . $reason . "\n"); return 1;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { exit(live30_main($argv)); }
