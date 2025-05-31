import sys
import json
from google.cloud import speech
import google.generativeai as genai

# ⛳️ إعداد مفتاح API لـ Google Gemini
GENAI_API_KEY = "AIzaSyAOdAABN6P26Nbhk4eH9ztVKofta6mdZss"
genai.configure(api_key=GENAI_API_KEY)

# ✅ تحسين النص العربي باستخدام Gemini
def refine_text_with_gemini(raw_text):
    prompt = f"""
    هذا النص تم تحويله من صوت إلى نص باستخدام الذكاء الاصطناعي. رجاءً حسّنه ليكون واضحًا وسليمًا لغويًا، دون تغيير المعنى:
    ---
    {raw_text}
    """
    try:
        model = genai.GenerativeModel("gemini-pro")
        response = model.generate_content(prompt)
        return response.text.strip()
    except Exception as e:
        return f"❌ خطأ أثناء تحسين النص: {e}"

# 🎙️ تحويل صوت إلى نص باستخدام Google Cloud Speech-to-Text
def transcribe_audio_google(audio_path):
    client = speech.SpeechClient()

    with open(audio_path, "rb") as audio_file:
        content = audio_file.read()

    audio = speech.RecognitionAudio(content=content)

    config = speech.RecognitionConfig(
        encoding=speech.RecognitionConfig.AudioEncoding.LINEAR16,  # تأكد من صيغة ملف الصوت لديك
        sample_rate_hertz=16000,  # يجب أن تتطابق مع ملف الصوت
        language_code="ar-SA",
    )

    response = client.recognize(config=config, audio=audio)

    transcript = ""
    for result in response.results:
        transcript += result.alternatives[0].transcript

    return transcript

# 🧩 الوظيفة الرئيسية
def main(audio_path):
    print("🔁 جارٍ تحويل الصوت إلى نص...")
    raw_text = transcribe_audio_google(audio_path)

    print("✨ جارٍ تحسين النص باستخدام Gemini...")
    refined_text = refine_text_with_gemini(raw_text)

    result = {
        "original_text": raw_text,
        "refined_text": refined_text
    }

    print(json.dumps(result, ensure_ascii=False, indent=2))

# 🚀 نقطة التشغيل
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("❌ يجب تمرير مسار الملف الصوتي كـ argument.")
        sys.exit(1)

    audio_file = sys.argv[1]
    main(audio_file)
