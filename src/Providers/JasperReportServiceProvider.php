<?php

namespace Dhenyson\JasperReportPHP\Providers;

use Illuminate\Support\ServiceProvider;
use Dhenyson\JasperReportPHP\JasperReport;

class JasperReportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('jasperreport', function ($app) {
            return JasperReport::class;
        });
    }

    public function boot()
    {
        // Se precisar de alguma inicialização extra
    }
}
