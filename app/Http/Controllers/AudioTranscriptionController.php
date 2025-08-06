<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AudioTranscriptionController extends Controller
{
    private $apiKey = '0139707f15b247db99b64d843663cc59';

    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,m4a',
        ]);

        // 1. ارفع الملف إلى AssemblyAI
        $filePath = $request->file('audio')->store('audio');
        $audioData = Storage::get($filePath);

        $uploadRes = Http::withHeaders([
            'authorization' => $this->apiKey,
            'content-type' => 'application/octet-stream',
        ])->withBody($audioData, 'application/octet-stream')
          ->post('https://api.assemblyai.com/v2/upload');

        if (!$uploadRes->successful()) {
            return response()->json(['error' => 'Upload failed'], 500);
        }

        $audioUrl = $uploadRes->json()['upload_url'];

        // 2. أنشئ طلب التفريغ
        $transcriptionRes = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->post('https://api.assemblyai.com/v2/transcript', [
            'audio_url' => $audioUrl,
            'language_code' => 'ar', // اللغة العربية
        ]);

        $transcriptId = $transcriptionRes->json()['id'];

        // 3. تحقق من حالة التفريغ
        sleep(10); // انتظر قليلاً قبل الطلب

        $pollRes = Http::withHeaders([
            'authorization' => $this->apiKey,
        ])->get("https://api.assemblyai.com/v2/transcript/{$transcriptId}");

        $status = $pollRes->json()['status'];
        if ($status !== 'completed') {
            return response()->json(['status' => $status, 'message' => 'Still processing...']);
        }

        return response()->json([
            'transcript' => $pollRes->json()['text']
        ]);
    }
}
