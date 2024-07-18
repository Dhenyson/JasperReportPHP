<?php

namespace Dhenyson\JasperReportPHP;

class JasperReport
{
    protected $jrxmlFilePath;
    protected $fileName;
    protected $config;
    protected $dbConnection;
    protected $parameters = [];
    protected $jasperFile;

    public function __construct(
        string $jrxmlFilePath,
        string $jrxmlFileName,
        array $config = []
    ) {
        if (strpos($jrxmlFileName, '.') !== false) {
            $jrxmlFileName = pathinfo($jrxmlFileName, PATHINFO_FILENAME);
        }

        $defaultConfig = [
            'jasperStarterPath' => __DIR__ . '/../bin/jasperstarter/bin/jasperstarter',
            'enableLog' => true,
            'outputDir' => $jrxmlFilePath,
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

        $this->jrxmlFilePath = $jrxmlFilePath;
        $this->fileName = $jrxmlFileName;
        $this->config = array_merge($defaultConfig, $config);

        $this->compile();
    }


    public function setDbDriver(string $value)
    {
        $this->config['dbConnection']['driver'] = $value;
    }

    public function setDbHost(string $value)
    {
        $this->config['dbConnection']['host'] = $value;
    }

    public function setDbPort(string $value)
    {
        $this->config['dbConnection']['port'] = $value;
    }

    public function setDbDatabase(string $value)
    {
        $this->config['dbConnection']['database'] = $value;
    }

    public function setDbUsername(string $value)
    {
        $this->config['dbConnection']['username'] = $value;
    }

    public function setDbPassword(string $value)
    {
        $this->config['dbConnection']['password'] = $value;
    }

    protected function getDbConnectionParams(): string
    {
        if (!$this->config['dbConnection']['host']) {
            return '';
        }

        $params = [];

        switch ($this->config['dbConnection']['driver']) {
            case 'mysql':
                $params[] = "-t mysql -u {$this->config['dbConnection']['username']} -p {$this->config['dbConnection']['password']} -H {$this->config['dbConnection']['host']} -n {$this->config['dbConnection']['database']}";
                if ($this->config['dbConnection']['port']) {
                    $params[] = "--db-port {$this->config['dbConnection']['port']}";
                }
                if ($this->config['jdbcDir']) {
                    $params[] = "--jdbc-dir {$this->config['jdbcDir']}";
                }
                break;
            case 'postgres':
                $params[] = "-t postgresql -u {$this->config['dbConnection']['username']} -p {$this->config['dbConnection']['password']} -H {$this->config['dbConnection']['host']} -n {$this->config['dbConnection']['database']}";
                if ($this->config['dbConnection']['port']) {
                    $params[] = "--db-port {$this->config['dbConnection']['port']}";
                }
                if ($this->config['jdbcDir']) {
                    $params[] = "--jdbc-dir {$this->config['jdbcDir']}";
                }
                break;
            default:
                throw new \Exception("Invalid database driver: {$this->config['dbConnection']['driver']}");
        }

        return implode(' ', $params);
    }

    protected function compile()
    {
        $jrxmlFile = $this->getOutputDir() . '/' . $this->getFileName() . '.jrxml';

        $output = [];
        $returnVar = 0;
        $flagCommandLog = $this->config['enableLog'] ? '' : '>/dev/null 2>&1';

        $command = "{$this->config['jasperStarterPath']} compile {$jrxmlFile} {$flagCommandLog}";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            error_log('command: ' . $command);
            error_log('output: ' . json_encode($output));
            throw new \Exception("Failed to compile report: " . implode("\n", $output));
        }

        $this->jasperFile = $this->getOutputDir() . '/' . $this->getFileName() . '.jasper';
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getOutputDir()
    {
        return $this->config['outputDir'];
    }

    /**
     * Output example: "/app/storage/app/public/myfile.jasper"
     * @return string
     */
    public function getJasperFilePath()
    {
        return $this->getOutputDir() . '/' . $this->getFileName() . '.jasper';
    }

    public function setParameter($name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function enableLog(): void
    {
        $this->config['enableLog'] = true;
    }

    protected function formatParameters(): string
    {
        $formatted = [];
        foreach ($this->parameters as $key => $value) {
            $formatted[] = "\"{$key}={$value}\"";
        }

        if (count($formatted) > 0) {
            return "-P " . implode(' ', $formatted);
        }

        return '';
    }


    public function process($outputFormat = 'pdf', $outputDir = null)
    {
        $output = [];
        $returnVar = 0;

        // Verifique se o formato de saída é válido
        $validFormats = ['pdf', 'xls'];
        if (!in_array($outputFormat, $validFormats)) {
            throw new \Exception("Invalid output format: {$outputFormat}. Supported formats: " . implode(', ', $validFormats));
        }

        $params = $this->formatParameters();

        $outputDir = $outputDir ? $outputDir : $this->getOutputDir();
        $dbConnectionParams = $this->getDbConnectionParams();
        $flagCommandLog = $this->config['enableLog'] ? '' : '>/dev/null 2>&1';

        $command = "{$this->config['jasperStarterPath']} process {$this->jasperFile} -f {$outputFormat} -o {$outputDir} {$params} {$dbConnectionParams} {$flagCommandLog}";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Failed to process report: " . implode("\n", $output));
        }

        return $outputDir . '/' . $this->getFileName() . '.' . $outputFormat;
    }

    /**
     * Example return: ["P MyFirstParameter java.lang.String","P BookSubTitleParameter java.lang.String"]
     * @return array
     */
    public function getParameters(): array
    {
        $command = escapeshellcmd("{$this->config['jasperStarterPath']} list_parameters " . escapeshellarg($this->jasperFile));

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Failed to list parameters: " . implode("\n", $output));
        }

        return $output;
    }
}
