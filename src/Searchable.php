<?php namespace GreyDev\LaravelSolr;

use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable{
    use ScoutSearchable;

    /**
     * Generates Eloquent attributes to Solr fields mapping.
     *
     * @return array
     */
    public function getScoutMap(){
        return array_combine($this->attributes, $this->attributes);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(){
        $attributes = $this->toArray();
        $map = $this->getScoutMap();
        foreach ($attributes as $attribute => &$value) {
            if($attribute == 'id')
                $value = $this->getTable()."-$value";
            if($attribute == $map[$attribute])
                continue;
            $attributes[$map[$attribute]] = $value;
            unset($attributes[$attribute]);
        }
        return $attributes;
    }
}