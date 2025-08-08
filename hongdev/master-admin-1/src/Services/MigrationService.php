<?php

namespace Hongdev\MasterAdmin\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class MigrationService
{
    private $migrationPaths;

    public function __construct()
    {
        $this->migrationPaths = [
            database_path('migrations'),
            base_path('database/migrations')
        ];
    }

    /**
     * Get list of all migrations
     *
     * @return array
     */
    public function getAllMigrations()
    {
        $migrations = [];

        foreach ($this->migrationPaths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $files = File::glob($path . '/*.php');
            
            foreach ($files as $file) {
                $filename = basename($file);
                $migrations[] = [
                    'filename' => $filename,
                    'path' => $file,
                    'name' => $this->extractMigrationName($filename),
                    'table' => $this->extractTableName($filename),
                    'type' => $this->getMigrationType($filename),
                    'created_at' => File::lastModified($file),
                    'size' => File::size($file),
                    'executed' => $this->isMigrationExecuted($filename)
                ];
            }
        }

        // Sort by creation time (newest first)
        usort($migrations, function($a, $b) {
            return $b['created_at'] - $a['created_at'];
        });

        return $migrations;
    }

    /**
     * Find migration by name pattern
     *
     * @param string $pattern
     * @return array
     */
    public function findMigrationsByName($pattern)
    {
        $allMigrations = $this->getAllMigrations();
        $foundMigrations = [];

        foreach ($allMigrations as $migration) {
            if (
                stripos($migration['filename'], $pattern) !== false ||
                stripos($migration['name'], $pattern) !== false ||
                stripos($migration['table'], $pattern) !== false
            ) {
                $foundMigrations[] = $migration;
            }
        }

        return $foundMigrations;
    }

    /**
     * Find specific migration file
     *
     * @param string $pattern
     * @param string|null $path
     * @return array|null
     */
    public function findMigrationFile($pattern, $path = null)
    {
        $searchPaths = $path ? [$path] : $this->migrationPaths;

        foreach ($searchPaths as $searchPath) {
            if (!File::exists($searchPath)) {
                continue;
            }

            $files = File::glob($searchPath . '/' . $pattern);
            
            if (!empty($files)) {
                $file = $files[0]; // Get the first match
                return $this->getMigrationDetails($file);
            }
        }

        return null;
    }

    /**
     * Get migration details including columns
     *
     * @param string $migrationFile
     * @return array
     */
    public function getMigrationDetails($migrationFile)
    {
        if (!File::exists($migrationFile)) {
            return null;
        }

        $content = File::get($migrationFile);
        $filename = basename($migrationFile);

        return [
            'filename' => $filename,
            'path' => $migrationFile,
            'name' => $this->extractMigrationName($filename),
            'table' => $this->extractTableName($filename),
            'type' => $this->getMigrationType($filename),
            'content' => $content,
            'columns' => $this->extractColumnsFromMigration($content),
            'indexes' => $this->extractIndexesFromMigration($content),
            'foreign_keys' => $this->extractForeignKeysFromMigration($content),
            'created_at' => File::lastModified($migrationFile),
            'size' => File::size($migrationFile),
            'executed' => $this->isMigrationExecuted($filename)
        ];
    }

    /**
     * Get columns configuration from migration content
     *
     * @param string $migrationName
     * @return array
     */
    public function getMigrationColumns($migrationName)
    {
        // Find migration by name
        $migration = $this->findMigrationsByName($migrationName);
        
        if (empty($migration)) {
            return [];
        }

        $migrationFile = $migration[0]['path'];
        $details = $this->getMigrationDetails($migrationFile);
        
        return $details ? $details['columns'] : [];
    }

