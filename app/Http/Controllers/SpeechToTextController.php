<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SpeechToTextController extends Controller
{
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,webm,m4a,ogg',
        ]);

        // حفظ الملف الصوتي في مجلد storage
        $filename = uniqid() . '.' . $request->file('audio')->getClientOriginalExtension();
        $relativePath = $request->file('audio')->storeAs('/audio_inputs', $filename);
        $absolutePath = storage_path('app/' . $relativePath);

        // تشغيل سكربت بايثون لتحويل الصوت إلى نص وتلخيصه
        $python = 'python'; // أو 'py' حسب بيئتك، تأكد أنه يشير إلى Python 3.11
        $scriptPath = base_path('python/voice_processor.py');

        $process = new Process([$python, $scriptPath, $absolutePath]);
        $process->setTimeout(300); // وقت كافي لأن Whisper + mT5 قد يستغرقان وقتًا
        $process->run();

        // التحقق من نجاح العملية
        if (!$process->isSuccessful()) {
            logger()->error('Python Error: ' . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        // الحصول على النتيجة من JSON المُرجع من سكربت بايثون
        $result = json_decode($process->getOutput(), true);

        return response()->json([
            'text' => $result['text'] ?? null,
            // 'summary' => $result['summary'] ?? null,
        ]);
    }

    public function transcribeAudio(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:flac,wav,mp3'
        ]);

        $audioPath = $request->file('audio')->store('audios');

        $audio = (new RecognitionAudio())
            ->setContent(file_get_contents(storage_path('app/' . $audioPath)));

        $config = (new RecognitionConfig())
            ->setEncoding(RecognitionConfig\AudioEncoding::LINEAR16)
            ->setSampleRateHertz(16000)
            ->setLanguageCode('en-US');

        $speech = new SpeechClient([
            'keyFilePath' => storage_path('app/google-credentials.json'),
        ]);

        $response = $speech->recognize($config, $audio);
        $text = '';
        foreach ($response->getResults() as $result) {
            $text .= $result->getAlternatives()[0]->getTranscript();
        }

        $speech->close();

        return response()->json(['text' => $text]);
    }
}
