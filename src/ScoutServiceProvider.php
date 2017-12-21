<?php namespace GreyDev\LaravelSolr;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class ScoutServiceProvider extends ServiceProvider{
    public function boot(){
        resolve(EngineManager::class)->extend('solr', function () {
            return new SolrEngine();
        });
    }
}