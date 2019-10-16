# ReactPHP HTTP Server performance
ReactPHP is really an interesting project inspired by NodeJS to bring the asynchronous I/O to the world of PHP programming. And for a performance freak like me, I always wonder how PHP Reactive Server work out my performance test.
For more details about the project please go here [https://reactphp.org/](https://reactphp.org/)

# Test design

## Test application design
I just used a simple PHP test script here which will create a http server base on the PHP EventLoop and listen on port 8080.

```php
use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
require __DIR__ . '/../vendor/autoload.php';
$loop = Factory::create();
$server = new Server(isset($argv[1]) ? $argv[1] : 0, $loop, array(
    'backlog' => 128,
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));
$server->on('connection', function (ConnectionInterface $connection) {
    $connection->once('data', function () use ($connection) {        
        $body = "<html><h1>Everyday is a beautiful day!</h1></html>\r\n";
        $connection->end("HTTP/1.1 200 OK\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
    });
});
$server->on('error', 'printf');
echo 'Listening on ' . strtr($server->getAddress(), array('tcp:' => 'http:', 'tls:' => 'https:')) . PHP_EOL;
$loop->run();

```
## Test specs
* Computer: Dell `Latitude 5490`, `Intel Core i7-8650U CPU @ 1.90GHz Turbo 3.8Ghz` 4 cores 8 threads, 16GB RAM
* PHP http web application, readonly, no database
* Concurrent users: 3000
* Test script details:
Ramp up 3000 threads in 20 seconds and loop for 20 times (enough loop for all 3000 threads to started and stayed alive) -> observer the result.

<pre>
&lt;ThreadGroup guiclass=&quot;ThreadGroupGui&quot; testclass=&quot;ThreadGroup&quot; testname=&quot;Thread Group&quot; enabled=&quot;true&quot;&gt;
  &lt;stringProp name=&quot;ThreadGroup.on_sample_error&quot;&gt;continue&lt;/stringProp&gt;
  &lt;elementProp name=&quot;ThreadGroup.main_controller&quot; elementType=&quot;LoopController&quot; guiclass=&quot;LoopControlPanel&quot; testclass=&quot;LoopController&quot; testname=&quot;Loop Controller&quot; enabled=&quot;true&quot;&gt;
    &lt;boolProp name=&quot;LoopController.continue_forever&quot;&gt;false&lt;/boolProp&gt;
    &lt;intProp name=&quot;LoopController.loops&quot;&gt;20&lt;/intProp&gt;
  &lt;/elementProp&gt;
  &lt;stringProp name=&quot;ThreadGroup.num_threads&quot;&gt;3000&lt;/stringProp&gt;
  &lt;stringProp name=&quot;ThreadGroup.ramp_time&quot;&gt;20&lt;/stringProp&gt;
  &lt;boolProp name=&quot;ThreadGroup.scheduler&quot;&gt;false&lt;/boolProp&gt;
  &lt;stringProp name=&quot;ThreadGroup.duration&quot;&gt;&lt;/stringProp&gt;
  &lt;stringProp name=&quot;ThreadGroup.delay&quot;&gt;&lt;/stringProp&gt;
&lt;/ThreadGroup&gt;
</pre>

# How to run the test
After cloning the repository, please run the following command in your terminal in order to run this test:
```bash
composer install
php src/server.php 8080
```
Assume you already have [jMeter](https://jmeter.apache.org/) download and setup properly, please run the follow script to run the load test
```bash
jmeter -n -t stress.jmx -l ./result/result.csv -e -o ./result
```

# Result Analyse and Conclusion
After running the test I could observe more than 50% of the requests are failed due to `Connect to 127.0.0.1:8080 [\/127.0.0.1] failed: Connection refused: connect`
This is still a big negative consider how the test is simple (just echo a static message, no other extension/connection).

There are couple of other optimization I should spend more time into before having a better conclusion,
issue such as [https://github.com/reactphp/http/issues/311](https://github.com/reactphp/http/issues/311)
could really impact the test result.
TODO: next step would be to update this test to a more complicated one in connection with MongoDB,
I think I'm gonna use this library: [https://github.com/jmikola/react-mongodb](https://github.com/jmikola/react-mongodb)

For more details please have a look at the [result folder](https://namnvhue.github.io/php-eventloop-performance/result/index.html)
* ReactPHP Performance summary
![ReactPHP Performance summary](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-performance.png)
* ReactPHP Performance overtime
![ReactPHP Performance overtime](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-overtime.png)
* ReactPHP Performance throughput
![ReactPHP Performance throughput](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-throughput.png)
* ReactPHP Performance response time
![ReactPHP Performance response time](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-response-time.png)
