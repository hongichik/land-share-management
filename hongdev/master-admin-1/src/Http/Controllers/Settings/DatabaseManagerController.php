<?php

namespace Hongdev\MasterAdmin\Http\Controllers\Settings;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseManagerController extends Controller
{
    /**
     * Show database overview with all tables
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            $tables = [];
            
            if ($driver === 'mysql') {
                $tablesResult = DB::select('SHOW TABLES');
                $tableColumn = 'Tables_in_' . config("database.connections.{$connection}.database");
                
                foreach ($tablesResult as $table) {
                    $tableName = $table->$tableColumn;
                    $tableInfo = DB::select("SHOW TABLE STATUS LIKE '{$tableName}'")[0];
                    $tables[] = [
                        'name' => $tableName,
                        'rows' => $tableInfo->Rows ?? 0,
                        'size' => $this->formatBytes(($tableInfo->Data_length ?? 0) + ($tableInfo->Index_length ?? 0)),
                        'engine' => $tableInfo->Engine ?? 'Unknown',
                        'collation' => $tableInfo->Collation ?? 'Unknown'
                    ];
                }
            } elseif ($driver === 'sqlite') {
                $tablesResult = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tablesResult as $table) {
                    $count = DB::table($table->name)->count();
                    $tables[] = [
                        'name' => $table->name,
                        'rows' => $count,
                        'size' => 'N/A',
                        'engine' => 'SQLite',
                        'collation' => 'N/A'
                    ];
                }
            }
            
            return view('master-admin::master-admin.page.settings.database.manager.index', [
                'tables' => $tables,
                'driver' => $driver
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading database: ' . $e->getMessage());
        }
    }

    /**
     * Show table structure and data
     *
     * @param string $table
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showTable($table, Request $request)
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            // Get table structure
            $columns = [];
            if ($driver === 'mysql') {
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } elseif ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info({$table})");
            }
            
            // Build query with search
            $query = DB::table($table);
            $search = $request->get('search');
            
            if ($search) {
                // Add WHERE conditions for search across all columns
                $query->where(function($q) use ($search, $table, $driver) {
                    $tableColumns = [];
                    
                    if ($driver === 'mysql') {
                        $tableColumns = DB::select("SHOW COLUMNS FROM `{$table}`");
                        foreach ($tableColumns as $column) {
                            $q->orWhere($column->Field, 'LIKE', "%{$search}%");
                        }
                    } elseif ($driver === 'sqlite') {
                        $tableColumns = DB::select("PRAGMA table_info({$table})");
                        foreach ($tableColumns as $column) {
                            $q->orWhere($column->name, 'LIKE', "%{$search}%");
                        }
                    }
                });
            }
            
            // Get sorting parameters
            $sortBy = $request->get('sort', 'id');
            $sortOrder = $request->get('order', 'asc');
            
            // Apply sorting if column exists
            $validColumns = [];
            if ($driver === 'mysql') {
                $validColumns = array_column($columns, 'Field');
            } elseif ($driver === 'sqlite') {
                $validColumns = array_column($columns, 'name');
            }
            
            if (in_array($sortBy, $validColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Get paginated results
            $perPage = $request->get('per_page', 25);
            $data = $query->paginate($perPage)->appends($request->query());
            
            return view('master-admin::master-admin.page.settings.database.manager.table', [
                'table' => $table,
                'columns' => $columns,
                'data' => $data,
                'driver' => $driver,
                'search' => $search,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'perPage' => $perPage
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading table: ' . $e->getMessage());
        }
    }

    /**
     * Show create table form
     *
     * @return \Illuminate\View\View
     */
    public function createTable()
    {
        return view('master-admin::master-admin.page.settings.database.manager.create-table');
    }

