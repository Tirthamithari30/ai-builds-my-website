<?php

namespace App\Services;

use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;
use Exception;

class DocumentProcessingService
{
    protected PdfParser $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new PdfParser();
    }

    /**
     * Process an uploaded file and extract text where supported.
     */
    public function process(UploadedFile $file): array
    {
        $fullPath = Storage::disk('public')->path($file->file_path);

        if (!file_exists($fullPath)) {
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'The uploaded file could not be found on the storage disk.',
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'The uploaded file could not be found on the storage disk.',
                'text' => null,
            ];
        }

        $fileSize = filesize($fullPath);
        if ($fileSize === 0) {
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'The uploaded file is empty (0 bytes).',
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'The uploaded file is empty (0 bytes).',
                'text' => null,
            ];
        }

        $type = strtolower($file->file_type);

        switch ($type) {
            case 'pdf':
                return $this->extractPdf($file, $fullPath);

            case 'docx':
                return $this->extractDocx($file, $fullPath);

            case 'txt':
                return $this->extractTxt($file, $fullPath);

            case 'image':
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'webp':
                return $this->processImage($file, $fullPath);

            default:
                $file->update([
                    'processing_status' => 'failed',
                    'error_message' => "Unsupported file type: {$type}",
                ]);

                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => "Unsupported file type: {$type}",
                    'text' => null,
                ];
        }
    }

    /**
     * Extract selectable text from PDF documents with scanned PDF detection.
     */
    protected function extractPdf(UploadedFile $file, string $fullPath): array
    {
        try {
            $pdf = $this->pdfParser->parseFile($fullPath);
            $pages = $pdf->getPages();
            $pageCount = count($pages);
            $rawText = $pdf->getText();

            // Clean null bytes and control chars
            $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $rawText);
            $cleanText = preg_replace('/\s+/', ' ', trim($cleanText));

            $charCount = mb_strlen($cleanText);
            $averageCharsPerPage = $pageCount > 0 ? ($charCount / $pageCount) : 0;

            // Scanned image PDF detection
            if ($charCount < 50 || $averageCharsPerPage < 30) {
                $status = 'scanned_ocr_required';
                $errorMessage = 'This PDF appears to be a scanned image containing little or no embedded selectable text. OCR is not configured in this environment; please enter details manually.';
                $text = $cleanText ?: 'No selectable text found in scanned document.';
                $isScanned = true;
            } else {
                $status = 'completed';
                $errorMessage = null;
                $text = $cleanText;
                $isScanned = false;
            }

            $metadata = [
                'page_count' => $pageCount,
                'character_count' => $charCount,
                'is_scanned' => $isScanned,
                'extracted_at' => now()->toIso8601String(),
            ];

            $file->update([
                'extracted_text' => $text,
                'processing_status' => $status,
                'error_message' => $errorMessage,
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'status' => $status,
                'error' => $errorMessage,
                'text' => $text,
                'metadata' => $metadata,
            ];

        } catch (Exception $e) {
            Log::warning("PDF parsing error on {$file->original_name}: " . $e->getMessage());

            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'Failed to parse PDF document: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Failed to parse PDF document: ' . $e->getMessage(),
                'text' => null,
            ];
        }
    }

    /**
     * Extract text from Microsoft Word .docx files using pure PHP ZipArchive.
     */
    protected function extractDocx(UploadedFile $file, string $fullPath): array
    {
        $zip = new ZipArchive();
        $openResult = $zip->open($fullPath);

        if ($openResult !== true) {
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'Unable to open DOCX file as a valid archive.',
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Unable to open DOCX file as a valid archive.',
                'text' => null,
            ];
        }

        $xmlIndex = $zip->locateName('word/document.xml');
        if ($xmlIndex === false) {
            $zip->close();
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'Corrupted DOCX: word/document.xml could not be found.',
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Corrupted DOCX: word/document.xml could not be found.',
                'text' => null,
            ];
        }

        $xmlContent = $zip->getFromIndex($xmlIndex);
        $zip->close();

        if (empty($xmlContent)) {
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'DOCX document body is empty.',
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'DOCX document body is empty.',
                'text' => null,
            ];
        }

        try {
            // Replace paragraph ends with newlines before stripping tags
            $formattedXml = str_replace(['</w:p>', '</w:r>'], ["\n", ' '], $xmlContent);
            $cleanText = strip_tags($formattedXml);
            $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $cleanText = preg_replace('/[^\S\r\n]+/', ' ', $cleanText);
            $cleanText = preg_replace('/\n{3,}/', "\n\n", trim($cleanText));

            $metadata = [
                'character_count' => mb_strlen($cleanText),
                'word_count' => str_word_count($cleanText),
                'extracted_at' => now()->toIso8601String(),
            ];

            $file->update([
                'extracted_text' => $cleanText,
                'processing_status' => 'completed',
                'error_message' => null,
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'status' => 'completed',
                'error' => null,
                'text' => $cleanText,
                'metadata' => $metadata,
            ];

        } catch (Exception $e) {
            Log::warning("DOCX parsing error on {$file->original_name}: " . $e->getMessage());

            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'Failed to parse DOCX contents: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Failed to parse DOCX contents: ' . $e->getMessage(),
                'text' => null,
            ];
        }
    }

    /**
     * Extract text from plain text (.txt) files safely.
     */
    protected function extractTxt(UploadedFile $file, string $fullPath): array
    {
        try {
            $rawContent = file_get_contents($fullPath);
            if ($rawContent === false) {
                throw new Exception('Could not read file stream.');
            }

            // Normalize encoding to UTF-8
            $cleanText = mb_convert_encoding($rawContent, 'UTF-8', mb_detect_encoding($rawContent, 'UTF-8, ISO-8859-1, ASCII, Windows-1252', true) ?: 'UTF-8');
            $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleanText);
            $cleanText = trim($cleanText);

            if (empty($cleanText)) {
                $file->update([
                    'processing_status' => 'failed',
                    'error_message' => 'The uploaded text file is empty.',
                ]);

                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => 'The uploaded text file is empty.',
                    'text' => null,
                ];
            }

            $metadata = [
                'character_count' => mb_strlen($cleanText),
                'word_count' => str_word_count($cleanText),
                'extracted_at' => now()->toIso8601String(),
            ];

            $file->update([
                'extracted_text' => $cleanText,
                'processing_status' => 'completed',
                'error_message' => null,
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'status' => 'completed',
                'error' => null,
                'text' => $cleanText,
                'metadata' => $metadata,
            ];

        } catch (Exception $e) {
            $file->update([
                'processing_status' => 'failed',
                'error_message' => 'Failed to read text file: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Failed to read text file: ' . $e->getMessage(),
                'text' => null,
            ];
        }
    }

    /**
     * Process visual image assets without claiming false OCR.
     */
    protected function processImage(UploadedFile $file, string $fullPath): array
    {
        $dimensions = @getimagesize($fullPath);
        $width = $dimensions ? $dimensions[0] : null;
        $height = $dimensions ? $dimensions[1] : null;

        $metadata = [
            'width' => $width,
            'height' => $height,
            'ocr_available' => false,
            'ocr_note' => 'OCR is not configured in this environment. The image is preserved as a verified visual asset for website galleries and hero imagery.',
            'processed_at' => now()->toIso8601String(),
        ];

        $note = "Image asset stored successfully ({$width}x{$height}px). OCR is not configured; visual asset preserved.";

        $file->update([
            'extracted_text' => $note,
            'processing_status' => 'completed',
            'error_message' => null,
            'metadata' => $metadata,
        ]);

        return [
            'success' => true,
            'status' => 'completed',
            'error' => null,
            'text' => $note,
            'metadata' => $metadata,
        ];
    }
}
