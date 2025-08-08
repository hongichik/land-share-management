<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DatabaseController extends Controller
{
    /**
     * Show database configuration form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $dbConfig = [
            'connection' => config('database.default'),
            'host' => config('database.connections.' . config('database.default') . '.host'),
            'port' => config('database.connections.' . config('database.default') . '.port'),
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'username' => config('database.connections.' . config('database.default') . '.username'),
            'password' => '********', // Don't expose the actual password
        ];
        
        $drivers = ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
        
        return view('master-admin::master-admin.page.settings.database.index', [
            'config' => $dbConfig,
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Update database configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'connection' => 'required|in:mysql,pgsql,sqlite,sqlsrv',
            'host' => 'required_unless:connection,sqlite',
            'port' => 'required_unless:connection,sqlite',
            'database' => 'required',
            'username' => 'required_unless:connection,sqlite',
        ]);
        
        try {
            // Get .env file contents
            $envPath = base_path('.env');
            $envContent = File::get($envPath);
            
            // Update database settings
            $updates = [
                'DB_CONNECTION' => $request->connection,
                'DB_HOST' => $request->host,
                'DB_PORT' => $request->port,
                'DB_DATABASE' => $request->database,
                'DB_USERNAME' => $request->username,
            ];
            
            // Only update password if provided
            if ($request->filled('password')) {
                $updates['DB_PASSWORD'] = $request->password;
            }
            
            // Apply updates to .env file
            foreach ($updates as $key => $value) {
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
            }
            
            // Save updated .env file
            File::put($envPath, $envContent);
            
            // Clear config cache
            Artisan::call('config:clear');
            
            return redirect()->route('master-admin.settings.database.index')
                           ->with('success', 'Database configuration updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error updating database configuration: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Test the database connection
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function test()
    {
        try {
            $connection = DB::connection()->getPdo();
            $databaseName = DB::connection()->getDatabaseName();
            
            return redirect()->back()->with('success', "Successfully connected to database: {$databaseName}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Drop all tables in the database
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dropAllTables()
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            if ($driver === 'mysql') {
                // Get all table names
                $tables = DB::select('SHOW TABLES');
                $tableColumn = 'Tables_in_' . config("database.connections.{$connection}.database");
                
                if (!empty($tables)) {
                    // Disable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                    
                    foreach ($tables as $table) {
                        $tableName = $table->$tableColumn;
                        DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                    }
                    
                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                }
            } elseif ($driver === 'pgsql') {
                // PostgreSQL
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                
                foreach ($tables as $table) {
                    DB::statement("DROP TABLE IF EXISTS \"{$table->tablename}\" CASCADE");
                }
            } elseif ($driver === 'sqlite') {
                // SQLite
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                
                foreach ($tables as $table) {
                    DB::statement("DROP TABLE IF EXISTS \"{$table->name}\"");
                }
            }
            
            return redirect()->back()
                ->with('success', 'All tables dropped successfully')
                ->with('active_tab', 'tools');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error dropping tables: ' . $e->getMessage())
                ->with('active_tab', 'tools');
        }
    }

    /**
     * Show import SQL form
     *
     * @return \Illuminate\View\View
     */
    public function showImport()
    {
        return view('master-admin::master-admin.page.settings.database.import');
    }

    /**
     * Import SQL file
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importSql(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200', // Max 50MB
        ]);

        try {
            $file = $request->file('sql_file');
            $sqlContent = file_get_contents($file->getPathname());
            
            // Split SQL content by semicolons to get individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sqlContent)),
                function($statement) {
                    return !empty($statement) && !preg_match('/^\s*--/', $statement);
                }
            );

            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            // Disable foreign key checks for MySQL
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            $executedCount = 0;
            foreach ($statements as $statement) {
                try {
                    DB::statement($statement);
                    $executedCount++;
                } catch (\Exception $e) {
                    \Log::warning("Failed to execute SQL statement: " . substr($statement, 0, 100) . "... Error: " . $e->getMessage());
                }
            }

            // Re-enable foreign key checks for MySQL
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            return redirect()->back()
                ->with('success', "SQL file imported successfully. Executed {$executedCount} statements.")
                ->with('active_tab', 'tools');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing SQL file: ' . $e->getMessage())
                ->with('active_tab', 'tools');
        }
    }

    /**
     * Execute custom SQL query
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executeQuery(Request $request)
    {
        $request->validate([
            'sql_query' => 'required|string',
        ]);

        try {
            $query = trim($request->sql_query);
            
            // Basic security check - prevent dangerous operations
            $dangerousKeywords = ['DROP DATABASE', 'CREATE DATABASE', 'GRANT', 'REVOKE'];
            foreach ($dangerousKeywords as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    return redirect()->back()
                        ->with('error', "Dangerous operation '{$keyword}' is not allowed.")
                        ->with('active_tab', 'query')
                        ->withInput();
                }
            }

            if (stripos($query, 'SELECT') === 0) {
                // For SELECT queries, return results
                $results = DB::select($query);
                return redirect()->back()
                    ->with('success', 'Query executed successfully. Found ' . count($results) . ' results.')
                    ->with('active_tab', 'query')
                    ->withInput();
            } else {
                // For other queries (INSERT, UPDATE, DELETE, etc.)
                $affected = DB::statement($query);
                return redirect()->back()
                    ->with('success', 'Query executed successfully.')
                    ->with('active_tab', 'query')
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error executing query: ' . $e->getMessage())
                ->with('active_tab', 'query')
                ->withInput();
        }
    }
}
