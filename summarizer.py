from flask import Flask, request, jsonify
from transformers import AutoTokenizer, AutoModelForSeq2SeqLM, pipeline

app = Flask(__name__)

model_name = "csebuetnlp/mT5_multilingual_XLSum"
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForSeq2SeqLM.from_pretrained(model_name)

summarizer = pipeline("summarization", model=model, tokenizer=tokenizer)

@app.route("/summarize", methods=["POST"])
def summarize():
    data = request.get_json()
    text = data.get("text", "").strip()

    if not text or len(text) < 30:
        return jsonify({"error": "النص قصير أو غير موجود"}), 400

    try:
        summary = summarizer(
            text,
            max_length=1200,
            min_length=30,
            do_sample=False
        )
        return jsonify({"summary": summary[0]["summary_text"]})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001)
###########################################################################################################
# from flask import Flask, request, jsonify
# from transformers import AutoTokenizer, AutoModelForSeq2SeqLM
# import torch

# app = Flask(__name__)

# model_name = "knkarthick/MEETING_SUMMARY"
# tokenizer = AutoTokenizer.from_pretrained(model_name)
# model = AutoModelForSeq2SeqLM.from_pretrained(model_name)

# @app.route("/summarize", methods=["POST"])
# def summarize():
#     data = request.get_json()
#     text = data.get("text", "").strip()

#     if not text or len(text) < 30:
#         return jsonify({"error": "النص قصير أو غير موجود"}), 400

#     try:
#         inputs = tokenizer(
#             text,
#             return_tensors="pt",
#             max_length=1024,
#             truncation=True
#         )

#         summary_ids = model.generate(
#             inputs["input_ids"],
#             attention_mask=inputs["attention_mask"],
#             max_length=256,
#             min_length=30,
#             do_sample=False,
#             num_beams=4,
#         )

#         summary_text = tokenizer.decode(summary_ids[0], skip_special_tokens=True)

#         return jsonify({"summary": summary_text})

#     except Exception as e:
#         return jsonify({"error": str(e)}), 500

# if __name__ == "__main__":
#     app.run(host="0.0.0.0", port=5001)
