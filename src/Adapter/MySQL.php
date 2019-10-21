<?php
namespace Xicrow\PhpSqlFormatter\Adapter;

use Xicrow\PhpSqlFormatter\Tokenizer\Token;
use Xicrow\PhpSqlFormatter\Tokenizer\TokenCollection;
use Xicrow\PhpSqlFormatter\Tokenizer\Tokenizer;
use Xicrow\PhpSqlFormatter\Tokenizer\TokenRule;

/**
 * Class MySQL
 *
 * @package Xicrow\PhpSqlFormatter\Adapter
 */
class MySQL implements AdapterInterface
{
	/**
	 * Constants for token types
	 */
	public const TokenType_Error            = 'Error';
	public const TokenType_Space            = 'Space';
	public const TokenType_Tab              = 'Tab';
	public const TokenType_Newline          = 'Newline';
	public const TokenType_Comment          = 'Comment';
	public const TokenType_Number           = 'Number';
	public const TokenType_String           = 'String';
	public const TokenType_Function         = 'Function';
	public const TokenType_TopLevelKeyword  = 'TopLevelKeyword';
	public const TokenType_Keyword          = 'Keyword';
	public const TokenType_Operator         = 'Operator';
	public const TokenType_ParenthesisStart = 'ParenthesisStart';
	public const TokenType_ParenthesisEnd   = 'ParenthesisEnd';
	public const TokenType_BracketStart     = 'BracketStart';
	public const TokenType_BracketEnd       = 'BracketEnd';
	public const TokenType_Variable         = 'Variable';
	public const TokenType_Identifier       = 'Identifier';
	public const TokenType_Parameter        = 'Parameter';
	public const TokenType_Unknown          = 'Unknown';

	/**
	 * Constants for action types
	 */
	public const ActionType_Compressed          = 'Compressed';
	public const ActionType_Formatted           = 'Formatted';
	public const ActionType_Highlighted         = 'Highlighted';
	public const ActionType_StrippedForComments = 'StrippedForComments';
	public const ActionType_Obfusticated        = 'Obfusticated';

	/**
	 * Format for WEB highlighting
	 *
	 * @var array
	 */
	public $highlightFormatWeb = [
		self::TokenType_Error            => '<span style="color: #FF0000;" title="' . self::TokenType_Error . '">%s</span>',
		self::TokenType_Space            => '<span style="color: #333333;" title="' . self::TokenType_Space . '">%s</span>',
		self::TokenType_Tab              => '<span style="color: #333333;" title="' . self::TokenType_Tab . '">%s</span>',
		self::TokenType_Newline          => '<span style="color: #333333;" title="' . self::TokenType_Newline . '">%s</span>',
		self::TokenType_Comment          => '<span style="color: #A0A0A0;" title="' . self::TokenType_Comment . '">%s</span>',
		self::TokenType_Function         => '<span style="color: #C040C0;" title="' . self::TokenType_Function . '">%s</span>',
		self::TokenType_TopLevelKeyword  => '<span style="color: #007FBF;" title="' . self::TokenType_TopLevelKeyword . '">%s</span>',
		self::TokenType_Keyword          => '<span style="color: #007FBF;" title="' . self::TokenType_Keyword . '">%s</span>',
		self::TokenType_Operator         => '<span style="color: #FF0000;" title="' . self::TokenType_Operator . '">%s</span>',
		self::TokenType_Identifier       => '<span style="color: #7D5A27;" title="' . self::TokenType_Identifier . '">%s</span>',
		self::TokenType_Parameter        => '<span style="color: #7D5A27;" title="' . self::TokenType_Parameter . '">%s</span>',
		self::TokenType_Variable         => '<span style="color: #C040C0;" title="' . self::TokenType_Variable . '">%s</span>',
		self::TokenType_Number           => '<span style="color: #F0A030;" title="' . self::TokenType_Number . '">%s</span>',
		self::TokenType_String           => '<span style="color: #209F20;" title="' . self::TokenType_String . '">%s</span>',
		self::TokenType_ParenthesisStart => '<span style="color: #888888;" title="' . self::TokenType_ParenthesisStart . '">%s</span>',
		self::TokenType_ParenthesisEnd   => '<span style="color: #888888;" title="' . self::TokenType_ParenthesisEnd . '">%s</span>',
		self::TokenType_BracketStart     => '<span style="color: #888888;" title="' . self::TokenType_BracketStart . '">%s</span>',
		self::TokenType_BracketEnd       => '<span style="color: #888888;" title="' . self::TokenType_BracketEnd . '">%s</span>',
	];

	/**
	 * Format for CLI highlighting
	 *
	 * @see https://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors
	 * @see https://misc.flogisoft.com/bash/tip_colors_and_formatting
	 *
	 * @var array
	 */
	public $highlightFormatCli = [
		self::TokenType_Error            => "\e[0;31m" . "%s" . "\e[0m",
		self::TokenType_Space            => "\e[1;30m" . "%s" . "\e[0m",
		self::TokenType_Tab              => "\e[1;30m" . "%s" . "\e[0m",
		self::TokenType_Newline          => "\e[1;30m" . "%s" . "\e[0m",
		self::TokenType_Comment          => "\e[0;37m" . "%s" . "\e[0m",
		self::TokenType_Function         => "\e[1;35m" . "%s" . "\e[0m",
		self::TokenType_TopLevelKeyword  => "\e[0;96m" . "%s" . "\e[0m",
		self::TokenType_Keyword          => "\e[0;96m" . "%s" . "\e[0m",
		self::TokenType_Operator         => "\e[0;31m" . "%s" . "\e[0m",
		self::TokenType_Identifier       => "\e[0;97m" . "%s" . "\e[0m",
		self::TokenType_Parameter        => "\e[0;97m" . "%s" . "\e[0m",
		self::TokenType_Variable         => "\e[1;97m" . "%s" . "\e[0m",
		self::TokenType_Number           => "\e[1;33m" . "%s" . "\e[0m",
		self::TokenType_String           => "\e[1;32m" . "%s" . "\e[0m",
		self::TokenType_ParenthesisStart => "\e[1;37m" . "%s" . "\e[0m",
		self::TokenType_ParenthesisEnd   => "\e[1;37m" . "%s" . "\e[0m",
		self::TokenType_BracketStart     => "\e[1;37m" . "%s" . "\e[0m",
		self::TokenType_BracketEnd       => "\e[1;37m" . "%s" . "\e[0m",
	];

	/** @var bool */
	private $isCli = false;
	/** @var string */
	private $renderTabCharacter = "\t";
	/** @var string */
	private $original = '';
	/** @var string */
	private $parsed = '';
	/** @var array */
	private $actionsTaken = [];

