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
* Concurrent users: 5000
* Test script details:
Ramp up 5000 threads in 60 seconds, run the test for 1.5 minutes or 2 minutes -> observer the result.
```xml
<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2" properties="5.0" jmeter="5.1.1 r1855137">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan" testname="Test Plan" enabled="true">
      <stringProp name="TestPlan.comments"></stringProp>
      <boolProp name="TestPlan.functional_mode">false</boolProp>
      <boolProp name="TestPlan.tearDown_on_shutdown">true</boolProp>
      <boolProp name="TestPlan.serialize_threadgroups">false</boolProp>
      <elementProp name="TestPlan.user_defined_variables" elementType="Arguments" guiclass="ArgumentsPanel" testclass="Arguments" testname="User Defined Variables" enabled="true">
        <collectionProp name="Arguments.arguments"/>
      </elementProp>
      <stringProp name="TestPlan.user_define_classpath"></stringProp>
    </TestPlan>
    <hashTree>
      <ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="Thread Group" enabled="true">
        <stringProp name="ThreadGroup.on_sample_error">continue</stringProp>
        <elementProp name="ThreadGroup.main_controller" elementType="LoopController" guiclass="LoopControlPanel" testclass="LoopController" testname="Loop Controller" enabled="true">
          <boolProp name="LoopController.continue_forever">false</boolProp>
          <intProp name="LoopController.loops">-1</intProp>
        </elementProp>
        <stringProp name="ThreadGroup.num_threads">5000</stringProp>
        <stringProp name="ThreadGroup.ramp_time">60</stringProp>
        <boolProp name="ThreadGroup.scheduler">false</boolProp>
        <stringProp name="ThreadGroup.duration"></stringProp>
        <stringProp name="ThreadGroup.delay"></stringProp>
      </ThreadGroup>
      <hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="HTTP Request" enabled="true">
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments" testname="User Defined Variables" enabled="true">
            <collectionProp name="Arguments.arguments"/>
          </elementProp>
          <stringProp name="HTTPSampler.domain">127.0.0.1</stringProp>
          <stringProp name="HTTPSampler.port">8080</stringProp>
          <stringProp name="HTTPSampler.protocol"></stringProp>
          <stringProp name="HTTPSampler.contentEncoding"></stringProp>
          <stringProp name="HTTPSampler.path"></stringProp>
          <stringProp name="HTTPSampler.method">GET</stringProp>
          <boolProp name="HTTPSampler.follow_redirects">true</boolProp>
          <boolProp name="HTTPSampler.auto_redirects">false</boolProp>
          <boolProp name="HTTPSampler.use_keepalive">true</boolProp>
          <boolProp name="HTTPSampler.DO_MULTIPART_POST">false</boolProp>
          <stringProp name="HTTPSampler.embedded_url_re"></stringProp>
          <stringProp name="HTTPSampler.connect_timeout"></stringProp>
          <stringProp name="HTTPSampler.response_timeout"></stringProp>
        </HTTPSamplerProxy>
        <hashTree/>
      </hashTree>
      <ResultCollector guiclass="GraphVisualizer" testclass="ResultCollector" testname="Graph Results" enabled="true">
        <boolProp name="ResultCollector.error_logging">false</boolProp>
        <objProp>
          <name>saveConfig</name>
          <value class="SampleSaveConfiguration">
            <time>true</time>
            <latency>true</latency>
            <timestamp>true</timestamp>
            <success>true</success>
            <label>true</label>
            <code>true</code>
            <message>true</message>
            <threadName>true</threadName>
            <dataType>true</dataType>
            <encoding>false</encoding>
            <assertions>true</assertions>
            <subresults>true</subresults>
            <responseData>false</responseData>
            <samplerData>false</samplerData>
            <xml>false</xml>
            <fieldNames>true</fieldNames>
            <responseHeaders>false</responseHeaders>
            <requestHeaders>false</requestHeaders>
            <responseDataOnError>false</responseDataOnError>
            <saveAssertionResultsFailureMessage>true</saveAssertionResultsFailureMessage>
            <assertionsResultsToSave>0</assertionsResultsToSave>
            <bytes>true</bytes>
            <sentBytes>true</sentBytes>
            <url>true</url>
            <threadCounts>true</threadCounts>
            <idleTime>true</idleTime>
            <connectTime>true</connectTime>
          </value>
        </objProp>
        <stringProp name="filename"></stringProp>
      </ResultCollector>
      <hashTree/>
    </hashTree>
  </hashTree>
</jmeterTestPlan>

```

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
After running the test I could observe more than 80% of the requests are failed due to `Connect to 127.0.0.1:8080 [\/127.0.0.1] failed: Connection refused: connect`
This is a big negative consider how the test is simple (just echo a static message, no other extension/connection).
Throughput only hit a certain number: 3475 hits/second and then starts to fall with the exact shape like accelerating, so this means the server reached an overwhelming threshold and stopped functioning properly.

For more details please have a look at the result folder.
* ReactPHP Performance summary
![ReactPHP Performance summary](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-performance.png)
* ReactPHP Performance overtime
![ReactPHP Performance overtime](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-overtime.png)
* ReactPHP Performance throughput
![ReactPHP Performance throughput](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-throughput.png)
* ReactPHP Performance response time
![ReactPHP Performance response time](https://namnvhue.github.io/php-eventloop-performance/assets/php-eventloop-response-time.png)
