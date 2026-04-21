<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::with('uploader')
            ->latest()
            ->paginate(15);

        $totalSize = Document::sum('file_size');

        return response()->json([
            'documents' => $documents->items(),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page'    => $documents->lastPage(),
                'per_page'     => $documents->perPage(),
                'total'        => $documents->total(),
            ],
            'total_size' => $totalSize,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'required|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx|max:20480',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        $count = Document::count() + 1;
        $code  = 'DOC-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $document = Document::create([
            'code'          => $code,
            'name'          => $request->input('name'),
            'description'   => $request->input('description'),
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'uploaded_by'   => Auth::id(),
        ]);

        return response()->json(['document' => $document->load('uploader')], 201);
    }

    public function destroy(Document $document)
    {
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return response()->noContent();
    }

    public function download(Document $document)
    {
        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }
}