	/**
	 * MySQL top level keywords
	 * Will be placed on their own line by themselves
	 *
	 * @var array
	 */
	private $topLevelKeywords = [
		'SELECT',
		'FROM',
		'JOIN',
		'CROSS JOIN',
		'LEFT JOIN',
		'LEFT OUTER JOIN',
		'RIGHT JOIN',
		'RIGHT OUTER JOIN',
		'FULL JOIN',
		'INNER JOIN',
		'OUTER JOIN',
		'WHERE',
		'GROUP BY',
		'HAVING',
		'ORDER BY',
		'LIMIT',
		'INSERT INTO',
		'INSERT IGNORE INTO',
		'VALUES',
		'UPDATE',
		'SET',
		'ALTER TABLE',
		'ADD',
		'DELETE FROM',
	];

	/**
	 * MySQL 5.7 keywords and reserved words
	 *
	 * @see https://dev.mysql.com/doc/refman/5.7/en/keywords.html
	 * @var array
	 */
	private $keywords = [
		'ACCESSIBLE',
		'ACCOUNT',
		'ACTION',
		'ADD',
		'AFTER',
		'AGAINST',
		'AGGREGATE',
		'ALGORITHM',
		'ALL',
		'ALTER',
		'ALWAYS',
		'ANALYSE',
		'ANALYZE',
		'AND',
		'ANY',
		'AS',
		'ASC',
		'ASCII',
		'ASENSITIVE',
		'AT',
		'AUTOEXTEND_SIZE',
		'AUTO_INCREMENT',
		'AVG',
		'AVG_ROW_LENGTH',
		'BACKUP',
		'BEFORE',
		'BEGIN',
		'BETWEEN',
		'BIGINT',
		'BINARY',
		'BINLOG',
		'BIT',
		'BLOB',
		'BLOCK',
		'BOOL',
		'BOOLEAN',
		'BOTH',
		'BTREE',
		'BY',
		'BYTE',
		'CACHE',
		'CALL',
		'CASCADE',
		'CASCADED',
		'CASE',
		'CATALOG_NAME',
		'CHAIN',
		'CHANGE',
		'CHANGED',
		'CHANNEL',
		'CHAR',
		'CHARACTER',
		'CHARSET',
		'CHECK',
		'CHECKSUM',
		'CIPHER',
		'CLASS_ORIGIN',
		'CLIENT',
		'CLOSE',
		'COALESCE',
		'CODE',
		'COLLATE',
		'COLLATION',
		'COLUMN',
		'COLUMNS',
		'COLUMN_FORMAT',
		'COLUMN_NAME',
		'COMMENT',
		'COMMIT',
		'COMMITTED',
		'COMPACT',
		'COMPLETION',
		'COMPRESSED',
		'COMPRESSION',
		'CONCURRENT',
		'CONDITION',
		'CONNECTION',
		'CONSISTENT',
		'CONSTRAINT',
		'CONSTRAINT_CATALOG',
		'CONSTRAINT_NAME',
		'CONSTRAINT_SCHEMA',
		'CONTAINS',
		'CONTEXT',
		'CONTINUE',
		'CONVERT',
		'CPU',
		'CREATE',
		'CROSS',
		'CUBE',
		'CURRENT',
		'CURRENT_DATE',
		'CURRENT_TIME',
		'CURRENT_TIMESTAMP',
		'CURRENT_USER',
		'CURSOR',
		'CURSOR_NAME',
		'DATA',
		'DATABASE',
		'DATABASES',
		'DATAFILE',
		'DATE',
		'DATETIME',
		'DAY',
		'DAY_HOUR',
		'DAY_MICROSECOND',
		'DAY_MINUTE',
		'DAY_SECOND',
		'DEALLOCATE',
		'DEC',
		'DECIMAL',
		'DECLARE',
		'DEFAULT',
		'DEFAULT_AUTH',
		'DEFINER',
		'DELAYED',
		'DELAY_KEY_WRITE',
		'DELETE',
		'DESC',
		'DESCRIBE',
		'DES_KEY_FILE',
		'DETERMINISTIC',
		'DIAGNOSTICS',
		'DIRECTORY',
		'DISABLE',
		'DISCARD',
		'DISK',
		'DISTINCT',
		'DISTINCTROW',
		'DIV',
		'DO',
		'DOUBLE',
		'DROP',
		'DUAL',
		'DUMPFILE',
		'DUPLICATE',
		'DYNAMIC',
		'EACH',
		'ELSE',
		'ELSEIF',
		'ENABLE',
		'ENCLOSED',
		'ENCRYPTION',
		'END',
		'ENDS',
		'ENGINE',
		'ENGINES',
		'ENUM',
		'ERROR',
		'ERRORS',
		'ESCAPE',
		'ESCAPED',
		'EVENT',
		'EVENTS',
		'EVERY',
		'EXCHANGE',
		'EXECUTE',
		'EXISTS',
		'EXIT',
		'EXPANSION',
		'EXPIRE',
		'EXPLAIN',
		'EXPORT',
		'EXTENDED',
		'EXTENT_SIZE',
		'FALSE',
		'FAST',
		'FAULTS',
		'FETCH',
		'FIELDS',
		'FILE',
		'FILE_BLOCK_SIZE',
		'FILTER',
		'FIRST',
		'FIXED',
		'FLOAT',
		'FLOAT4',
		'FLOAT8',
		'FLUSH',
		'FOLLOWS',
		'FOR',
		'FORCE',
		'FOREIGN',
		'FORMAT',
		'FOUND',
		'FROM',
		'FULL',
		'FULLTEXT',
		'FUNCTION',
		'GENERAL',
		'GENERATED',
		'GEOMETRY',
		'GEOMETRYCOLLECTION',
		'GET',
		'GET_FORMAT',
		'GLOBAL',
		'GRANT',
		'GRANTS',
		'GROUP',
		'GROUP_REPLICATION',
		'HANDLER',
		'HASH',
		'HAVING',
		'HELP',
		'HIGH_PRIORITY',
		'HOST',
		'HOSTS',
		'HOUR',
		'HOUR_MICROSECOND',
		'HOUR_MINUTE',
		'HOUR_SECOND',
		'IDENTIFIED',
		'IF',
		'IGNORE',
		'IGNORE_SERVER_IDS',
		'IMPORT',
		'IN',
		'INDEX',
		'INDEXES',
		'INFILE',
		'INITIAL_SIZE',
		'INNER',
		'INOUT',
		'INSENSITIVE',
		'INSERT',
		'INSERT_METHOD',
		'INSTALL',
		'INSTANCE',
		'INT',
		'INT1',
		'INT2',
		'INT3',
		'INT4',
		'INT8',
		'INTEGER',
		'INTERVAL',
		'INTO',
		'INVOKER',
		'IO',
		'IO_AFTER_GTIDS',
		'IO_BEFORE_GTIDS',
		'IO_THREAD',
		'IPC',
		'IS',
		'IS NOT',
		'IS NULL',
		'IS NOT NULL',
		'ISOLATION',
		'ISSUER',
		'ITERATE',
		'JOIN',
		'JSON',
		'KEY',
		'KEYS',
		'KEY_BLOCK_SIZE',
		'KILL',
		'LANGUAGE',
		'LAST',
		'LEADING',
		'LEAVE',
		'LEAVES',
		'LEFT',
		'LESS',
		'LEVEL',
		'LIKE',
		'LIMIT',
		'LINEAR',
		'LINES',
		'LINESTRING',
		'LIST',
		'LOAD',
		'LOCAL',
		'LOCALTIME',
		'LOCALTIMESTAMP',
		'LOCK',
		'LOCKS',
		'LOGFILE',
		'LOGS',
		'LONG',
		'LONGBLOB',
		'LONGTEXT',
		'LOOP',
		'LOW_PRIORITY',
		'MASTER',
		'MASTER_AUTO_POSITION',
		'MASTER_BIND',
		'MASTER_CONNECT_RETRY',
		'MASTER_DELAY',
		'MASTER_HEARTBEAT_PERIOD',
		'MASTER_HOST',
		'MASTER_LOG_FILE',
		'MASTER_LOG_POS',
		'MASTER_PASSWORD',
		'MASTER_PORT',
		'MASTER_RETRY_COUNT',
		'MASTER_SERVER_ID',
		'MASTER_SSL',
		'MASTER_SSL_CA',
		'MASTER_SSL_CAPATH',
		'MASTER_SSL_CERT',
		'MASTER_SSL_CIPHER',
		'MASTER_SSL_CRL',
		'MASTER_SSL_CRLPATH',
		'MASTER_SSL_KEY',
		'MASTER_SSL_VERIFY_SERVER_CERT',
		'MASTER_TLS_VERSION',
		'MASTER_USER',
		'MATCH',
		'MAXVALUE',
		'MAX_CONNECTIONS_PER_HOUR',
		'MAX_QUERIES_PER_HOUR',
		'MAX_ROWS',
		'MAX_SIZE',
		'MAX_STATEMENT_TIME',
		'MAX_UPDATES_PER_HOUR',
		'MAX_USER_CONNECTIONS',
		'MEDIUM',
		'MEDIUMBLOB',
		'MEDIUMINT',
		'MEDIUMTEXT',
		'MEMORY',
		'MERGE',
		'MESSAGE_TEXT',
		'MICROSECOND',
		'MIDDLEINT',
		'MIGRATE',
		'MINUTE',
		'MINUTE_MICROSECOND',
		'MINUTE_SECOND',
		'MIN_ROWS',
		'MOD',
		'MODE',
		'MODIFIES',
		'MODIFY',
		'MONTH',
		'MULTILINESTRING',
		'MULTIPOINT',
		'MULTIPOLYGON',
		'MUTEX',
		'MYSQL_ERRNO',
		'NAME',
		'NAMES',
		'NATIONAL',
		'NATURAL',
		'NCHAR',
		'NDB',
		'NDBCLUSTER',
		'NEVER',
		'NEW',
		'NEXT',
		'NO',
		'NODEGROUP',
		'NONBLOCKING',
		'NONE',
		'NOT',
		'NO_WAIT',
		'NO_WRITE_TO_BINLOG',
		'NULL',
		'NUMBER',
		'NUMERIC',
		'NVARCHAR',
		'OFFSET',
		'OLD_PASSWORD',
		'ON',
		'ONE',
		'ONLY',
		'OPEN',
		'OPTIMIZE',
		'OPTIMIZER_COSTS',
		'OPTION',
		'OPTIONALLY',
		'OPTIONS',
		'OR',
		'ORDER',
		'OUT',
		'OUTER',
		'OUTFILE',
		'OWNER',
		'PACK_KEYS',
		'PAGE',
		'PARSER',
		'PARSE_GCOL_EXPR',
		'PARTIAL',
		'PARTITION',
		'PARTITIONING',
		'PARTITIONS',
		'PASSWORD',
		'PHASE',
		'PLUGIN',
		'PLUGINS',
		'PLUGIN_DIR',
		'POINT',
		'POLYGON',
		'PORT',
		'PRECEDES',
		'PRECISION',
		'PREPARE',
		'PRESERVE',
		'PREV',
		'PRIMARY',
		'PRIVILEGES',
		'PROCEDURE',
		'PROCESSLIST',
		'PROFILE',
		'PROFILES',
		'PROXY',
		'PURGE',
		'QUARTER',
		'QUERY',
		'QUICK',
		'RANGE',
		'READ',
		'READS',
		'READ_ONLY',
		'READ_WRITE',
		'REAL',
		'REBUILD',
		'RECOVER',
		'REDOFILE',
		'REDO_BUFFER_SIZE',
		'REDUNDANT',
		'REFERENCES',
		'REGEXP',
		'RELAY',
		'RELAYLOG',
		'RELAY_LOG_FILE',
		'RELAY_LOG_POS',
		'RELAY_THREAD',
		'RELEASE',
		'RELOAD',
		'REMOVE',
		'RENAME',
		'REORGANIZE',
		'REPAIR',
		'REPEAT',
		'REPEATABLE',
		'REPLACE',
		'REPLICATE_DO_DB',
		'REPLICATE_DO_TABLE',
		'REPLICATE_IGNORE_DB',
		'REPLICATE_IGNORE_TABLE',
		'REPLICATE_REWRITE_DB',
		'REPLICATE_WILD_DO_TABLE',
		'REPLICATE_WILD_IGNORE_TABLE',
		'REPLICATION',
		'REQUIRE',
		'RESET',
		'RESIGNAL',
		'RESTORE',
		'RESTRICT',
		'RESUME',
		'RETURN',
		'RETURNED_SQLSTATE',
		'RETURNS',
		'REVERSE',
		'REVOKE',
		'RIGHT',
		'RLIKE',
		'ROLLBACK',
		'ROLLUP',
		'ROTATE',
		'ROUTINE',
		'ROW',
		'ROWS',
		'ROW_COUNT',
		'ROW_FORMAT',
		'RTREE',
		'SAVEPOINT',
		'SCHEDULE',
		'SCHEMA',
		'SCHEMAS',
		'SCHEMA_NAME',
		'SECOND',
		'SECOND_MICROSECOND',
		'SECURITY',
		'SELECT',
		'SENSITIVE',
		'SEPARATOR',
		'SERIAL',
		'SERIALIZABLE',
		'SERVER',
		'SESSION',
		'SET',
		'SHARE',
		'SHOW',
		'SHUTDOWN',
		'SIGNAL',
		'SIGNED',
		'SIMPLE',
		'SLAVE',
		'SLOW',
		'SMALLINT',
		'SNAPSHOT',
		'SOCKET',
		'SOME',
		'SONAME',
		'SOUNDS',
		'SOURCE',
		'SPATIAL',
		'SPECIFIC',
		'SQL',
		'SQLEXCEPTION',
		'SQLSTATE',
		'SQLWARNING',
		'SQL_AFTER_GTIDS',
		'SQL_AFTER_MTS_GAPS',
		'SQL_BEFORE_GTIDS',
		'SQL_BIG_RESULT',
		'SQL_BUFFER_RESULT',
		'SQL_CACHE',
		'SQL_CALC_FOUND_ROWS',
		'SQL_NO_CACHE',
		'SQL_SMALL_RESULT',
		'SQL_THREAD',
		'SQL_TSI_DAY',
		'SQL_TSI_HOUR',
		'SQL_TSI_MINUTE',
		'SQL_TSI_MONTH',
		'SQL_TSI_QUARTER',
		'SQL_TSI_SECOND',
		'SQL_TSI_WEEK',
		'SQL_TSI_YEAR',
		'SSL',
		'STACKED',
		'START',
		'STARTING',
		'STARTS',
		'STATS_AUTO_RECALC',
		'STATS_PERSISTENT',
		'STATS_SAMPLE_PAGES',
		'STATUS',
		'STOP',
		'STORAGE',
		'STORED',
		'STRAIGHT_JOIN',
		'STRING',
		'SUBCLASS_ORIGIN',
		'SUBJECT',
		'SUBPARTITION',
		'SUBPARTITIONS',
		'SUPER',
		'SUSPEND',
		'SWAPS',
		'SWITCHES',
		'TABLE',
		'TABLES',
		'TABLESPACE',
		'TABLE_CHECKSUM',
		'TABLE_NAME',
		'TEMPORARY',
		'TEMPTABLE',
		'TERMINATED',
		'TEXT',
		'THAN',
		'THEN',
		'TIME',
		'TIMESTAMP',
		'TIMESTAMPADD',
		'TIMESTAMPDIFF',
		'TINYBLOB',
		'TINYINT',
		'TINYTEXT',
		'TO',
		'TRAILING',
		'TRANSACTION',
		'TRIGGER',
		'TRIGGERS',
		'TRUE',
		'TRUNCATE',
		'TYPE',
		'TYPES',
		'UNCOMMITTED',
		'UNDEFINED',
		'UNDO',
		'UNDOFILE',
		'UNDO_BUFFER_SIZE',
		'UNICODE',
		'UNINSTALL',
		'UNION',
		'UNIQUE',
		'UNKNOWN',
		'UNLOCK',
		'UNSIGNED',
		'UNTIL',
		'UPDATE',
		'UPGRADE',
		'USAGE',
		'USE',
		'USER',
		'USER_RESOURCES',
		'USE_FRM',
		'USING',
		'UTC_DATE',
		'UTC_TIME',
		'UTC_TIMESTAMP',
		'VALIDATION',
		'VALUE',
		'VALUES',
		'VARBINARY',
		'VARCHAR',
		'VARCHARACTER',
		'VARIABLES',
		'VARYING',
		'VIEW',
		'VIRTUAL',
		'WAIT',
		'WARNINGS',
		'WEEK',
		'WEIGHT_STRING',
		'WHEN',
		'WHERE',
		'WHILE',
		'WITH',
		'WITHOUT',
		'WORK',
		'WRAPPER',
		'WRITE',
		'X509',
		'XA',
		'XID',
		'XML',
		'XOR',
		'YEAR',
		'YEAR_MONTH',
		'ZEROFILL',
	];

