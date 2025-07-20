<?php

namespace App\Repositories;


abstract class AbstractRepository {

    protected $model;

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findBy(array $where = [], array $or_where = [], array $with = [])
    {
        $query = $this->model->with($with);
        foreach ($where as $key => $value) {
            $query->where($key, $value);
        }

        foreach ($or_where as $key => $value) {
            $query->orWhere($key, $value);
        }

        return $query;
    }

    public function create(array $data)
    {

        foreach ($data as $key => $value) {
            $data[$key] = gettype($value) == "string" ? trim($value) : $value;
        }

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $this->model = $this->find($id);
        foreach($data as $key => $val) {
            $this->model->$key = $val;
        }
        $this->model->save();
        return $this->model;
    }

    public function all($order_by = null)
    {
        if(!is_null($order_by)) {
            return $this->model->orderBy($order_by)->get();
        }

        return $this->model->all();
    }
}