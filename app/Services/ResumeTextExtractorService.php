<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;

class ResumeTextExtractorService
{
    /**
     * Extract text from a given file path.
     * Currently supports PDF via pdftotext.
     *
     * @param  string  $filePath  The relative path in the storage disk.
     * @param  string  $disk  The storage disk being used (default: current default disk).
     * @return string|null The extracted text, or null if unsupported/failed.
     */
    public function extractText(string $filePath, string $disk = 'public'): ?string
    {
        if (! Storage::disk($disk)->exists($filePath)) {
            Log::warning("Resume parsing failed: File not found at {$filePath}");

            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $absolutePath = Storage::disk($disk)->path($filePath);

        try {
            if ($extension === 'pdf') {
                return $this->extractFromPdf($absolutePath);
            } elseif ($extension === 'txt') {
                return Storage::disk($disk)->get($filePath);
            } else {
                Log::notice("Resume parsing skipped: Unsupported file extension .{$extension} for {$filePath}");

                return null;
            }
        } catch (Exception $e) {
            Log::error("Resume parsing error for {$filePath}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Extract text from a PDF file using spatie/pdf-to-text.
     */
    protected function extractFromPdf(string $absolutePath): string
    {
        // Path to pdftotext executable.
        $binPath = config('services.pdftotext.path', '/opt/homebrew/bin/pdftotext');

        if (file_exists($binPath) || file_exists('/usr/bin/pdftotext')) {
            $actualBin = file_exists($binPath) ? $binPath : '/usr/bin/pdftotext';
            try {
                return (new Pdf($actualBin))
                    ->setPdf($absolutePath)
                    ->text();
            } catch (Exception $e) {
                Log::warning('spatie/pdf-to-text failed, falling back to smalot/pdfparser: '.$e->getMessage());
            }
        }

        // Fallback to pure PHP parser if bin missing or failed
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($absolutePath);

            return $pdf->getText();
        } catch (Exception $e) {
            Log::error("Both PDF extraction methods failed for {$absolutePath}: ".$e->getMessage());

            throw $e;
        }
    }
}
