<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class PdfProcessingService
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text and metadata from a PDF file.
     * Handles password-protected, corrupted, and scanned documents gracefully.
     */
    public function extractText(string $diskRelativePath): array
    {
        $fullPath = Storage::disk('public')->path($diskRelativePath);

        if (!file_exists($fullPath)) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'The uploaded PDF file could not be found on the storage disk.',
                'text' => null,
                'page_count' => 0,
                'is_scanned' => false,
            ];
        }

        try {
            $pdf = $this->parser->parseFile($fullPath);
            $pages = $pdf->getPages();
            $pageCount = count($pages);
            $rawText = $pdf->getText();

            // Clean invisible characters, null bytes and normalize whitespace
            $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $rawText);
            $cleanText = preg_replace('/\s+/', ' ', trim($cleanText));

            // Scanned PDF detection: if pageCount > 0 but total text length is negligible
            $charCount = mb_strlen($cleanText);
            $averageCharsPerPage = $pageCount > 0 ? ($charCount / $pageCount) : 0;

            if ($charCount < 50 || $averageCharsPerPage < 30) {
                return [
                    'success' => true,
                    'status' => 'scanned_ocr_required',
                    'error' => 'This PDF appears to be a scanned image or photo containing little to no embedded selectable text. Please review extracted facts or enter key details manually.',
                    'text' => $cleanText ?: 'No selectable text found (Scanned Image PDF).',
                    'page_count' => $pageCount,
                    'is_scanned' => true,
                ];
            }

            return [
                'success' => true,
                'status' => 'completed',
                'error' => null,
                'text' => $cleanText,
                'page_count' => $pageCount,
                'is_scanned' => false,
            ];

        } catch (Exception $e) {
            Log::warning("PDF Extraction failed for file {$diskRelativePath}: " . $e->getMessage());

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Unable to parse PDF: ' . $e->getMessage() . '. The file may be password protected or corrupted.',
                'text' => null,
                'page_count' => 0,
                'is_scanned' => false,
            ];
        }
    }
}
