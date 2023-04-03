<?php

namespace App\Abstracts;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractService
{
    protected $modelClass;

    protected $modelQueryBuilderClass;

    /**
     * @var Application|mixed
     */
    protected $model;

    /**
     * AbstractService constructor.
     */
    public function __construct()
    {
        $this->model = app($this->modelClass);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOrFailById($id)
    {
        try {
            $data = $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return $data;
    }

    /**
     * @param $resourceCollectionClass
     * @param $models
     * @return mixed
     */
    public function resourceCollectionToData($resourceCollectionClass, $models)
    {
        return app($resourceCollectionClass, ['resource' => $models])
            ->toResponse(app('Request'))
            ->getData(true);
    }

    /**
     * @param $resourceClass
     * @param $model
     * @return mixed
     */
    public function resourceToData($resourceClass, $model)
    {
        return app($resourceClass, ['resource' => $model])
            ->toResponse(app('Request'))
            ->getData(true);
    }

    /**
     * @param $perPage
     * @param $page
     * @param $columns
     * @param $pageName
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCollectionWithPagination($perPage = 15, $page = 1, $columns = '*', $pageName = 'page')
    {
        $perPage = request()->get('per_page', $perPage);
        $page = request()->get('page', $page);
        $columns = request()->get('columns', $columns);
        $pageName = request()->get('page_name', $pageName);

        return $this->modelQueryBuilderClass::initialQuery()
            ->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $model = $this->findOrFailById($id);
        if ($model) {
            $model->delete();
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create($data = [])
    {
        return $this->model->create($data);
    }

    /**
     * @param $model
     * @param array $data
     * @return mixed
     */
    public function update($model, $data = [])
    {
        return $model->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOneById($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param $where
     * @return Builder|Model|object|null
     */
    public function findOneWhere($where)
    {
        return $this->model->where($where)->first();
    }

    /**
     * @param $where
     * @return mixed
     */
    public function findOneWhereOrFail($where)
    {
        return $this->model->where($where)->firstOrFail();
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->model->all();
    }

    public function findAllWhere($where)
    {
        return $this->model->where($where)->get();
    }
}
