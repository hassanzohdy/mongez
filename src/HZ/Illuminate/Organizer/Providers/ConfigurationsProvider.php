<?php
namespace HZ\Illuminate\Organizer\Providers;

use Illuminate\Filesystem\Filesystem;

class ConfigurationsProvider extends ServiceProvider
{
    /**
     * Startup config
     * 
     * @var array
     */
    protected $config = [];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        die('@');
        $this->publishes([$this->configPath() => config_path('organizer.php')]);
    }

    /**
     * Get config path
     * 
     * @return string
     */
    protected function configPath(): string 
    {
        return '../../../../../files/config/organizer.php';
    } 


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        die('@');
        echo '12';
    }
}