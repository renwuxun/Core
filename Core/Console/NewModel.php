<?php
/**
 * Created by PhpStorm.
 * User: mofan
 * Date: 2016/10/17 0017
 * Time: 16:03
 */




if (!isset($argv[2]) || !file_exists($argv[2])) {
    echo "usage: {$argv[0]} {modelname} {ddl-file-name}\n";
    die();
}

$modelName = $argv[1];

$ddl=file_get_contents($argv[2]);


$lines = explode("\n", mb_strtolower($ddl));
foreach ($lines as $k=>$line) {
    if (!preg_match('/^`\w+`\s+/is', trim($line))) {
        unset($lines[$k]);
    }
}

$fields=[];
foreach ($lines as $k=>$line) {
    if (preg_match('/^`(\w+)`\s+/is', trim($line), $m)) {
        //echo $m[1]."\n";
        $fields[] = $m[1];
    } else {
        echo "无法识别的字段{$line}\n";
        die();
    }
}

$types=[];
$typesInModel=[
    'char'=>'self::DATA_TYPE_STR',
    'varchar'=>'self::DATA_TYPE_STR',
    'text'=>'self::DATA_TYPE_STR',
    'tinyblob'=>'self::DATA_TYPE_STR',
    'tinytext'=>'self::DATA_TYPE_STR',
    'blob'=>'self::DATA_TYPE_STR',
    'mediumblob'=>'self::DATA_TYPE_STR',
    'mediumtext'=>'self::DATA_TYPE_STR',
    'longblob'=>'self::DATA_TYPE_STR',
    'longtext'=>'self::DATA_TYPE_STR',

    'date'=>'self::DATA_TYPE_STR',
    'datetime'=>'self::DATA_TYPE_STR',
    'time'=>'self::DATA_TYPE_STR',
    'timestamp'=>'self::DATA_TYPE_STR',
    'year'=>'self::DATA_TYPE_STR',

    'enum'=>'self::DATA_TYPE_STR',

    'int'=>'self::DATA_TYPE_INT',
    'integer'=>'self::DATA_TYPE_INT',
    'tinyint'=>'self::DATA_TYPE_INT',
    'bigint'=>'self::DATA_TYPE_INT',
    'smallint'=>'self::DATA_TYPE_INT',
    'mediumint'=>'self::DATA_TYPE_INT',

    'float'=>'self::DATA_TYPE_FLOAT',
    'double'=>'self::DATA_TYPE_FLOAT',
    'decimal'=>'self::DATA_TYPE_FLOAT',
];
foreach ($lines as $k=>$line) {
    if (preg_match('/^`\w+`\s+([^\s\(]+)/is', trim($line), $m)) {
        //echo $m[1]."\n";
        if (isset($typesInModel[$m[1]])) {
            $types[] = $typesInModel[$m[1]];
        } else {
            echo "不支持的类型{$line}\n";
            die();
        }
    } else {
        echo "无法识别的类型{$line}\n";
        die();
    }
}

$comments=[];
foreach ($lines as $k=>$line) {
    if (preg_match("/comment\s+'([^']+)'/is", trim($line), $m)) {
        //echo $m[1]."\n";
        $comments[] = $m[1];
    } else {
        $comments[] = '';
    }
}

printf("<?php

/**
 * User:
 * Date: %s
 * Time: %s
 */\n\n", date('Y-m-d'), date('H:i'));
echo "/**
 * Class $modelName\n";
foreach ($fields as $k=>$field) {
    echo " * @property \${$field}\n";
}
echo " */\n";
echo "class $modelName extends Core_Lib_VerModel {\n";
foreach ($fields as $k=>$field) {
    echo "    public \${$field};\n";
}
echo "\n";
foreach ($fields as $k=>$field) {
    echo "    const FN_".mb_strtoupper($field)." = '{$field}';\n";
}
echo "\n";
echo "    public static function fieldType() {
        return [\n";
foreach ($fields as $k=>$field) {
    echo "            self::FN_".mb_strtoupper($field)." => {$types[$k]},\n";
}
echo "        ];
    }\n";
echo "\n\n";

echo "    public static function primaryField() {
        return self::FN_; // @FIXME
    }\n\n";
echo "    public static function shardingField() {
        return self::FN_; // @FIXME
    }\n\n";

echo "    //'modelServers' => [
    //    '$modelName' => [
    //        'sid'=>0,
    //        'host'=>'',
    //        'port'=>3306,
    //        'user'=>'',
    //        'psw'=>'',
    //        'dbname'=>'',
    //        'tbname'=>'',
    //        'charset'=>'utf8'
    //    ]
    //]\n";

echo '    /**
     * @return Core_Lib_Conn
     * @throws Exception
     */
    protected static function newConn() {
        $conf = Core_Lib_App::app()->getConfig()->get(\'modelServers.\'.__CLASS__);
        if ($conf[\'sid\'] > 0) {
            $ipport = Core_Helper_L5::getInstance()->route($conf[\'sid\']);
            if (!empty($ipport) && $ipport[1] != \'0\') {
                $conf[\'host\'] = $ipport[0];
                $conf[\'port\'] = $ipport[1];
            }
        }
        $conn = new Core_Lib_MysqliConn($conf[\'host\'], $conf[\'user\'], $conf[\'psw\'], $conf[\'dbname\'], $conf[\'port\'], null, $conf[\'tbname\']);
        if ($conn->connect_errno != 0) {
            throw new Exception(\'model server connect error: \'.$conn->connect_error);
        }
        if (isset($conf[\'charset\'])) {
            $conn->set_charset($conf[\'charset\']);
        }
        return $conn;
    }'.PHP_EOL;
echo '
    public static function dataAccessor() {
        static $da;
        if (null === $da) {
            $da = new Core_Lib_MysqliAccessor;
            $da->setModel(__CLASS__)
                ->setConn(self::newConn());
        }
        return $da;
    }'.PHP_EOL.PHP_EOL;
echo '}';