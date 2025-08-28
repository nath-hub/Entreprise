<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait MultiDatabaseTrait
{
  protected function getDatabaseConnection($user = null)
    {
        if ($user && $user->environment === 'prod') {
            return 'mysql_prod';
        }
        return 'mysql_sandbox';
    }

    protected function executeOnBothDatabases(\Closure $callback, $data = null)
    {
        $results = [];

        foreach (['mysql_prod', 'mysql_sandbox'] as $connection) {
            try {
                DB::connection($connection)->beginTransaction();
                $results[$connection] = $callback($connection, $data);
                DB::connection($connection)->commit();
            } catch (\Exception $e) {
                DB::connection($connection)->rollBack();
                Log::error("Erreur sur {$connection}: " . $e->getMessage());
                throw $e;
            }
        }

        return $results;
    }
}
