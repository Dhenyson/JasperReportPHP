# Jasper Report PHP
Generate reports easily from .jrxml files created with JasperSoft Studio.

> **.jrxml**  to **.pdf** or **.xls**.

> The repository is this size because it is focused on practicality, so for this the repository already has jasperstarter files and jdbc drivers in its structure.

# Requirements
* PHP 7.4+
* Java JDK 1.8 (set **ENV JAVA_HOME** and **ENV PATH**)

# Install
```bash
composer require dhenyson/jasper-report-php
```

# Basic example
```php
<?php

use Dhenyson\JasperReportPHP\JasperReport;
...
$fileOutputDir = __DIR__ . '/../../../storage/app/public';
$fileName = "example" // or "example.jrxml"

$jasperReport = new JasperReport($fileOutputDir, $fileName);
$jasperReport->setParameter('MyParameter', 'Hello world!');
$filePath = $jasperReport->process("xls"); // default = "pdf"

echo($filePath); // example output -> /app/storage/app/public/example.xls
...
```
> Note: The files will be output in the same location as the base file (.jrxml), so if it is in memory, save it somewhere.

# Basic example with dbConnection
```php
<?php
...
$config = [
    'enableLog' => false,
    'dbConnection' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'test',
        'username' => 'root',
        'password' => '123456',
    ]
];
$jasperReport = new JasperReport($fileOutputDir, $fileName, $config);
$filePath = $jasperReport->process();
...
```
or
```php
<?php
...
$jasperReport = new JasperReport($fileOutputDir, $fileName);
$jasperReport->setDbDriver('mysql');
$jasperReport->setDbHost('127.0.0.1');
$jasperReport->setDbPort('3306');
$jasperReport->setDbDatabase('test');
$jasperReport->setDbUsername('root');
$jasperReport->setDbPassword('123456');

$jasperReport->setParameter('UserId', '123');

$filePath = $jasperReport->process();
...
```

# Default config
```php
$defaultConfig = [
    'jasperStarterPath' => __DIR__ . '/../bin/jasperstarter/bin/jasperstarter',
    'enableLog' => true,
    'dbConnection' => [
        'driver' => 'mysql',
        'host' => null,
        'port' => null,
        'database' => null,
        'username' => null,
        'password' => null,
    ],
    'jdbcDir' => __DIR__ . '/../jdbc',
];
```

# Simple example in Laravel
```php
<?php

use Dhenyson\JasperReportPHP\JasperReport;
use Illuminate\Http\Request;

class JasperReportsController extends Controller
{
    public function generateReport(Request $request)
    {
        // get file from api request
        $jrxmlFile = $request->file('jrxmlFile');

        // set default path and names
        $fileOutputDir = __DIR__ . '/../../../../storage/app/public';
        $newFileName = 'my_report';
        $jrxmlFilePath = "$fileOutputDir/$newFileName.jrxml";
        $jasperFilePath = "$fileOutputDir/$newFileName.jasper";

        // save the file somewhere
        $jrxmlFile->storeAs('public', "$newFileName.jrxml");

        // Create jasper object and process
        $jasperReport = new JasperReport($fileOutputDir, $newFileName);
        $parameters = $jasperReport->getParameters(); // do something with the parameters
        $jasperReport->setParameter('MyParameter', 'Parameter value');
        $jasperReport->setParameter('MySecondParameter', 'Hello World');

        $outputFilePath = $jasperReport->process();

        // If necessary, remove the files created in the process to free up space
        if (file_exists($jrxmlFilePath)) {
            unlink($jrxmlFilePath);
        }
        if (file_exists($jasperReport->getJasperFilePath())) {
            unlink($jasperReport->getJasperFilePath());
        }

        // Return file to API and delete file
        return response()->download($outputFilePath)->deleteFileAfterSend(true);
    }
```

# Develop with Docker

Recommended requirements:
- docker 24.0.5
- docker-compose 1.29.2

Clone repository
```bash
git clone git@github.com:Dhenyson/JasperReportPHP.git
```

Create container
```bash
docker-composer up -d
```

> Access the container and make modifications, changes to the container will be reflected in the original files