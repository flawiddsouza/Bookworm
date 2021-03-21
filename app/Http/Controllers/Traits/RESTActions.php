<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;

trait RESTActions
{
    public function index()
    {
        $m = self::MODEL;
        $response = $m;

        if(defined('self::RELATIONS') && count(self::RELATIONS) > 0) {
            foreach(self::RELATIONS as $index => $relation) {
                if($index === 0) {
                    $response = $response::leftJoin($relation['table'], function($join) use($relation) {
                        $join->on($relation['table'] . '.id', '=', self::TABLE . '.' . $relation['foreignKey']);
                    });
                } else {
                    $response = $response->leftJoin($relation['table'], function($join) use($relation) {
                        $join->on($relation['table'] . '.id', '=', self::TABLE . '.' . $relation['foreignKey']);
                    });
                }
            }
            $select = [];
            $select[] = self::TABLE . '.*';
            foreach(self::RELATIONS as $index => $relation) {
                $select[] = $relation['table'] . '.' . $relation['foreignField'] . ' as ' . $relation['field'];
            }
            $response = $response->select($select);
        }

        if(defined('self::WHERE')) {
            foreach(self::WHERE as $field => $whereCondition) {
                if(gettype($response) === 'string') {
                    $response =  $response::where($field, $whereCondition[0], $whereCondition[1]);
                } else {
                    $response =  $response->where($field, $whereCondition[0], $whereCondition[1]);
                }
            }
        }

        if(defined('self::ORDER_BY')) {
            foreach(self::ORDER_BY as $orderBy) {
                if(gettype($response) === 'string') {
                    $response =  $response::orderBy($orderBy, 'ASC');
                } else {
                    $response =  $response->orderBy($orderBy, 'ASC');
                }
            }
        }

        if(gettype($response) === 'string') {
            $response = $response::get();
        } else {
            $response = $response->get();
        }

        return $this->respond(Response::HTTP_OK, $response);
    }

    public function get($id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        return $this->respond(Response::HTTP_OK, $model);
    }

    public function store(Request $request)
    {
        $m = self::MODEL;

        $toMerge = [];
        if(!defined('self::DISABLE_CREATED_BY')) {
            $request['created_by'] = Auth::id();
            $request['updated_by'] = Auth::id();
            $toMerge[] = 'created_by';
            $toMerge[] = 'updated_by';
        }

        if(method_exists(get_called_class(), 'modifyRestActionsStoreRequest')) {
            self::modifyRestActionsStoreRequest($request);
        }

        if(defined('self::UNIQUE_FIELDS') && count(self::UNIQUE_FIELDS) > 0) {
            $duplicatesCount = self::MODEL;
            foreach(self::UNIQUE_FIELDS as $index => $uniqueField) {
                if($index === 0) {
                    $duplicatesCount = $duplicatesCount::where($uniqueField, 'ilike', $request[$uniqueField]);
                } else {
                    $duplicatesCount = $duplicatesCount->where($uniqueField, 'ilike', $request[$uniqueField]);
                }
            }
            $duplicatesCount = $duplicatesCount->count();
            if($duplicatesCount > 0) {
                return $this->respond(Response::HTTP_BAD_REQUEST, 'Duplicate Entry');
            }
        }

        if(method_exists(get_called_class(), 'modifyRestActionsStore')) {
            $modify = self::modifyRestActionsStore($request);
            if($modify !== false) {
                return $modify;
            }
        }

        $createdRecord = $m::create($request->only(array_merge(self::FIELDS, $toMerge)));

        // logActivity([
        //     'table' => self::TABLE,
        //     'description' => self::TABLE_DESCRIPTION . ' added',
        //     'data' => $createdRecord
        // ]);

        if(method_exists(get_called_class(), 'restActionsAfterCreateHook')) {
            self::restActionsAfterCreateHook();
        }

        return $this->respond(Response::HTTP_CREATED, $createdRecord);
    }

    public function update(Request $request, $id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        if(!defined('self::DISABLE_CREATED_BY')) {
            $request['updated_by'] = Auth::id();
        }

        if(defined('self::UNIQUE_FIELDS') && count(self::UNIQUE_FIELDS) > 0) {
            $duplicatesCount = self::MODEL;
            $duplicatesCount = $duplicatesCount::where('id', '!=', $id);

            foreach(self::UNIQUE_FIELDS as $uniqueField) {
                $duplicatesCount = $duplicatesCount->where($uniqueField, 'ilike', $request[$uniqueField]);
            }
            $duplicatesCount = $duplicatesCount->count();
            if($duplicatesCount>0) {
                return $this->respond(Response::HTTP_BAD_REQUEST, 'Duplicate Entry');
            }
        }

        if(method_exists(get_called_class(), 'modifyRestActionsUpdate')) {
            $modify = self::modifyRestActionsUpdate($request, $model);
            if($modify !== false) {
                return $modify;
            }
        }

        if(!defined('self::DISABLE_CREATED_BY')) {
            $model->update($request->only(array_merge(self::FIELDS, ['updated_by'])));
        } else {
            $model->update($request->only(self::FIELDS));
        }

        // logActivity([
        //     'table' => self::TABLE,
        //     'description' => self::TABLE_DESCRIPTION . ' updated',
        //     'data' => $model
        // ]);

        if(method_exists(get_called_class(), 'restActionsAfterUpdateHook')) {
            self::restActionsAfterUpdateHook();
        }

        return $this->respond(Response::HTTP_OK, $model);
    }

    public function destroy($id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        try {
            $m::destroy($id);

            // logActivity([
            //     'table' => self::TABLE,
            //     'description' => self::TABLE_DESCRIPTION . ' deleted',
            //     'data' => $model
            // ]);

            if(method_exists(get_called_class(), 'restActionsAfterDestroyHook')) {
                self::restActionsAfterDestroyHook();
            }
        } catch(\Throwable $e) {
            return $this->respond(Response::HTTP_BAD_REQUEST, 'Entry in use');
        }

        return $this->respond(Response::HTTP_NO_CONTENT);
    }

    protected function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }
}
