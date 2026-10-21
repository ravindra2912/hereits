<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class LogController extends Controller
{
    /**
     * Display log entries in tabular format.
     */
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');

        $availableFiles = array_map(function ($file) {
            return basename($file);
        }, $files);

        // Sort files descending (newest log files first)
        rsort($availableFiles);

        $selectedFile = $request->get('file', $availableFiles[0] ?? 'laravel.log');
        $currentFilePath = $logPath . '/' . $selectedFile;

        $logs = [];
        $fileSize = 0;

        if (File::exists($currentFilePath)) {
            $fileSize = File::size($currentFilePath);
            $logs = $this->parseLogFile($currentFilePath, $request->get('level'), $request->get('search'));
        }

        // Paginate results manually
        $perPage = 25;
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginatedLogs = new LengthAwarePaginator(
            array_slice($logs, $offset, $perPage),
            count($logs),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $levels = ['ERROR', 'WARNING', 'INFO', 'DEBUG', 'CRITICAL', 'NOTICE', 'ALERT', 'EMERGENCY'];

        return view('admin.logs.index', compact(
            'paginatedLogs',
            'availableFiles',
            'selectedFile',
            'fileSize',
            'levels'
        ));
    }

    /**
     * Download the selected log file.
     */
    public function download(Request $request)
    {
        $fileName = $request->get('file', 'laravel.log');
        $filePath = storage_path('logs/' . basename($fileName));

        if (!File::exists($filePath)) {
            return back()->with('error', 'Log file not found.');
        }

        return Response::download($filePath);
    }

    /**
     * Clear / truncate the selected log file.
     */
    public function clear(Request $request)
    {
        $fileName = $request->get('file', 'laravel.log');
        $filePath = storage_path('logs/' . basename($fileName));

        if (File::exists($filePath)) {
            File::put($filePath, '');
            return back()->with('success', "Log file {$fileName} cleared successfully!");
        }

        return back()->with('error', 'Log file not found.');
    }

    /**
     * Parse log file safely without memory overflow.
     */
    private function parseLogFile(string $filePath, ?string $levelFilter = null, ?string $search = null): array
    {
        // Read maximum last 4MB of log file to protect memory
        $maxBytes = 4 * 1024 * 1024;
        $fileSize = filesize($filePath);

        if ($fileSize === 0) {
            return [];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        if ($fileSize > $maxBytes) {
            fseek($handle, -$maxBytes, SEEK_END);
            // Drop first line as it might be incomplete block
            fgets($handle);
        }

        $content = stream_get_contents($handle);
        fclose($handle);

        if (empty($content)) {
            return [];
        }

        // Pattern matching Laravel log header
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s+([a-zA-Z0-9_-]+)\.([A-Z]+):\s+(.*)$/m';

        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return [];
        }

        $parsed = [];
        $count = count($matches[0]);

        for ($i = 0; $i < $count; $i++) {
            $date = $matches[1][$i][0];
            $env = $matches[2][$i][0];
            $level = strtoupper($matches[3][$i][0]);
            $message = $matches[4][$i][0];

            $startPos = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $endPos = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($content);
            $stackTrace = trim(substr($content, $startPos, $endPos - $startPos));

            // Apply Level Filter
            if ($levelFilter && strtoupper($levelFilter) !== 'ALL' && $level !== strtoupper($levelFilter)) {
                continue;
            }

            // Apply Search Filter
            if ($search) {
                $searchLower = strtolower($search);
                if (!str_contains(strtolower($message), $searchLower) && !str_contains(strtolower($stackTrace), $searchLower)) {
                    continue;
                }
            }

            $parsed[] = [
                'id' => $i + 1,
                'date' => $date,
                'env' => $env,
                'level' => $level,
                'badge_class' => $this->getLevelBadgeClass($level),
                'message' => $message,
                'stack' => $stackTrace,
            ];
        }

        // Return latest logs first
        return array_reverse($parsed);
    }

    /**
     * Map log level to Bootstrap badge classes.
     */
    private function getLevelBadgeClass(string $level): string
    {
        return match ($level) {
            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'bg-danger text-white',
            'WARNING' => 'bg-warning text-dark',
            'NOTICE', 'INFO' => 'bg-info text-dark',
            'DEBUG' => 'bg-secondary text-white',
            default => 'bg-dark text-white',
        };
    }
}
