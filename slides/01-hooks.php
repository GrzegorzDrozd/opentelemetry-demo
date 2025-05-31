<?php
require_once '../symfony1/vendor/autoload.php';
use function OpenTelemetry\Instrumentation\hook;
hook(null, 'testFunc',
    pre: static function ($objectInstance, $params, $className, $methodName, $file, $line) {
        print "before 1\n";
    },
    post: static function ($objectInstance, $params, $return, $throwable, $className, $methodName, $file, $line) {
        print "after 1\n";
    }
);
hook(null,'testFunc',
    pre: static function ($objectInstance, $params, $className, $methodName, $file, $line) {
        $params[0] = 3;
        print "before 2\n";
        return $params;
    },
    post: static function ($objectInstance, $params, $return, $throwable, $className, $methodName, $file, $line) {
        print "after 2\n";
    }
);








function testFunc($param1, $param2): void
{
    print sprintf("testFunc %s %s\n", $param1, $param2);
}

testFunc(1, 2);