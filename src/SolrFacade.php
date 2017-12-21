<?php namespace GreyDev\LaravelSolr;

use Illuminate\Support\Facades\Facade;
use Solarium\Client;

class SolrFacade extends Facade{
    protected static function getFacadeAccessor(){
        return Client::class;
    }
}