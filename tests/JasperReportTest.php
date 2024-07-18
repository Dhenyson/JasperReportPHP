<?php

use PHPUnit\Framework\TestCase;
use Dhenyson\JasperReportPHP\JasperReport;

class JasperReportTest extends TestCase
{
    const SAMPLE_FILE_NAME = 'sample';
    private $config;

    protected function setUp(): void
    {
        $this->config = [
            'enableLog' => false
        ];
    }

    public function testCompile()
    {
        $jasper = new JasperReport(__DIR__, self::SAMPLE_FILE_NAME, $this->config);
        $this->assertFileExists($jasper->getJasperFilePath(), 'Jasper file was not generated');
    }

    public function testListParameters()
    {
        $jasper = new JasperReport(__DIR__, self::SAMPLE_FILE_NAME, $this->config);
        $parameters = $jasper->getParameters();

        $this->assertNotEmpty($parameters, 'Parameters array is empty');
    }

    public function testProcessToPdf()
    {
        $jasper = new JasperReport(__DIR__, self::SAMPLE_FILE_NAME, $this->config);

        $jasper->setParameter('BookTitle', 'Value 1');
        $jasper->setParameter('BookSubTitle', 'Value 2');
        $outputFile = $jasper->process();

        $this->assertFileExists($outputFile, 'PDF file was not generated');
    }

    public function testProcessToXls()
    {
        $jasper = new JasperReport(__DIR__, self::SAMPLE_FILE_NAME, $this->config);

        $jasper->setParameter('BookTitle', 'Value 1');
        $jasper->setParameter('BookSubTitle', 'Value 2');
        $outputFile = $jasper->process('xls');

        $this->assertFileExists($outputFile, 'XLS file was not generated');
    }
}