	/**
	 * MySQL 5.7 functions
	 *
	 * @see https://dev.mysql.com/doc/refman/5.7/en/func-op-summary-ref.html
	 * @var array
	 */
	private $functions = [
		'ABS',
		'ACOS',
		'ADDDATE',
		'ADDTIME',
		'AES_DECRYPT',
		'AES_ENCRYPT',
		'ANY_VALUE',
		'Area',
		'AsBinary',
		'AsWKB',
		'ASCII',
		'ASIN',
		'AsText',
		'AsWKT',
		'ASYMMETRIC_DECRYPT',
		'ASYMMETRIC_DERIVE',
		'ASYMMETRIC_ENCRYPT',
		'ASYMMETRIC_SIGN',
		'ASYMMETRIC_VERIFY',
		'ATAN',
		'ATAN2',
		'ATAN',
		'AVG',
		'BENCHMARK',
		'BIN',
		'BIT_AND',
		'BIT_COUNT',
		'BIT_LENGTH',
		'BIT_OR',
		'BIT_XOR',
		'Buffer',
		'CAST',
		'CEIL',
		'CEILING',
		'Centroid',
		'CHAR',
		'CHAR_LENGTH',
		'CHARACTER_LENGTH',
		'CHARSET',
		'COALESCE',
		'COERCIBILITY',
		'COLLATION',
		'COMPRESS',
		'CONCAT',
		'CONCAT_WS',
		'CONNECTION_ID',
		'Contains',
		'CONV',
		'CONVERT',
		'CONVERT_TZ',
		'ConvexHull',
		'COS',
		'COT',
		'COUNT',
		'CRC32',
		'CREATE_ASYMMETRIC_PRIV_KEY',
		'CREATE_ASYMMETRIC_PUB_KEY',
		'CREATE_DH_PARAMETERS',
		'CREATE_DIGEST',
		'Crosses',
		'CURDATE',
		'CURRENT_DATE',
		'CURRENT_TIME',
		'CURRENT_TIMESTAMP',
		'CURRENT_USER',
		'CURTIME',
		'DATABASE',
		'DATE',
		'DATE_ADD',
		'DATE_FORMAT',
		'DATE_SUB',
		'DATEDIFF',
		'DAY',
		'DAYNAME',
		'DAYOFMONTH',
		'DAYOFWEEK',
		'DAYOFYEAR',
		'DECODE',
		'DEFAULT',
		'DEGREES',
		'DES_DECRYPT',
		'DES_ENCRYPT',
		'Dimension',
		'Disjoint',
		'Distance',
		'ELT',
		'ENCODE',
		'ENCRYPT',
		'EndPoint',
		'Envelope',
		'Equals',
		'EXP',
		'EXPORT_SET',
		'ExteriorRing',
		'EXTRACT',
		'ExtractValue',
		'FIELD',
		'FIND_IN_SET',
		'FLOOR',
		'FORMAT',
		'FOUND_ROWS',
		'FROM_BASE64',
		'FROM_DAYS',
		'FROM_UNIXTIME',
		'GeomCollFromText',
		'GeometryCollectionFromText',
		'GeomCollFromWKB',
		'GeometryCollectionFromWKB',
		'GeometryCollection',
		'GeometryN',
		'GeometryType',
		'GeomFromText',
		'GeometryFromText',
		'GeomFromWKB',
		'GeometryFromWKB',
		'GET_FORMAT',
		'GET_LOCK',
		'GLength',
		'GREATEST',
		'GROUP_CONCAT',
		'GTID_SUBSET',
		'GTID_SUBTRACT',
		'HEX',
		'HOUR',
		'IF',
		'IFNULL',
		'IN',
		'INET_ATON',
		'INET_NTOA',
		'INET6_ATON',
		'INET6_NTOA',
		'INSERT',
		'INSTR',
		'InteriorRingN',
		'Intersects',
		'INTERVAL',
		'IS_FREE_LOCK',
		'IS_IPV4',
		'IS_IPV4_COMPAT',
		'IS_IPV4_MAPPED',
		'IS_IPV6',
		'IS_USED_LOCK',
		'IsClosed',
		'IsEmpty',
		'ISNULL',
		'IsSimple',
		'JSON_APPEND',
		'JSON_ARRAY',
		'JSON_ARRAY_APPEND',
		'JSON_ARRAY_INSERT',
		'JSON_ARRAYAGG',
		'JSON_CONTAINS',
		'JSON_CONTAINS_PATH',
		'JSON_DEPTH',
		'JSON_EXTRACT',
		'JSON_INSERT',
		'JSON_KEYS',
		'JSON_LENGTH',
		'JSON_MERGE',
		'JSON_MERGE_PATCH',
		'JSON_MERGE_PRESERVE',
		'JSON_OBJECT',
		'JSON_OBJECTAGG',
		'JSON_PRETTY',
		'JSON_QUOTE',
		'JSON_REMOVE',
		'JSON_REPLACE',
		'JSON_SEARCH',
		'JSON_SET',
		'JSON_STORAGE_SIZE',
		'JSON_TYPE',
		'JSON_UNQUOTE',
		'JSON_VALID',
		'LAST_INSERT_ID',
		'LCASE',
		'LEAST',
		'LEFT',
		'LENGTH',
		'LineFromText',
		'LineStringFromText',
		'LineFromWKB',
		'LineStringFromWKB',
		'LineString',
		'LN',
		'LOAD_FILE',
		'LOCALTIME',
		'LOCALTIMESTAMP',
		'LOCATE',
		'LOG',
		'LOG10',
		'LOG2',
		'LOWER',
		'LPAD',
		'LTRIM',
		'MAKE_SET',
		'MAKEDATE',
		'MAKETIME',
		'MASTER_POS_WAIT',
		'MAX',
		'MBRContains',
		'MBRCoveredBy',
		'MBRCovers',
		'MBRDisjoint',
		'MBREqual',
		'MBREquals',
		'MBRIntersects',
		'MBROverlaps',
		'MBRTouches',
		'MBRWithin',
		'MD5',
		'MICROSECOND',
		'MID',
		'MIN',
		'MINUTE',
		'MLineFromText',
		'MultiLineStringFromText',
		'MLineFromWKB',
		'MultiLineStringFromWKB',
		'MOD',
		'MONTH',
		'MONTHNAME',
		'MPointFromText',
		'MultiPointFromText',
		'MPointFromWKB',
		'MultiPointFromWKB',
		'MPolyFromText',
		'MultiPolygonFromText',
		'MPolyFromWKB',
		'MultiPolygonFromWKB',
		'MultiLineString',
		'MultiPoint',
		'MultiPolygon',
		'NAME_CONST',
		'NOT IN',
		'NOW',
		'NULLIF',
		'NumGeometries',
		'NumInteriorRings',
		'NumPoints',
		'OCT',
		'OCTET_LENGTH',
		'OLD_PASSWORD',
		'ORD',
		'Overlaps',
		'PASSWORD',
		'PERIOD_ADD',
		'PERIOD_DIFF',
		'PI',
		'Point',
		'PointFromText',
		'PointFromWKB',
		'PointN',
		'PolyFromText',
		'PolygonFromText',
		'PolyFromWKB',
		'PolygonFromWKB',
		'Polygon',
		'POSITION',
		'POW',
		'POWER',
		'PROCEDURE ANALYSE',
		'QUARTER',
		'QUOTE',
		'RADIANS',
		'RAND',
		'RANDOM_BYTES',
		'RELEASE_ALL_LOCKS',
		'RELEASE_LOCK',
		'REPEAT',
		'REPLACE',
		'REVERSE',
		'RIGHT',
		'ROUND',
		'ROW_COUNT',
		'RPAD',
		'RTRIM',
		'SCHEMA',
		'SEC_TO_TIME',
		'SECOND',
		'SESSION_USER',
		'SHA1',
		'SHA',
		'SHA2',
		'SIGN',
		'SIN',
		'SLEEP',
		'SOUNDEX',
		'SPACE',
		'SQRT',
		'SRID',
		'ST_Area',
		'ST_AsBinary',
		'ST_AsWKB',
		'ST_AsGeoJSON',
		'ST_AsText',
		'ST_AsWKT',
		'ST_Buffer',
		'ST_Buffer_Strategy',
		'ST_Centroid',
		'ST_Contains',
		'ST_ConvexHull',
		'ST_Crosses',
		'ST_Difference',
		'ST_Dimension',
		'ST_Disjoint',
		'ST_Distance',
		'ST_Distance_Sphere',
		'ST_EndPoint',
		'ST_Envelope',
		'ST_Equals',
		'ST_ExteriorRing',
		'ST_GeoHash',
		'ST_GeomCollFromText',
		'ST_GeometryCollectionFromText',
		'ST_GeomCollFromTxt',
		'ST_GeomCollFromWKB',
		'ST_GeometryCollectionFromWKB',
		'ST_GeometryN',
		'ST_GeometryType',
		'ST_GeomFromGeoJSON',
		'ST_GeomFromText',
		'ST_GeometryFromText',
		'ST_GeomFromWKB',
		'ST_GeometryFromWKB',
		'ST_InteriorRingN',
		'ST_Intersection',
		'ST_Intersects',
		'ST_IsClosed',
		'ST_IsEmpty',
		'ST_IsSimple',
		'ST_IsValid',
		'ST_LatFromGeoHash',
		'ST_Length',
		'ST_LineFromText',
		'ST_LineStringFromText',
		'ST_LineFromWKB',
		'ST_LineStringFromWKB',
		'ST_LongFromGeoHash',
		'ST_MakeEnvelope',
		'ST_MLineFromText',
		'ST_MultiLineStringFromText',
		'ST_MLineFromWKB',
		'ST_MultiLineStringFromWKB',
		'ST_MPointFromText',
		'ST_MultiPointFromText',
		'ST_MPointFromWKB',
		'ST_MultiPointFromWKB',
		'ST_MPolyFromText',
		'ST_MultiPolygonFromText',
		'ST_MPolyFromWKB',
		'ST_MultiPolygonFromWKB',
		'ST_NumGeometries',
		'ST_NumInteriorRing',
		'ST_NumInteriorRings',
		'ST_NumPoints',
		'ST_Overlaps',
		'ST_PointFromGeoHash',
		'ST_PointFromText',
		'ST_PointFromWKB',
		'ST_PointN',
		'ST_PolyFromText',
		'ST_PolygonFromText',
		'ST_PolyFromWKB',
		'ST_PolygonFromWKB',
		'ST_Simplify',
		'ST_SRID',
		'ST_StartPoint',
		'ST_SymDifference',
		'ST_Touches',
		'ST_Union',
		'ST_Validate',
		'ST_Within',
		'ST_X',
		'ST_Y',
		'StartPoint',
		'STD',
		'STDDEV',
		'STDDEV_POP',
		'STDDEV_SAMP',
		'STR_TO_DATE',
		'STRCMP',
		'SUBDATE',
		'SUBSTR',
		'SUBSTRING',
		'SUBSTRING_INDEX',
		'SUBTIME',
		'SUM',
		'SYSDATE',
		'SYSTEM_USER',
		'TAN',
		'TIME',
		'TIME_FORMAT',
		'TIME_TO_SEC',
		'TIMEDIFF',
		'TIMESTAMP',
		'TIMESTAMPADD',
		'TIMESTAMPDIFF',
		'TO_BASE64',
		'TO_DAYS',
		'TO_SECONDS',
		'Touches',
		'TRIM',
		'TRUNCATE',
		'UCASE',
		'UNCOMPRESS',
		'UNCOMPRESSED_LENGTH',
		'UNHEX',
		'UNIX_TIMESTAMP',
		'UpdateXML',
		'UPPER',
		'USER',
		'UTC_DATE',
		'UTC_TIME',
		'UTC_TIMESTAMP',
		'UUID',
		'UUID_SHORT',
		'VALIDATE_PASSWORD_STRENGTH',
		'VALUES',
		'VAR_POP',
		'VAR_SAMP',
		'VARIANCE',
		'VERSION',
		'WAIT_FOR_EXECUTED_GTID_SET',
		'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
		'WAIT_FOR_EXECUTED_GTID_SET',
		'WEEK',
		'WEEKDAY',
		'WEEKOFYEAR',
		'WEIGHT_STRING',
		'Within',
		'X',
		'Y',
		'YEAR',
		'YEARWEEK',
	];

