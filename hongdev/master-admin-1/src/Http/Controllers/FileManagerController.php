<?php

namespace Hongdev\MasterAdmin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{
    private $allowedDisks = ['public', 'storage', 'local'];
    
    /**
     * Show file manager index
     */
    public function index(Request $request)
    {
        $disk = $request->get('disk', 'public');
        $path = $request->get('path', '');
        
        if (!in_array($disk, $this->allowedDisks)) {
            $disk = 'public';
        }
        
        try {
            $basePath = $this->getBasePath($disk);
            $fullPath = $basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
            
            // Kiểm tra path có tồn tại không
            if (!File::exists($fullPath)) {
                $path = '';
                $fullPath = $basePath;
            }
            
            $items = [];
            $scanDir = File::glob($fullPath . DIRECTORY_SEPARATOR . '*');
            
            foreach ($scanDir as $item) {
                $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $item);
                $isDirectory = File::isDirectory($item);
                
                $items[] = [
                    'name' => basename($item),
                    'path' => $relativePath,
                    'type' => $isDirectory ? 'directory' : 'file',
                    'size' => $isDirectory ? null : File::size($item),
                    'modified' => File::lastModified($item),
                    'url' => $this->getFileUrl($disk, $relativePath),
                    'extension' => $isDirectory ? null : pathinfo($item, PATHINFO_EXTENSION)
                ];
            }
            
            // Sort: directories first, then files
            usort($items, function($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'directory' ? -1 : 1;
                }
                return strcasecmp($a['name'], $b['name']);
            });
            
            $breadcrumbs = $this->buildBreadcrumbs($path);
            
