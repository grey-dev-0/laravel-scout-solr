<?php namespace GreyDev\LaravelSolr;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class SolrEngine extends Engine{

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @throws \Exception In case of update command failure.
     * @return void
     */
    public function update($models){
        $update = \Solr::createUpdate();
        $models->each(function($model) use(&$update){
            $document = $update->createDocument($model->toSearchableArray());
            $update->addDocument($document);
        });
        $this->executeStatement($update);
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @throws \Exception In case of delete command failure.
     * @return void
     */
    public function delete($models){
        $update = \Solr::createUpdate();
        $models->each(function($model) use(&$update){
            $update->addDeleteById($model->getKey());
        });
        $this->executeStatement($update);
    }

    /**
     * Execute Update or Delete statement on the index.
     *
     * @throws \Exception In case of command failure.
     * @param $statement \Solarium\QueryType\Update\Query\Query
     */
    private function executeStatement(&$statement){
        $statement->addCommit();
        $response = \Solr::update($statement);
        if($response->getStatus() != 0)
            throw new \Exception("Update command failed \n\n".json_encode($response->getData()));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     * @return mixed
     */
    public function search(Builder $builder){
        $query = \Solr::createSelect();
        return $this->executeQuery($query, $builder);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder $builder
     * @param  int $perPage
     * @param  int $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page){
        $query = \Solr::createSelect();
        $offset = ($page - 1) * $perPage;
        return $this->executeQuery($query, $builder, $offset, $perPage);
    }

    /**
     * Execute Select command on the index.
     *
     * @param \Solarium\QueryType\Select\Query\Query $query
     * @param \Laravel\Scout\Builder $builder
     * @param int $offset
     * @param int $limit
     * @return \Solarium\QueryType\Select\Result\Result
     */
    private function executeQuery(&$query, &$builder, $offset = 0, $limit = null){
        $conditions = (!empty($builder->query))? [$builder->query] : [];
        foreach($builder->wheres as $key => &$value)
            $conditions[] = "$key:\"$value\"";
        $query->setQuery(implode(' ', $conditions));
        if(!is_null($limit))
            $query->setStart($offset)->setRows($limit);
        return \Solr::select($query);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  \Solarium\QueryType\Select\Result\Result $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results){
        $ids = [];
        foreach($results as $document)
            $ids[] = $document->id;
        return collect($ids);
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Solarium\QueryType\Select\Result\Result $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map($results, $model){
        $map = (method_exists($model, 'getScoutMap'))? $model->getScoutMap() : array_combine($keys = array_keys($model->toSearchableArray()), $keys);
        $models = [];
        $queryBuilder = $model->newQuery();
        foreach($results as $document){
            $attributes = [];
            foreach($document as $field => $value)
                $attributes[$map[$field]] = $value;
            $models[] = $queryBuilder->create($attributes);
        }
        return Collection::make($models);
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  \Solarium\QueryType\Select\Result\Result $results
     * @return int
     */
    public function getTotalCount($results){
        return $results->getNumFound();
    }
}