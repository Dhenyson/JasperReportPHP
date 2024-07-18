<?php

namespace Dhenyson\JasperReportPHP\Facades;

use Illuminate\Support\Facades\Facade;

class JasperReport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'jasperreport';
    }
}