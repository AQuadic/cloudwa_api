<?php

namespace AQuadic\Cloudwa;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AQuadic\Cloudwa\Commands\CloudwaCommand;

class CloudwaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('cloudwa-api')
            ->hasConfigFile();
//            ->hasViews()
    }
}
