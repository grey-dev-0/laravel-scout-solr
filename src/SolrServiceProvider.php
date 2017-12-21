<?php namespace GreyDev\LaravelSolr;

use Illuminate\Support\ServiceProvider;
use Solarium\Client;

class SolrServiceProvider extends ServiceProvider{
    protected $defer = true;

    public function boot(){
        $this->publishes([
            __DIR__.'/solr-config.php' => config_path('solr.php')
        ]);
    }

    public function register(){
        $this->app->singleton(Client::class, function(){
            return new Client(config('solr'));
        });
    }
    
    public function provides(){
        return [Client::class];
    }
}