	/**
	 * MySQL 5.7 operators
	 *
	 * @see https://dev.mysql.com/doc/refman/5.7/en/func-op-summary-ref.html
	 * @var array
	 */
	private $operators = [
		'AND',
		'&&',
		'=',
		'SET',
		'SET',
		'UPDATE',
		':=',
		'BINARY',
		'&',
		'~',
		'|',
		'^',
		'CASE',
		'COUNT(DISTINCT)',
		'CURRENT_DATE',
		'CURRENT_TIME',
		'CURRENT_TIMESTAMP',
		'CURRENT_USER',
		'DIV',
		'/',
		'=',
		'<=>',
		'>',
		'>=',
		'->',
		'->>',
		'LAST_DAY',
		'<<',
		'<',
		'<=',
		'LIKE',
		'LOCALTIME',
		'LOCALTIMESTAMP',
		'MATCH',
		'-',
		'%',
		'MOD',
		'NOT',
		'!',
		'!=',
		'<>',
		'NOT LIKE',
		'NOT REGEXP',
		'OR',
		'||',
		'+',
		'REGEXP',
		'>>',
		'RLIKE',
		'SOUNDS LIKE',
		'*',
		'-',
		'XOR',
	];

	/**
	 * MySQL logical operators
	 *
	 * @var array
	 */
	private $logicalOperators = [
		'AND',
		'&&',
		'NOT',
		'OR',
		'||',
		'XOR',
	];