    /**
     * Store new table
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTable(Request $request)
    {
        $request->validate([
            'table_name' => 'required|string|max:64',
            'columns' => 'required|array|min:1',
            'columns.*.name' => 'required|string',
            'columns.*.type' => 'required|string',
            'columns.*.length' => 'nullable|string',
            'columns.*.null' => 'boolean',
            'columns.*.primary' => 'boolean',
            'columns.*.auto_increment' => 'boolean',
        ]);

        try {
            $tableName = $request->table_name;
            $columns = $request->columns;
            
            $sql = "CREATE TABLE `{$tableName}` (";
            $columnDefinitions = [];
            $primaryKeys = [];
            
            foreach ($columns as $column) {
                $def = "`{$column['name']}` {$column['type']}";
                
                if (!empty($column['length'])) {
                    $def .= "({$column['length']})";
                }
                
                if (empty($column['null'])) {
                    $def .= " NOT NULL";
                }
                
                if (!empty($column['auto_increment'])) {
                    $def .= " AUTO_INCREMENT";
                }
                
                $columnDefinitions[] = $def;
                
                if (!empty($column['primary'])) {
                    $primaryKeys[] = "`{$column['name']}`";
                }
            }
            
            $sql .= implode(', ', $columnDefinitions);
            
            if (!empty($primaryKeys)) {
                $sql .= ', PRIMARY KEY (' . implode(', ', $primaryKeys) . ')';
            }
            
            $sql .= ')';
            
            DB::statement($sql);
            
            return redirect()->route('master-admin.settings.database.manager.index')
                           ->with('success', "Table '{$tableName}' created successfully");
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error creating table: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Drop table
     *
     * @param string $table
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dropTable($table)
    {
        try {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
            return redirect()->route('master-admin.settings.database.manager.index')
                           ->with('success', "Table '{$table}' dropped successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error dropping table: ' . $e->getMessage());
        }
    }

    /**
     * Show insert record form
     *
     * @param string $table
     * @return \Illuminate\View\View
     */
    public function createRecord($table)
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            $columns = [];
            if ($driver === 'mysql') {
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } elseif ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info({$table})");
            }
            
            return view('master-admin::master-admin.page.settings.database.manager.create-record', [
                'table' => $table,
                'columns' => $columns,
                'driver' => $driver
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading table structure: ' . $e->getMessage());
        }
    }

    /**
     * Store new record
     *
     * @param Request $request
     * @param string $table
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRecord(Request $request, $table)
    {
        try {
            $data = $request->except(['_token']);
            DB::table($table)->insert($data);
            
            return redirect()->route('master-admin.settings.database.manager.table', $table)
                           ->with('success', 'Record inserted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error inserting record: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Show edit record form
     *
     * @param string $table
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function editRecord($table, $id)
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            $columns = [];
            if ($driver === 'mysql') {
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } elseif ($driver === 'sqlite') {
                $columns = DB::select("PRAGMA table_info({$table})");
            }
            
            $record = DB::table($table)->where('id', $id)->first();
            
            if (!$record) {
                return redirect()->back()->with('error', 'Record not found');
            }
            
            return view('master-admin::master-admin.page.settings.database.manager.edit-record', [
                'table' => $table,
                'columns' => $columns,
                'record' => $record,
                'driver' => $driver
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading record: ' . $e->getMessage());
        }
    }

    /**
     * Update record
     *
     * @param Request $request
     * @param string $table
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRecord(Request $request, $table, $id)
    {
        try {
            $data = $request->except(['_token', '_method']);
            DB::table($table)->where('id', $id)->update($data);
            
            return redirect()->route('master-admin.settings.database.manager.table', $table)
                           ->with('success', 'Record updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error updating record: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Delete record
     *
     * @param string $table
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteRecord($table, $id)
    {
        try {
            DB::table($table)->where('id', $id)->delete();
            
            return redirect()->route('master-admin.settings.database.manager.table', $table)
                           ->with('success', 'Record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting record: ' . $e->getMessage());
        }
    }

    /**
     * Export table as SQL
     *
     * @param string $table
     * @return \Illuminate\Http\Response
     */
    public function exportTable($table)
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            $sql = "-- Export for table: {$table}\n\n";
            
            if ($driver === 'mysql') {
                // Get create table statement
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
                $sql .= $createTable->{'Create Table'} . ";\n\n";
                
                // Get data
                $records = DB::table($table)->get();
                if ($records->count() > 0) {
                    $sql .= "INSERT INTO `{$table}` VALUES\n";
                    $values = [];
                    foreach ($records as $record) {
                        $recordArray = (array) $record;
                        $escapedValues = array_map(function($value) {
                            return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                        }, $recordArray);
                        $values[] = '(' . implode(', ', $escapedValues) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n";
                }
            }
            
            $filename = $table . '_export_' . date('Y-m-d_H-i-s') . '.sql';
            
            return response($sql)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting table: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
