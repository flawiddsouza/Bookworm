<?php

namespace App\Classes;

/**
 * Paginator class with support for filtering and multi-column sorting
 *
 * Multi-column sorting usage:
 * 1. Request params - Pass sort_columns as JSON string:
 *    sort_columns: '[{"column": "name", "direction": "asc"}, {"column": "created_at", "direction": "desc"}]'
 *
 * 2. Default params - Pass sortBy as array:
 *    'sortBy' => [
 *        ['column' => 'name', 'direction' => 'asc'],
 *        ['column' => 'created_at', 'direction' => 'desc']
 *    ]
 *    OR simple array: 'sortBy' => ['name', 'created_at'] (defaults to asc)
 *
 * Single-column sorting (legacy, still supported):
 * - Request: sort_by: "name", sort_order: "asc"
 * - Default: 'sortBy' => 'name', 'sortOrder' => 'asc'
 */
class Paginator
{
    public static function generate($query, $params, $request)
    {
        $unfilteredTotal = $query->count();
        $paginator = $query;

        if(isset($params['filterColumns']) && isset($request->filter) && $request->filter !== '') {
            $paginator->where(function($where) use($params, $request) {
                foreach($params['filterColumns'] as $filterColumn) {
                    $where->orWhere($filterColumn, 'ilike', '%' . $request->filter . '%');
                }
            });
        }

        // Apply sorting - supports both single and multi-column sorting
        $paginator = self::applySorting($paginator, $params, $request);

        $paginator = $paginator->paginate(50);

        return [
            'paginator' => $paginator,
            'unfiltered_total' => $unfilteredTotal
        ];
    }

    private static function applySorting($query, $params, $request)
    {
        // Multi-column sorting from request
        if (isset($request->sort_columns) && is_string($request->sort_columns)) {
            $sortColumns = json_decode($request->sort_columns, true);
            if (is_array($sortColumns) && !empty($sortColumns)) {
                return self::applyMultiColumnSort($query, $sortColumns, $params['requestSortBySubtitutions'] ?? []);
            }
        }

        // Single-column sorting from request
        if ($request->sort_by) {
            $column = $params['requestSortBySubtitutions'][$request->sort_by] ?? $request->sort_by;
            $direction = $request->sort_order ?: 'asc';
            return $query->orderBy($column, $direction);
        }

        // Default sorting from params
        if (isset($params['sortBy'])) {
            if (is_array($params['sortBy'])) {
                return self::applyMultiColumnSort($query, $params['sortBy']);
            }
            $direction = $params['sortOrder'] ?: 'asc';
            return $query->orderBy($params['sortBy'], $direction);
        }

        return $query;
    }

    private static function applyMultiColumnSort($query, $sortColumns, $substitutions = [])
    {
        foreach ($sortColumns as $sort) {
            if (is_array($sort) && isset($sort['column'])) {
                $column = $substitutions[$sort['column']] ?? $sort['column'];
                $direction = isset($sort['direction']) && in_array(strtolower($sort['direction']), ['asc', 'desc'])
                    ? $sort['direction'] : 'asc';
                $query = $query->orderBy($column, $direction);
            } else {
                // Handle simple array like ['name', 'created_at']
                $column = $substitutions[$sort] ?? $sort;
                $query = $query->orderBy($column, 'asc');
            }
        }
        return $query;
    }
}