	/**
	 * Timer for monitoring time spent
	 *
	 * @var int|mixed
	 */
	private $timeStart = 0;

	/**
	 * @inheritDoc
	 */
	public function __construct(string $sql)
	{
		$this->isCli              = (php_sapi_name() == 'cli');
		$this->renderTabCharacter = ($this->isCli ? '    ' : "\t");

		$this->original = $sql;
		$this->parsed   = $sql;

		$this->timeStart = microtime(true);
	}

	/**
	 * @inheritDoc
	 */
	public function compress(): AdapterInterface
	{
		if (in_array(static::ActionType_Compressed, $this->actionsTaken)) {
			return $this;
		}

		if (!in_array(static::ActionType_StrippedForComments, $this->actionsTaken)) {
			$this->stripComments();
		}

		$this->parsed = str_replace("\n", ' ', $this->parsed);
		$this->parsed = str_replace("\r", ' ', $this->parsed);
		$this->parsed = str_replace("\t", ' ', $this->parsed);

		$this->parsed = preg_replace('/ {2,}/', ' ', $this->parsed);

		$this->parsed = str_replace(' )', ')', $this->parsed);
		$this->parsed = str_replace('( ', '(', $this->parsed);

		$this->parsed = trim($this->parsed);

		$this->actionsTaken[] = static::ActionType_Compressed;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function format(): AdapterInterface
	{
		if (in_array(static::ActionType_Formatted, $this->actionsTaken)) {
			return $this;
		}
		if (in_array(static::ActionType_Highlighted, $this->actionsTaken)) {
			return $this;
		}

		// Normalize newlines
		$this->parsed = str_replace("\r", "\n", $this->parsed);

		$this->parsed = $this->formatSql($this->parsed);

		$this->actionsTaken[] = static::ActionType_Formatted;

		return $this;
	}

	/**
	 * Format SQL
	 *
	 * @param string $sql
	 * @param int    $indentation
	 * @return string
	 */
	protected function formatSql(string $sql, int $indentation = 0): string
	{
		// Get tokens
		$tokens = $this->getTokens($sql);

		// Filter out whitespace
		$tokens = $tokens->filter([
			static::TokenType_Space,
			static::TokenType_Tab,
			static::TokenType_Newline,
		], false);

		if ($indentation === 0) {
			// Make keywords, functions and operator uppercase
			foreach ($tokens as $tokenIndex => $token) {
				if ($token->isTypeIn([static::TokenType_TopLevelKeyword, static::TokenType_Keyword, static::TokenType_Function, static::TokenType_Operator])) {
					$tokens[$tokenIndex]->setContent(strtoupper($token->getContent()));
				}
			}

			// Check for unmatched parenthesis
			$unmatchedParenthesis = 0;
			foreach ($tokens as $tokenIndex => $token) {
				if ($token->isType(static::TokenType_ParenthesisStart)) {
					$unmatchedParenthesis++;
				}
				if ($token->isType(static::TokenType_ParenthesisEnd)) {
					$unmatchedParenthesis--;
				}
			}
			if ($unmatchedParenthesis > 0) {
				return $sql . "\n\n" . 'ERROR: ' . $unmatchedParenthesis . ' unmatched parenthesis';
			}
		}

		$addNewline                     = false;
		$blockIndentation               = $indentation;
		$inlineParenthesis              = false;
		$inlineParenthesisLevel         = 0;
		$parenthesisLevel               = 0;
		$parenthesisLevelTriggerNewline = null;
		$subqueryDetected               = false;
		$subqueryLevel                  = 0;
		$subqueryContent                = '';

		$formatted = '';
		for ($tokenIndex = 0; $tokenIndex < count($tokens); $tokenIndex++) {
			$token         = $tokens[$tokenIndex];
			$previousToken = $tokens[$tokenIndex - 1] ?? new Token();
			$nextToken     = $tokens[$tokenIndex + 1] ?? new Token();

			if ($addNewline) {
				$addNewline = false;
				$formatted  .= "\n";
				if ($blockIndentation > 0) {
					$formatted .= str_repeat("\t", $blockIndentation);
				}
			}

			if (!$subqueryDetected && $token->isContent('SELECT') && $previousToken->isContent('(')) {
				$subqueryDetected = true;
				$subqueryLevel    = 1;
			}
			if ($subqueryDetected && $token->isContent('(')) {
				$subqueryLevel++;
			}
			if ($subqueryDetected && $token->isContent(')')) {
				$subqueryLevel--;
			}
			if ($subqueryDetected && $subqueryLevel === 0) {
				$formatted .= $this->formatSql($subqueryContent, $blockIndentation);

				$subqueryDetected = false;
				$subqueryContent  = '';
				$subqueryLevel    = 0;
			}
			if ($subqueryDetected) {
				if ($subqueryContent !== '') {
					$subqueryContent .= ' ';
				}
				$subqueryContent .= $token->getContent();
				continue;
			}

			if ($token->getType() === static::TokenType_Comment) {
				$comment = $token->getContent();
				if (strpos($comment, "\n") !== false) {
					$comment = preg_replace('/\n\s*/', "\n" . str_repeat("\t", $blockIndentation), $comment);
				} elseif ($formatted !== '' && !in_array(substr($formatted, -1), ["\n", "\t"])) {
					$formatted .= ' ';
				}
				$formatted .= $comment;

				$addNewline = true;
			} elseif ($token->getType() === static::TokenType_TopLevelKeyword) {
				$addNewline       = true;
				$blockIndentation = $indentation + 1;

				if ($formatted !== '' && substr($formatted, -1) !== "\n") {
					$formatted .= "\n";
					$formatted .= str_repeat("\t", $indentation);
				}
				$formatted .= $token->getContent();
			} elseif ($token->getType() === static::TokenType_Keyword && $token->isContentIn(['ON'])) {
				$addNewline = true;
				$blockIndentation++;

				$formatted .= ' ';
				$formatted .= $token->getContent();
			} elseif ($token->isContentIn($this->logicalOperators)) {
				if ($formatted !== '') {
					if ($inlineParenthesis) {
						$formatted .= ' ';
					} else {
						$formatted .= "\n";
						$formatted .= str_repeat("\t", $blockIndentation);
					}
				}

				$formatted .= $token->getContent();
			} elseif ($token->getType() === static::TokenType_ParenthesisStart) {
				if (!$inlineParenthesis) {
					if($previousToken->isType(static::TokenType_Identifier) && $nextToken->isType(static::TokenType_Identifier)){

					} else {
						$nextParenthesisTokenIndex = $tokenIndex + 1;
						$tmpLevel                  = $parenthesisLevel + 1;
						while (isset($tokens[$nextParenthesisTokenIndex])) {
							$nextParenthesisToken = $tokens[$nextParenthesisTokenIndex];

							if ($nextParenthesisToken->isTypeIn([static::TokenType_TopLevelKeyword, static::TokenType_Function])) {
								break;
							}

							if ($nextParenthesisToken->isType(static::TokenType_ParenthesisStart)) {
								$tmpLevel++;
							}
							if ($nextParenthesisToken->isType(static::TokenType_ParenthesisEnd)) {
								$tmpLevel--;

								if ($tmpLevel === $parenthesisLevel) {
									$inlineParenthesis      = true;
									$inlineParenthesisLevel = $parenthesisLevel + 1;
									break;
								}
							}

							$nextParenthesisTokenIndex++;
						}
					}
				}

				if (!$inlineParenthesis) {
					$addNewline = true;
				}
				if ($previousToken->isTypeIn([static::TokenType_Keyword, static::TokenType_Operator]) && !in_array(substr($formatted, -1), ["\n", "\t"])) {
					$formatted .= ' ';
				}
				$formatted .= $token->getContent();

				$parenthesisLevel++;
				$blockIndentation++;
			} elseif ($token->getType() === static::TokenType_ParenthesisEnd) {
				if (!$inlineParenthesis) {
					$formatted .= "\n";
					$formatted .= str_repeat("\t", $blockIndentation - 1);
				}
				if ($inlineParenthesis && $parenthesisLevel === $inlineParenthesisLevel) {
					$inlineParenthesis      = false;
					$inlineParenthesisLevel = 0;
				}
				$formatted .= $token->getContent();

				$parenthesisLevel--;
				$blockIndentation--;
			} elseif ($token->getContent() === '.') {
				$formatted .= $token->getContent();
			} elseif ($token->getContent() === ',') {
				$addNewline = !$inlineParenthesis;

				$formatted .= $token->getContent();
			} elseif ($token->getContent() === ';') {
				if (strpos($formatted, "\n") !== false) {
					$formatted .= "\n";
				}
				$formatted .= $token->getContent();
			} elseif ($tokenIndex > 0 && !in_array(substr($formatted, -1), ["\n", "\t", '(', '.'])) {
				$formatted .= ' ';
				$formatted .= $token->getContent();
			} else {
				$formatted .= $token->getContent();
			}
		}

		return $formatted;
	}

	/**
	 * @inheritDoc
	 */
	public function highlight(array $arrTypes = []): AdapterInterface
	{
		if (in_array(static::ActionType_Highlighted, $this->actionsTaken)) {
			return $this;
		}

		$highlightFormat = $this->highlightFormatWeb;
		if ($this->isCli) {
			$highlightFormat = $this->highlightFormatCli;
		}

		$sql    = '';
		$tokens = $this->getTokens($this->parsed);
		foreach ($tokens as $token) {
			if (count($arrTypes) > 0 && !$token->isTypeNotIn($arrTypes)) {
				$sql .= $token->getContent();
				continue;
			}

			if (!array_key_exists($token->getType(), $highlightFormat)) {
				$sql .= $token->getContent();
				continue;
			}

			if ($token->isType(static::TokenType_Space)) {
				$sql .= str_repeat(sprintf($highlightFormat[$token->getType()], ($this->isCli ? '.' : '&middot;')), substr_count($token->getContent(), " "));
			} elseif ($token->isType(static::TokenType_Tab)) {
				$sql .= str_repeat(sprintf($highlightFormat[$token->getType()], ($this->isCli ? '--->' : '&mdash;&mdash;&mdash;&rarr;')), substr_count($token->getContent(), "\t"));
			} elseif ($token->isType(static::TokenType_Newline)) {
				$sql .= str_repeat(sprintf($highlightFormat[$token->getType()], ($this->isCli ? '/' : '&#745;')) . "\n", substr_count($token->getContent(), "\n"));
			} else {
				$sql .= sprintf($highlightFormat[$token->getType()], $token->getContent());
			}
		}

		$this->parsed = $sql;

		$this->actionsTaken[] = static::ActionType_Highlighted;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function stripComments(): AdapterInterface
	{
		if (in_array(static::ActionType_StrippedForComments, $this->actionsTaken)) {
			return $this;
		}

		$tokens       = $this->getTokens($this->parsed)->filter([static::TokenType_Comment], false);
		$this->parsed = implode('', $tokens->getTokenContents());

		$this->actionsTaken[] = static::ActionType_StrippedForComments;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function obfusticate(array $arrTypes = []): AdapterInterface
	{
		if (empty($arrTypes)) {
			$arrTypes = [
				static::TokenType_String,
				static::TokenType_Number,
				static::TokenType_Identifier,
				static::TokenType_Parameter,
				static::TokenType_Variable,
			];
		}

		$sql    = '';
		$tokens = $this->getTokens($this->parsed);
		foreach ($tokens as $token) {
			if ($token->isTypeIn($arrTypes)) {
				$sql .= $this->jibberish($token->getContent());
			} else {
				$sql .= $token->getContent();
			}
		}

		$this->parsed = $sql;

		$this->actionsTaken[] = static::ActionType_Obfusticated;

		return $this;
	}

	/**
	 * Convert string into "matching" jibberish
	 *
	 * @param string $string
	 * @return string
	 */
	protected function jibberish(string $string): string
	{
		$jibberish = '';

		if (preg_match('/^["\'`]?[a-z.]+["\'`]?$/i', $string)) {
			$characters = 'abcdefhijklmnopqrstuvwxyz';
		} elseif (preg_match('/^["\'`]?[0-9. ]+["\'`]?$/i', $string)) {
			$characters = '0123456789';
		} else {
			$characters = 'abcdefhijklmnopqrstuvwxyz0123456789';
		}

		$charactersLength = strlen($characters) - 1;

		$iMax = strlen($string) - 1;
		for ($i = 0; $i <= $iMax; $i++) {
			if (in_array($string[$i], ['"', "'", '.', '`', ' ', ':', '$'])) {
				$jibberish .= $string[$i];
			} else {
				$jibberish .= $characters[rand(0, $charactersLength)];
			}
		}

		return $jibberish;
	}

	/**
	 * @inheritDoc
	 */
	public function render(): string
	{
		$elapsedTime = (microtime(true) - $this->timeStart) * 1000;

		$trace = current(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

		if ($this->isCli) {
			return implode('', [
				"\n",
				str_repeat('-', 100),
				"\n",
				'- ',
				'MySQL',
				(count($this->actionsTaken) > 0 ? ' - ' . implode(', ', $this->actionsTaken) : ''),
				' - ' . sprintf('%0.4f', $elapsedTime) . ' ms.',
				' - ' . basename($trace['file']) . '::' . $trace['line'],
				"\n",
				str_repeat('-', 100),
				"\n",
				$this->parsed,
				"\n",
				str_repeat('-', 100),
				"\n",
			]);
		}

		return implode('', [
			'<pre style="margin-bottom: 0; padding: 5px; font-family: Menlo, Monaco, Consolas, monospace; font-weight: normal; font-size: 12px; background-color: #2C2C2C; border: none; border-radius: 0; color: #AAAAAA; display: block; z-index: 1000;  overflow: auto;">',
			'MySQL',
			(count($this->actionsTaken) > 0 ? ' - ' . implode(', ', $this->actionsTaken) : ''),
			' - ' . sprintf('%0.4f', $elapsedTime) . ' ms.',
			'<span style="float: right;">',
			basename($trace['file']) . '::' . $trace['line'],
			'</span>',
			'</pre>',
			'<pre style="margin-top: 0; padding: 10px; font-family: Menlo, Monaco, Consolas, monospace; font-weight: bold; font-size: 12px; background-color: #18171B; border: none; border-radius: 0; color: #EEEEEE; display: block; z-index: 1000; overflow: auto; tab-size: 4; -moz-tab-size: 4;">',
			$this->parsed,
			'</pre>',
		]);
	}

	/**
	 * Get tokens for given SQL
	 *
	 * @param string $sql
	 * @return TokenCollection
	 */
	protected function getTokens(string $sql): TokenCollection
	{
		$functions = $this->functions;
		$functions = array_map(function ($value) {
			return preg_quote($value, '/');
		}, $functions);
		usort($functions, function ($a, $b) {
			return strlen($b) <=> strlen($a);
		});

		$topLevelKeywords = $this->topLevelKeywords;
		$topLevelKeywords = array_map(function ($value) {
			$value = preg_quote($value, '/');
			$value = str_replace(' ', '\s+', $value);

			return $value;
		}, $topLevelKeywords);
		usort($topLevelKeywords, function ($a, $b) {
			return strlen($b) <=> strlen($a);
		});

		$keywords = $this->keywords;
		$keywords = array_map(function ($value) {
			$value = preg_quote($value, '/');
			$value = str_replace(' ', '\s+', $value);

			return $value;
		}, $keywords);
		usort($keywords, function ($a, $b) {
			return strlen($b) <=> strlen($a);
		});

		$operators = $this->operators;
		$operators = array_map(function ($value) {
			return preg_quote($value, '/');
		}, $operators);
		usort($operators, function ($a, $b) {
			return strlen($b) <=> strlen($a);
		});

		$tokenizer = new Tokenizer();
		$tokenizer->addRule(new TokenRule(static::TokenType_Error, '/^ERROR: [^\n]+/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Space, '/^ +/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Tab, '/^\t+/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Newline, '/^[\n\r]+/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Comment, '/^(?:#|--)[^\n]+/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Comment, '/^\/\*[^*\/]+\*\//'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Number, '/^\d+(?=(\s|\)|,))/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_String, '/^"([^"]+)?"/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_String, '/^\'([^\']+)?\'/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_TopLevelKeyword, '/^(' . implode('|', $topLevelKeywords) . ')(?=(\s|\())/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Function, '/^(' . implode('|', $functions) . ')(?=\s*\()/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Keyword, '/^(' . implode('|', $keywords) . ')(?=[\s,\(,\)])/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Operator, '/^(' . implode('|', $operators) . ')(?=[\s\(\)\'\"\`\$0-9])/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Operator, '/^[,]/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_ParenthesisStart, '/^\(/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_ParenthesisEnd, '/^\)/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_BracketStart, '/^\[/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_BracketEnd, '/^\]/'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Variable, '/^\$[a-z0-9_]+(\[[^\]]+\])?/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Identifier, '/^`?[a-z0-9_*.]+`?/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Parameter, '/^:[a-z0-9_*]+/i'));
		$tokenizer->addRule(new TokenRule(static::TokenType_Unknown, '/.*/'));

		$tokens = $tokenizer->tokenize($sql);

		// Replace newlines with spaces in multi-word keywords
		foreach ($tokens as $tokenIndex => $token) {
			if ($token->isTypeIn([static::TokenType_TopLevelKeyword, static::TokenType_Keyword])) {
				$tokens[$tokenIndex]->setContent(str_replace("\n", ' ', $token->getContent()));
			}
		}

		return $tokens;
	}
}
