<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminate\Support\ServiceProvider;

/**
 * DataProviderServiceProvider allows publishing of the resource files related to `DataProvider` usage.
 *
 * ```
 * php artisan vendor:publish --provider="Illuminatech\DataProvider\DataProviderServiceProvider" --tag=config
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DataProviderServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerPublications();
    }

    /**
     * Register resources to be published by the publish command.
     */
    protected function registerPublications(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/data_provider.php' => $this->app->make('path.config') . DIRECTORY_SEPARATOR . 'data_provider.php',
        ], 'config');
    }
}
