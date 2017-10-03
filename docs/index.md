# sebastianthiel DI Documentation
## 1 Introduction
sebastianthiel DI is a library, that resolves class dependencies. It is not a service locator 

This Dependency Injector is not intended to be injected into a class. 

## 2 Getting Started
### create a new instance of the DI
```php
<?php
$DI = new sebastianthiel\DI\DI();
```

### register some definitions
```php
<?php
// use an instance of FooClass, if FooClassInterface is required
$DI->set(FooClassInterface::class, FooClass::class);

// call BarClassFactory->createNew() to create a new instance of BarClass
$DI->set(BarClass::class, BarClassFactory::createNew);

// call the closure, if a new instance Baz class is requested
$DI->set(BazClass::class, function(ConfigurationInterface $configuration){
    return new BazClass($configuration->get('BazSettings'));
});
```

### retrieve an object
```php
<?php
class QuxClass{
    private $foo;
    
    public function __construct(FooInterface $foo) {
        $this->foo = $foo;    
    }
}
 
$qux = $DI->get(QuxClass::class);
```
in this example the Dependency Injector will first create an instance of FooInterface, as required by the QuxClass constructor,
and inject it into the constructor function

```php
<?php
class QuuxClass{
    private $foo;
    private $bar;
    
    public function __construct(FooInterface $foo, string $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
 
$qux = $DI->get(QuxClass::class, ['baz' => 'someString']);
```
BuildIn parameters (e.g. String, Int, Array) can not be resolved automatically. In this example the $bar parameter is set to "someString".

### resolving function parameters
the Dependency Injector also can resolve function parameter and invoke the function.
```php
<?php
$callable = function(FooInterface $foo, string $bar){
    return 'something';
};

$returnValue = $DI->invoke($callable, ['baz' => 'someString']);
```
this example invokes the function stored in $callable and stores the result in $returnValue

```php
<?php
class QuzClass{
    private $foo;

    public function __construct(FooInterface $foo) {
        $this->foo = $foo;
    }
    
    public function doSomething(string $bar){
        return $this->foo->something($bar); 
    }
}
//get a new quz object
$quz = $DI->get(QuzClass::class);

// call the doSomething method
$returnValue = $DI->invoke(
    [QuzClass::class, 'doSomething'], 
    ['bar' => 'someString'], 
    $quz
);

// alternatively
// if no quz object is present and/or needed
// the DI can retrieve it in
// the process
$returnValue = $DI->invoke(
    [QuzClass::class, 'doSomething'], 
    ['bar' => 'someString']
);
```
