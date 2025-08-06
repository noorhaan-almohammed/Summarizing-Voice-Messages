@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center mb-5"> تفريغ وتلخيص النصوص</h1>

    <div class="row g-4">
        {{-- تفريغ الصوت --}}
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h2 class="mb-3">🎙 تفريغ صوت</h2>
                <form id="transcribe-form" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="audio" class="form-label">اختر ملف صوتي (mp3, wav, m4a):</label>
                        <input type="file" name="audio" id="audio" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">ابدأ التفريغ</button>
                </form>
                <div id="transcribe-result" class="result mt-3"></div>
            </div>
        </div>

        {{-- تلخيص نص --}}
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h2 class="mb-3">📝 تلخيص نص</h2>
                <form id="summarize-form">
                    <div class="mb-3">
                        <label for="text" class="form-label">النص المراد تلخيصه:</label>
                        <textarea name="text" id="text" class="form-control" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">تلخيص</button>
                </form>
                <div id="summarize-result" class="result mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // تفريغ صوت
        document.getElementById("transcribe-form").addEventListener("submit", async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            document.getElementById("transcribe-result").innerText = "جاري المعالجة...";

            const res = await fetch("/api/transcribe", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const data = await res.json();
            document.getElementById("transcribe-result").innerText = data.transcript || data.message || "حدث خطأ أثناء التفريغ.";
        });

        // تلخيص نص
        document.getElementById("summarize-form").addEventListener("submit", async function (e) {
            e.preventDefault();
            const text = document.getElementById("text").value;
            document.getElementById("summarize-result").innerText = "جاري التلخيص...";

            const res = await fetch("/summarize-text", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ text })
            });

            const data = await res.json();
            document.getElementById("summarize-result").innerText = data.summary || data.error || "حدث خطأ أثناء التلخيص.";
        });
    });
</script>
@endsection
