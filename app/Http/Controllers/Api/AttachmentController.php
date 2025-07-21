<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    /**
     * Store a newly created attachment in storage.
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $file = $request->file('file');
        
        // Generate a unique filename
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $uniqueFilename = $basename . '_' . Str::random(8) . '.' . $extension;
        
        // Store the file
        $filepath = $file->storeAs('attachments', $uniqueFilename, 'public');
        
        // Create the attachment record
        $attachment = Attachment::create([
            'ticket_id' => $request->input('ticket_id'),
            'comment_id' => $request->input('comment_id'),
            'user_id' => auth()->id(),
            'filename' => $filename,
            'filepath' => $filepath,
            'filetype' => $file->getMimeType(),
            'filesize' => $file->getSize(),
            'metadata' => $request->input('metadata', []),
        ]);

        return response()->json(new AttachmentResource($attachment), 201);
    }

    /**
     * Download the specified attachment.
     */
    public function download(Attachment $attachment): Response
    {
        if (!Storage::disk('public')->exists($attachment->filepath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download(
            $attachment->filepath,
            $attachment->filename
        );
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        // Delete the physical file
        if (Storage::disk('public')->exists($attachment->filepath)) {
            Storage::disk('public')->delete($attachment->filepath);
        }

        // Delete the database record
        $attachment->delete();

        return response()->json(null, 204);
    }
}
