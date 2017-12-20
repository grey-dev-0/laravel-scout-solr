<?php namespace GreyDev\LaravelSolr;

use Illuminate\Support\ServiceProvider;

class SolrServiceProvider extends ServiceProvider{
    protected $defer = true;

    public function register(){

    }
    
    public function provides(){
        return [];
    }
}