<?php

namespace Sofa\DbQueriesAlert;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

class Middleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (config('db_queries_alert.enabled')) {
            $this->crunchTheNumbers();
        }

        return $response;
    }

    /**
     * Check the queries executed during this request and log
     * info|warning|error when threshold has been exceeded.
     *
     * @return void
     */
    protected function crunchTheNumbers() : void
    {
        $queries = DB::getQueryLog();
        $query_count = count($queries);

        if ($query_count >= config('db_queries_alert.error', -1)) {
            $level = 'error';
        } elseif ($query_count >= config('db_queries_alert.warning', -1)) {
            $level = 'warning';
        } elseif ($query_count >= config('db_queries_alert.info', -1)) {
            $level = 'info';
        } else {
            return;
        }

        $route = Route::current();

        Log::{$level}('Check your queries bro!', [
            'url' => Request::fullUrl(),
            'action' => $route ? $route->getActionName() : null,
            'queries' => $query_count,
            'details' => $this->getBreakdown($queries),
        ]);
    }

    /**
     * Build simple breakdown of the queries.
     *
     *  [
     *      "url" => "https://some.url",
     *      "action" => "App\Http\SomeController@someMethod",
     *      "queries" => 170,
     *      "details" =>  [
     *          "select" => [
     *              "type" => "select",
     *              "count" => [
     *                  "total" => 10,
     *                  "by_table" => [
     *                      "permissions" => 25,
     *                      "users" => 25,
     *                      "roles" => 20
     *                  ],
     *              ],
     *          ],
     *          "insert" => [
     *              "type" => "insert",
     *              "count" => [
     *                  "total" => 1,
     *                  "by_table" => [
     *                      "some_table" => 100,
     *                  ]
     *              ]
     *          ]
     *      ]
     *  ]
     *
     *
     * @param  array  $queries
     * @return array
     */
    private function getBreakdown(array $queries) : array
    {
        return collect($queries)
            ->groupBy(function ($query) {
                return $this->parseStatement($query['query']);
            })
            ->map(function ($queries_by_type, $type) {
                return [
                    'type' => $type,
                    'count' => [
                        'total' => count($queries_by_type),
                        'by_table' => collect($queries_by_type)->groupBy(function ($query) {
                            return $this->parseTable($query['query']);
                        })
                        ->map->count()
                        ->sort()->reverse()
                        ->toArray(),
                    ],
                ];
            })
            ->sortByDesc('count.total')
            ->toArray();
    }

    /**
     * Get type of the statement.
     *
     * @param  string $query
     * @return string
     */
    private function parseStatement(string $query) : string
    {
        return strtolower(preg_split('/\h+/', $query, 2, PREG_SPLIT_NO_EMPTY)[0] ?? null);
    }

    /**
     * Get table name from the query.
     *
     * @param  string $query
     * @return string
     */
    private function parseTable(string $query) : string
    {
        preg_match('/(from|into|update)\h+`?(\w+)`?\b/', $query, $matches);

        return strtolower($matches[2] ?? null);
    }
}
