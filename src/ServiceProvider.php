<?php

namespace Datashaman\OpenSearch\Model;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/opensearch.php';
        $this->mergeConfigFrom($configPath, 'opensearch');

        $this->app->singleton('opensearch', function ($app) {
            $clientFactory = array_get(
                $app['config'],
                'opensearch.clientFactory',
                [
                    ClientFactory::class,
                    'make'
                ]
            );

            return call_user_func($clientFactory, $app);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/opensearch.php';
        $this->publishes([$configPath => config_path('opensearch.php')], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['opensearch'];
    }
}