            return view('master-admin::master-admin.page.file-manager.index', [
                'items' => $items,
                'currentPath' => $path,
                'disk' => $disk,
                'breadcrumbs' => $breadcrumbs,
                'diskInfo' => $this->getDiskInfo($disk)
            ]);
            
        } catch (\Exception $e) {
            return redirect()->route('master-admin.file-manager.index')
                           ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Create directory
     */
    public function createDirectory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\-\s]+$/',
            'path' => 'nullable|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $currentPath = $basePath . ($request->path ? DIRECTORY_SEPARATOR . $request->path : '');
            $newDirPath = $currentPath . DIRECTORY_SEPARATOR . $request->name;
            
            if (File::exists($newDirPath)) {
                return back()->with('error', 'Directory already exists');
            }
            
            File::makeDirectory($newDirPath, 0755, true);
            
            return back()->with('success', 'Directory created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Upload files
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:20480', // 20MB
            'path' => 'nullable|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $uploadPath = $basePath . ($request->path ? DIRECTORY_SEPARATOR . $request->path : '');
            $uploaded = 0;
            
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $destinationPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                
                // Tránh ghi đè file
                $counter = 1;
                while (File::exists($destinationPath)) {
                    $info = pathinfo($filename);
                    $newName = $info['filename'] . '_' . $counter . '.' . $info['extension'];
                    $destinationPath = $uploadPath . DIRECTORY_SEPARATOR . $newName;
                    $counter++;
                }
                
                $file->move($uploadPath, basename($destinationPath));
                $uploaded++;
            }
            
            return back()->with('success', "Uploaded {$uploaded} file(s)");
        } catch (\Exception $e) {
            return back()->with('error', 'Upload error: ' . $e->getMessage());
        }
    }

    /**
     * Delete file or directory
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $request->path;
            
            if (!File::exists($fullPath)) {
                return back()->with('error', 'Item not found');
            }
            
            if (File::isDirectory($fullPath)) {
                File::deleteDirectory($fullPath);
                $message = 'Directory deleted';
            } else {
                File::delete($fullPath);
                $message = 'File deleted';
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Delete error: ' . $e->getMessage());
        }
    }

    /**
     * Download file
     */
    public function download(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $request->path;
            
            if (!File::exists($fullPath) || File::isDirectory($fullPath)) {
                return back()->with('error', 'File not found');
            }
            
            return response()->download($fullPath);
        } catch (\Exception $e) {
            return back()->with('error', 'Download error: ' . $e->getMessage());
        }
    }

    /**
     * View file content
     */
    public function view(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $request->path;
            
            if (!File::exists($fullPath) || File::isDirectory($fullPath)) {
                return back()->with('error', 'File not found');
            }
            
            $content = null;
            $fileSize = File::size($fullPath);
            $isTextFile = $this->isTextFile($request->path);
            
            // Chỉ đọc file text nhỏ hơn 1MB
            if ($isTextFile && $fileSize < 1048576) {
                $content = File::get($fullPath);
            }
            
            $fileInfo = [
                'name' => basename($request->path),
                'size' => $fileSize,
                'modified' => File::lastModified($fullPath),
                'mimeType' => File::mimeType($fullPath),
                'extension' => pathinfo($request->path, PATHINFO_EXTENSION)
            ];
            
            return view('master-admin::master-admin.page.file-manager.view', [
                'fileInfo' => $fileInfo,
                'content' => $content,
                'filePath' => $request->path,
                'disk' => $request->disk,
                'isTextFile' => $isTextFile
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'View error: ' . $e->getMessage());
        }
    }

    /**
     * Edit file content
     */
    public function edit(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $this->showEditor($request);
        }
        
        return $this->updateFile($request);
    }

    private function showEditor(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'required|in:public,storage,local'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $request->path;
            
            if (!File::exists($fullPath) || !$this->isTextFile($request->path)) {
                return back()->with('error', 'File cannot be edited');
            }
            
            $content = File::get($fullPath);
            
            return view('master-admin::master-admin.page.file-manager.edit', [
                'content' => $content,
                'filePath' => $request->path,
                'disk' => $request->disk,
                'fileName' => basename($request->path)
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Edit error: ' . $e->getMessage());
        }
    }

    private function updateFile(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'required|in:public,storage,local',
            'content' => 'required|string'
        ]);

        try {
            $basePath = $this->getBasePath($request->disk);
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $request->path;
            
            File::put($fullPath, $request->content);
            
            return back()->with('success', 'File updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Update error: ' . $e->getMessage());
        }
    }

    /**
     * Get base path for disk
     */
    private function getBasePath($disk)
    {
        switch ($disk) {
            case 'public':
                return public_path();
            case 'storage':
                return storage_path('app/public');
            case 'local':
                return base_path();
            default:
                return public_path();
        }
    }

    /**
     * Get file URL if accessible
     */
    private function getFileUrl($disk, $path)
    {
        switch ($disk) {
            case 'public':
                return url('/' . $path);
            case 'storage':
                return url('/storage/' . $path);
            default:
                return null;
        }
    }

    /**
     * Build breadcrumb navigation
     */
    private function buildBreadcrumbs($path)
    {
        $breadcrumbs = [['name' => 'Root', 'path' => '']];
        
        if ($path) {
            $parts = explode('/', $path);
            $currentPath = '';
            
            foreach ($parts as $part) {
                $currentPath .= ($currentPath ? '/' : '') . $part;
                $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
            }
        }
        
        return $breadcrumbs;
    }

    /**
     * Get disk information
     */
    private function getDiskInfo($disk)
    {
        $info = [];

        try {
            switch ($disk) {
                case 'public':
                    $info = [
                        'name' => 'Public',
                        'path' => public_path(),
                        'url' => url('/'),
                        'description' => 'Public folder - Web accessible files (CSS, JS, images)'
                    ];
                    break;
                case 'storage':
                    $info = [
                        'name' => 'Storage',
                        'path' => storage_path('app/public'),
                        'url' => url('/storage'),
                        'description' => 'Storage/app/public - Laravel storage disk (linked to public/storage)'
                    ];
                    break;
                case 'local':
                    $info = [
                        'name' => 'Local',
                        'path' => base_path(),
                        'url' => null,
                        'description' => 'Project root directory - Private files not accessible from web'
                    ];
                    break;
            }
        } catch (\Exception $e) {
            $info['error'] = $e->getMessage();
        }
        
        return $info;
    }

    /**
     * Check if file is editable text file
     */
    private function isTextFile($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $textExtensions = [
            'txt', 'log', 'md', 'php', 'js', 'css', 'html', 'htm', 'xml', 
            'json', 'yml', 'yaml', 'env', 'ini', 'conf', 'sql', 'py', 'rb', 'go'
        ];
        
        return in_array($extension, $textExtensions);
    }
}
