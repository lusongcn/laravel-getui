<?php 
namespace Earnp\Getui;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
class GetuiServiceprovider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    public function boot()
    {
        // this for conig
        $this->publishes([
            __DIR__.'/config/getui.php' => config_path('getui.php'),
        ]);
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     * @return void
     */


    public function register()
    {
        $this->app->bind('getui',function($app){
            return new Getui();
        });
    }
}