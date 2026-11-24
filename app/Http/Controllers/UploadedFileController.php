<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\UploadedFile;
use App\Services\SourceExtractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadedFileController extends Controller
{
    public function __construct(
        protected SourceExtractionService $extractionService
    ) {}

    /**
     * Store and process an uploaded document or image.
     */
    public function store(Request $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:15360', // 15MB limit
                'mimes:pdf,docx,txt,jpg,jpeg,png,webp',
            ],
        ], [
            'file.required' => 'Please select a document or image to upload.',
            'file.max' => 'The file size must not exceed 15MB.',
            'file.mimes' => 'Only PDF, DOCX, TXT, JPG, JPEG, PNG, and WebP files are supported.',
        ]);

        $uploaded = $request->file('file');
        $originalName = $uploaded->getClientOriginalName();
        $mimeType = $uploaded->getMimeType();
        $fileSize = $uploaded->getSize();
        $extension = strtolower($uploaded->getClientOriginalExtension());

        // Normalize file_type category
        $fileType = match ($extension) {
            'pdf' => 'pdf',
            'docx' => 'docx',
            'txt' => 'txt',
            'jpg', 'jpeg', 'png', 'webp' => 'image',
            default => 'other',
        };

        // Secure storage path segregated by business ID
        $storageDir = "documents/{$business->id}";
        $safeFilename = Str::uuid() . '.' . $extension;
        $filePath = $uploaded->storeAs($storageDir, $safeFilename, 'public');

        $fileRecord = UploadedFile::create([
            'business_id' => $business->id,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'processing_status' => 'pending',
        ]);

        $result = $this->extractionService->processFile($fileRecord);

        if ($result['status'] === 'scanned_ocr_required') {
            return redirect()->route('businesses.sources.index', $business)
                ->with('warning', 'Document uploaded, but it appears to be a scanned image with no selectable text. OCR is not available; key facts can be entered manually.');
        }

        if (!$result['success'] && $result['status'] === 'failed') {
            return redirect()->route('businesses.sources.index', $business)
                ->with('error', 'Document uploaded, but text extraction failed: ' . ($result['error'] ?? 'Parsing error'));
        }

        return redirect()->route('businesses.sources.index', $business)
            ->with('success', "'{$originalName}' uploaded and processed successfully!");
    }

    /**
     * Retry processing an uploaded file.
     */
    public function retry(UploadedFile $file): RedirectResponse
    {
        Gate::authorize('delete', $file);

        $result = $this->extractionService->processFile($file);

        if (!$result['success'] && $result['status'] === 'failed') {
            return back()->with('error', 'Reprocessing failed: ' . ($result['error'] ?? 'Parsing error'));
        }

        return back()->with('success', 'Document reprocessed successfully.');
    }

    /**
     * Remove the specified file from storage and database.
     */
    public function destroy(UploadedFile $file): RedirectResponse
    {
        Gate::authorize('delete', $file);

        $business = $file->business;

        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $filename = $file->original_name;
        $file->delete();

        return redirect()->route('businesses.sources.index', $business)
            ->with('success', "File '{$filename}' was deleted successfully.");
    }
}
