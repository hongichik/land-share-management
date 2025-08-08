<?php

namespace Hongdev\MasterAdmin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class MasterAdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('master-admin::master-admin.page.welcome');
    }

    /**
     * Execute an Artisan command and return to dashboard
     *
     * @param Request $request
     * @param string $command
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executeCommand(Request $request, $command)
    {
        $result = '';
        $success = true;
        
        try {
            switch ($command) {
                // Cache Commands
                case 'cache-clear':
                    Artisan::call('cache:clear');
                    $result = 'Application cache cleared successfully';
                    break;
                    
                case 'config-clear':
                    Artisan::call('config:clear');
                    $result = 'Configuration cache cleared successfully';
                    break;
                    
                case 'config-cache':
                    Artisan::call('config:cache');
                    $result = 'Configuration cached successfully';
                    break;
                    
                case 'route-clear':
                    Artisan::call('route:clear');
                    $result = 'Route cache cleared successfully';
                    break;
                    
                case 'route-cache':
                    Artisan::call('route:cache');
                    $result = 'Routes cached successfully';
                    break;
                    
                case 'view-clear':
                    Artisan::call('view:clear');
                    $result = 'View cache cleared successfully';
                    break;
                    
                case 'optimize-clear':
                    Artisan::call('optimize:clear');
                    $result = 'All caches cleared successfully';
                    break;
                    
                case 'optimize':
                    Artisan::call('optimize');
                    $result = 'Application optimized successfully';
                    break;
                
                // Database Commands
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    $result = 'Database migrations executed successfully';
                    break;
                    
                case 'migrate-fresh':
                    Artisan::call('migrate:fresh', ['--force' => true]);
                    $result = 'Database dropped and re-migrated successfully';
                    break;
                    
                case 'migrate-status':
                    $output = Artisan::call('migrate:status');
                    $result = 'Migration status checked. See logs for details.';
                    \Log::info(Artisan::output());
                    break;
                    
                case 'db-seed':
                    Artisan::call('db:seed', ['--force' => true]);
                    $result = 'Database seeded successfully';
                    break;
                
                case 'db-wipe':
                    Artisan::call('db:wipe', ['--force' => true]);
                    $result = 'Database wiped successfully';
                    break;
                
                // System Commands
                case 'storage-link':
                    Artisan::call('storage:link');
                    $result = 'Storage link created successfully';
                    break;
                    
                case 'key-generate':
                    Artisan::call('key:generate', ['--force' => true]);
                    $result = 'Application key generated successfully';
                    break;
                    
                case 'down':
                    $params = [];
    
                    if ($request->has('secret')) {
                        $params['--secret'] = $request->get('secret');
                        session(['maintenance_bypass_token' => $request->get('secret')]);
                        $result = 'Application is now in maintenance mode. You can bypass it using: ' . 
                                  url('/') . '?secret=' . $request->get('secret');
                    } else {
                        $result = 'Application is now in maintenance mode';
                    }
        
                    if (!empty($params)) {
                        Artisan::call('down', $params);
                    } else {
                        Artisan::call('down');
                    }
                    break;
                    
                case 'up':
                    Artisan::call('up');
                    $result = 'Application is now live';
                    break;
                
                // Queue & Schedule Commands
                case 'queue-work':
                    $result = 'Queue worker should be started with: php artisan queue:work';
                    \Log::info('Queue worker command requested');
                    break;
                    
                case 'queue-restart':
                    Artisan::call('queue:restart');
                    $result = 'Queue workers restarted successfully';
                    break;
                    
                case 'queue-retry-all':
                    Artisan::call('queue:retry', ['--all' => true]);
                    $result = 'All failed jobs have been pushed back onto the queue';
                    break;
                    
                case 'queue-clear':
                    Artisan::call('queue:clear', ['--all' => true]);
                    $result = 'Failed job queue cleared successfully';
                    break;
                
                case 'schedule-list':
                    $output = Artisan::call('schedule:list');
                    $result = 'Schedule list retrieved. See logs for details.';
                    \Log::info(Artisan::output());
                    break;
                    
                case 'schedule-run':
                    Artisan::call('schedule:run');
                    $result = 'Scheduled tasks run successfully';
                    break;
                
                // Package Management Commands
                case 'package-discover':
                    Artisan::call('package:discover');
                    $result = 'Packages discovered successfully';
                    break;
                    
                case 'vendor-publish-all':
                    Artisan::call('vendor:publish', ['--all' => true, '--force' => true]);
                    $result = 'All vendor assets published successfully';
                    break;
                    
                case 'publish-master-admin-public':
                    Artisan::call('vendor:publish', ['--tag' => 'master-admin-public', '--force' => true]);
                    $result = 'Master Admin public assets published successfully';
                    break;
                    
                case 'publish-master-admin-environment':
                    Artisan::call('vendor:publish', ['--tag' => 'master-admin-environment', '--force' => true]);
                    $result = 'Master Admin environment files published successfully';
                    break;
                    
                case 'composer-update':
                    $result = 'Composer update should be run manually from the command line';
                    break;
                    
                case 'composer-dump-autoload':
                    if (function_exists('exec')) {
                        exec('composer dump-autoload -o');
                        $result = 'Composer autoload dumped successfully';
                    } else {
                        $result = 'Cannot execute composer dump-autoload (exec function disabled)';
                    }
                    break;
                    
                case 'npm-install':
                    $result = 'NPM install should be run manually from the command line';
                    break;
                    
                case 'npm-run-dev':
                    $result = 'NPM run dev should be run manually from the command line';
                    break;
                
                // Route Commands
                case 'route-list':
                    $output = Artisan::call('route:list');
                    $result = 'Route list retrieved. See logs for details.';
                    \Log::info(Artisan::output());
                    break;
                
                // Make Commands
                case 'make-controller':
                    $result = 'Please use Artisan command to create a controller: php artisan make:controller YourController';
                    break;
                    
                case 'make-model':
                    $result = 'Please use Artisan command to create a model: php artisan make:model YourModel';
                    break;
                    
                case 'make-migration':
                    $result = 'Please use Artisan command to create a migration: php artisan make:migration create_your_table';
                    break;
                    
                case 'make-seeder':
                    $result = 'Please use Artisan command to create a seeder: php artisan make:seeder YourSeeder';
                    break;
                    
                case 'make-middleware':
                    $result = 'Please use Artisan command to create middleware: php artisan make:middleware YourMiddleware';
                    break;
                    
                default:
                    $result = 'Unknown command';
                    $success = false;
            }
        } catch (\Exception $e) {
            $result = 'Error: ' . $e->getMessage();
            $success = false;
        }
        
        if ($success) {
            session()->flash('success', $result);
        } else {
            session()->flash('error', $result);
        }
        
        return redirect()->back();
    }

    /**
     * Get system performance metrics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPerformanceMetrics()
    {
        $metrics = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'server_load' => $this->getServerLoad(),
            'uptime' => $this->getUptime(),
        ];
        
        return response()->json($metrics);
    }
    
    /**
     * Get CPU usage
     *
     * @return array
     */
    private function getCpuUsage()
    {
        $cpuUsage = 0;
        $cpuCount = 0;
        
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Darwin') {
                $cores = (int) shell_exec('sysctl -n hw.ncpu');
                $loadOutput = shell_exec('sysctl -n vm.loadavg');
                
                if ($loadOutput) {
                    $matches = [];
                    if (preg_match('/{\s*([\d.]+)/', $loadOutput, $matches)) {
                        $load = (float) $matches[1];
                        if ($cores > 0) {
                            $cpuUsage = min(100, round(($load / $cores) * 100, 2));
                            $cpuCount = $cores;
                        }
                    }
                }
                
                if ($cpuUsage === 0) {
                    $topOutput = shell_exec('top -l 1 | grep "CPU usage"');
                    if (preg_match('/(\d+\.\d+)%\s+user/', $topOutput, $matches)) {
                        $userPercentage = (float) $matches[1];
                        if (preg_match('/(\d+\.\d+)%\s+sys/', $topOutput, $matches)) {
                            $sysPercentage = (float) $matches[1];
                            $cpuUsage = round($userPercentage + $sysPercentage, 2);
                        }
                    }
                }
            } elseif (PHP_OS !== 'WINNT') {
                $load = sys_getloadavg();
                $cores = (int) shell_exec('nproc');
                
                if ($cores > 0) {
                    $cpuUsage = min(100, round(($load[0] / $cores) * 100, 2));
                    $cpuCount = $cores;
                }
            } elseif (function_exists('shell_exec')) {
                $cmd = 'wmic cpu get LoadPercentage';
                $output = shell_exec($cmd);
                if (preg_match('/(\d+)/', $output, $matches)) {
                    $cpuUsage = (float) $matches[1];
                }
                
                $cmd = 'wmic cpu get NumberOfCores';
                $output = shell_exec($cmd);
                if (preg_match('/(\d+)/', $output, $matches)) {
                    $cpuCount = (int) $matches[1];
                }
            }
        }
        
        return [
            'usage' => $cpuUsage,
            'cores' => $cpuCount,
        ];
    }
    
    /**
     * Get memory usage
     *
     * @return array
     */
    private function getMemoryUsage()
    {
        $totalMemory = 0;
        $freeMemory = 0;
        $usedMemory = 0;
        $usedPercentage = 0;
        
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Darwin') {
                $totalMemory = (int) shell_exec('sysctl -n hw.memsize');
                
                $vmStat = shell_exec('vm_stat');
                $pageSize = 4096;
                
                if (preg_match('/page size of (\d+) bytes/', $vmStat, $matches)) {
                    $pageSize = (int) $matches[1];
                }
                
                $freePages = 0;
                if (preg_match('/Pages free:\s+(\d+)/', $vmStat, $matches)) {
                    $freePages += (int) $matches[1];
                }
                if (preg_match('/Pages inactive:\s+(\d+)/', $vmStat, $matches)) {
                    $freePages += (int) $matches[1];
                }
                
                $freeMemory = $freePages * $pageSize;
                $usedMemory = $totalMemory - $freeMemory;
                $usedPercentage = round(($usedMemory / $totalMemory) * 100, 2);
            } elseif (PHP_OS !== 'WINNT') {
                $memInfo = shell_exec('free -b');
                if (preg_match('/^Mem:\s+(\d+)\s+(\d+)/m', $memInfo, $matches)) {
                    $totalMemory = $matches[1];
                    $usedMemory = $matches[2];
                    $freeMemory = $totalMemory - $usedMemory;
                    $usedPercentage = round(($usedMemory / $totalMemory) * 100, 2);
                }
            } elseif (PHP_OS === 'WINNT' && function_exists('shell_exec')) {
                $cmd = 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value';
                $output = shell_exec($cmd);
                
                if (preg_match('/TotalVisibleMemorySize=(\d+)/i', $output, $matches)) {
                    $totalMemory = (int) $matches[1] * 1024;
                }
                
                if (preg_match('/FreePhysicalMemory=(\d+)/i', $output, $matches)) {
                    $freeMemory = (int) $matches[1] * 1024;
                }
                
                $usedMemory = $totalMemory - $freeMemory;
                $usedPercentage = round(($usedMemory / $totalMemory) * 100, 2);
            }
        }
        
        $formatMemory = function($bytes) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= (1 << (10 * $pow));
            
            return round($bytes, 2) . ' ' . $units[$pow];
        };
        
        return [
            'total' => $formatMemory($totalMemory),
            'used' => $formatMemory($usedMemory),
            'free' => $formatMemory($freeMemory),
            'percentage' => $usedPercentage,
        ];
    }
    
    /**
     * Get disk usage
     *
     * @return array
     */
    private function getDiskUsage()
    {
        $path = base_path();
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = round(($usedSpace / $totalSpace) * 100, 2);
        
        $formatDisk = function($bytes) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= (1 << (10 * $pow));
            
            return round($bytes, 2) . ' ' . $units[$pow];
        };
        
        return [
            'total' => $formatDisk($totalSpace),
            'used' => $formatDisk($usedSpace),
            'free' => $formatDisk($freeSpace),
            'percentage' => $usedPercentage,
        ];
    }
    
    /**
     * Get server load average
     *
     * @return array
     */
    private function getServerLoad()
    {
        $load = [0, 0, 0];
        
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
        }
        
        return [
            '1min' => round($load[0], 2),
            '5min' => round($load[1], 2),
            '15min' => round($load[2], 2),
        ];
    }
    
    /**
     * Get system uptime
     *
     * @return string
     */
    private function getUptime()
    {
        $uptime = 'Unknown';
        
        if (function_exists('shell_exec')) {
            if (PHP_OS === 'Darwin') {
                $bootTime = shell_exec('sysctl -n kern.boottime');
                if (preg_match('/sec = (\d+)/', $bootTime, $matches)) {
                    $bootTimestamp = (int) $matches[1];
                    $uptimeSeconds = time() - $bootTimestamp;
                    $days = floor($uptimeSeconds / 86400);
                    $hours = floor(($uptimeSeconds % 86400) / 3600);
                    $minutes = floor(($uptimeSeconds % 3600) / 60);
                    
                    $uptime = '';
                    if ($days > 0) $uptime .= $days . ' days, ';
                    $uptime .= $hours . ' hours, ' . $minutes . ' minutes';
                }
            } elseif (PHP_OS !== 'WINNT') {
                $uptimeOutput = shell_exec('uptime -p');
                if ($uptimeOutput) {
                    $uptime = trim($uptimeOutput);
                }
            } elseif (PHP_OS === 'WINNT' && function_exists('shell_exec')) {
                $cmd = 'wmic os get lastbootuptime';
                $output = shell_exec($cmd);
                if (preg_match('/(\d{14})/', $output, $matches)) {
                    $bootTime = \DateTime::createFromFormat('YmdHis', $matches[1]);
                    $now = new \DateTime();
                    $diff = $now->diff($bootTime);
                    
                    $uptime = '';
                    if ($diff->d > 0) $uptime .= $diff->d . ' days, ';
                    $uptime .= $diff->h . ' hours, ' . $diff->i . ' minutes';
                }
            }
        }
        
        return $uptime;
    }
}