    /**
     * Extract columns from migration content
     *
     * @param string $content
     * @return array
     */
    private function extractColumnsFromMigration($content)
    {
        $columns = [];
        
        // Extract table columns using regex
        preg_match_all('/\$table->(\w+)\([\'"]?([^\'",\)]+)[\'"]?(?:,\s*([^)]+))?\)(?:->(\w+(?:\([^)]*\))?))*(?:->(\w+(?:\([^)]*\))?))*(?:;|\s*->)/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $type = $match[1];
            $name = $match[2] ?? '';
            $length = isset($match[3]) ? trim($match[3], '"\'') : null;
            
            // Skip if it's a method call without column name
            if (empty($name) || in_array($type, ['timestamps', 'softDeletes', 'rememberToken', 'index', 'unique', 'foreign'])) {
                continue;
            }

            $column = [
                'name' => $name,
                'type' => $type,
                'length' => $length,
                'nullable' => $this->hasModifier($content, $name, 'nullable'),
                'unique' => $this->hasModifier($content, $name, 'unique'),
                'index' => $this->hasModifier($content, $name, 'index'),
                'default' => $this->getDefaultValue($content, $name),
                'unsigned' => $this->hasModifier($content, $name, 'unsigned'),
                'auto_increment' => $this->hasModifier($content, $name, 'autoIncrement'),
                'primary' => $this->hasModifier($content, $name, 'primary')
            ];

            $columns[] = $column;
        }

        // Check for common Laravel methods
        if (strpos($content, '$table->timestamps()') !== false) {
            $columns[] = ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true];
            $columns[] = ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => true];
        }

        if (strpos($content, '$table->softDeletes()') !== false) {
            $columns[] = ['name' => 'deleted_at', 'type' => 'timestamp', 'nullable' => true];
        }

        if (strpos($content, '$table->rememberToken()') !== false) {
            $columns[] = ['name' => 'remember_token', 'type' => 'string', 'length' => 100, 'nullable' => true];
        }

        return $columns;
    }

    /**
     * Extract indexes from migration content
     *
     * @param string $content
     * @return array
     */
    private function extractIndexesFromMigration($content)
    {
        $indexes = [];
        
        // Extract index definitions
        preg_match_all('/\$table->(?:index|unique)\((?:\[)?[\'"]([^\'"]+)[\'"](?:\])?(?:,\s*[\'"]([^\'"]*)[\'"]\s*)?\)/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $indexes[] = [
                'column' => $match[1],
                'name' => $match[2] ?? null,
                'type' => strpos($match[0], 'unique') !== false ? 'unique' : 'index'
            ];
        }

        return $indexes;
    }

    /**
     * Extract foreign keys from migration content
     *
     * @param string $content
     * @return array
     */
    private function extractForeignKeysFromMigration($content)
    {
        $foreignKeys = [];
        
        // Extract foreign key definitions
        preg_match_all('/\$table->foreign\([\'"]([^\'"]+)[\'"]\)->references\([\'"]([^\'"]+)[\'"]\)->on\([\'"]([^\'"]+)[\'"]\)/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $foreignKeys[] = [
                'column' => $match[1],
                'references' => $match[2],
                'on' => $match[3]
            ];
        }

        return $foreignKeys;
    }

    /**
     * Check if a column has a specific modifier
     *
     * @param string $content
     * @param string $columnName
     * @param string $modifier
     * @return bool
     */
    private function hasModifier($content, $columnName, $modifier)
    {
        $pattern = "/\\\$table->\\w+\\(['\"]" . preg_quote($columnName) . "['\"].*?\\)->{$modifier}\\(\\)/";
        return preg_match($pattern, $content) === 1;
    }

    /**
     * Get default value for a column
     *
     * @param string $content
     * @param string $columnName
     * @return mixed
     */
    private function getDefaultValue($content, $columnName)
    {
        $pattern = "/\\\$table->\\w+\\(['\"]" . preg_quote($columnName) . "['\"].*?\\)->default\\(['\"]?([^'\")]+)['\"]?\\)/";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract migration name from filename
     *
     * @param string $filename
     * @return string
     */
    private function extractMigrationName($filename)
    {
        // Remove timestamp and .php extension
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
        $name = str_replace('.php', '', $name);
        return $name;
    }

    /**
     * Extract table name from migration filename
     *
     * @param string $filename
     * @return string
     */
    private function extractTableName($filename)
    {
        if (preg_match('/create_(\w+)_table/', $filename, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/add_\w+_to_(\w+)_table/', $filename, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/modify_(\w+)_table/', $filename, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Get migration type
     *
     * @param string $filename
     * @return string
     */
    private function getMigrationType($filename)
    {
        if (strpos($filename, 'create_') !== false) {
            return 'create';
        }
        
        if (strpos($filename, 'add_') !== false) {
            return 'add';
        }
        
        if (strpos($filename, 'modify_') !== false || strpos($filename, 'alter_') !== false) {
            return 'modify';
        }
        
        if (strpos($filename, 'drop_') !== false) {
            return 'drop';
        }

        return 'unknown';
    }

    /**
     * Check if migration has been executed
     *
     * @param string $filename
     * @return bool
     */
    private function isMigrationExecuted($filename)
    {
        try {
            $migrationName = str_replace('.php', '', $filename);
            $result = DB::table('migrations')
                ->where('migration', $migrationName)
                ->first();
            
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
