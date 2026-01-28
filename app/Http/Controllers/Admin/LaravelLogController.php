<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LaravelLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth('admin')->user() && auth('admin')->user()->can('view-admin-logs'), 403);

        $logsDir = storage_path('logs');

        $files = [];
        if (is_dir($logsDir)) {
            $paths = File::glob($logsDir . DIRECTORY_SEPARATOR . '*.log') ?: [];
            foreach ($paths as $path) {
                $files[] = [
                    'name' => basename($path),
                    'path' => $path,
                    'mtime' => @filemtime($path) ?: 0,
                    'size' => @filesize($path) ?: 0,
                ];
            }
        }

        usort($files, function ($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });

        $selected = (string) $request->query('file', '');
        $lines = (int) $request->query('lines', 500);
        if ($lines < 10) {
            $lines = 10;
        }
        if ($lines > 5000) {
            $lines = 5000;
        }

        $search = (string) $request->query('q', '');

        $content = null;
        $error = null;

        if ($selected !== '') {
            $selectedName = basename($selected);
            $selectedPath = $logsDir . DIRECTORY_SEPARATOR . $selectedName;

            $realLogs = realpath($logsDir);
            $realSelected = realpath($selectedPath);

            if (!$realLogs || !$realSelected || strncmp($realSelected, $realLogs, strlen($realLogs)) !== 0 || !is_file($realSelected)) {
                $error = 'A kiválasztott log fájl nem elérhető.';
            } else {
                $contentLines = $this->tailFile($realSelected, $lines);

                if ($search !== '') {
                    $needle = mb_strtolower($search);
                    $contentLines = array_values(array_filter($contentLines, function ($line) use ($needle) {
                        return mb_strpos(mb_strtolower($line), $needle) !== false;
                    }));
                }

                $content = implode("", $contentLines);
            }
        }

        return view('admin.statistics.laravel_logs', [
            'files' => $files,
            'selected' => $selected,
            'lines' => $lines,
            'q' => $search,
            'content' => $content,
            'error' => $error,
        ]);
    }

    private function tailFile(string $path, int $lines): array
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $buffer = '';
        $chunkSize = 8192;
        $pos = -1;
        $lineCount = 0;

        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);

        while ($fileSize + $pos >= 0 && $lineCount <= $lines) {
            $seek = max($fileSize + $pos - $chunkSize, 0);
            $read = ($fileSize + $pos) - $seek;

            fseek($handle, $seek, SEEK_SET);
            $chunk = fread($handle, $read);

            if ($chunk === false) {
                break;
            }

            $buffer = $chunk . $buffer;
            $lineCount = substr_count($buffer, "\n");

            $pos -= $chunkSize;
            if ($seek === 0) {
                break;
            }
        }

        fclose($handle);

        $allLines = preg_split("/\r\n|\n|\r/", $buffer);
        if ($allLines === false) {
            return [];
        }

        $slice = array_slice($allLines, -$lines);
        return array_map(function ($l) {
            return $l . "\n";
        }, $slice);
    }
}
