<?php
require_once '../symfony1/vendor/autoload.php';
use function OpenTelemetry\Instrumentation\hook;
hook(SomeInterface::class,'someMethod',
    pre: function ($objectInstance,$params,$className,$methodName,$file,$line) {
        $params[0] = 3;
        printf("before: %s\n", get_class($objectInstance));
        return $params;
    },
    post: function ($objectInstance,$params,$return,$throwable,$className,$methodName,$file,$line) {
        print "after\n";
        print $throwable->getMessage();
    }
);

interface SomeInterface
{
    public function someMethod($param1, $param2): void;
}

















class SomeClassForInstrumentationDemo implements SomeInterface
{
    public function someMethod($param1, $param2): void{
        printf("some method: %s\n", $param1);
        throw new Exception("some exception");
    }
}
try {
    (new SomeClassForInstrumentationDemo())->someMethod(1, 2);
} catch (Exception $e) {
}