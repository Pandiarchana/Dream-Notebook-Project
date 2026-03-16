import sys
import json
from textblob import TextBlob

text = sys.argv[1]

blob = TextBlob(text)

sentences = blob.sentences

results = []

total_polarity = 0
trend = []

risk_keywords = [
    "die", "suicide", "kill myself", "end my life",
    "hopeless", "worthless", "no reason to live"
]

risk_level = "Low"

lower_text = text.lower()
for word in risk_keywords:
    if word in lower_text:
        risk_level = "High"

for sentence in sentences:

    polarity = sentence.sentiment.polarity

    if polarity > 0.3:
        emotion = "Positive 😊"
        trend.append("Positive")
    elif polarity < -0.3:
        emotion = "Negative 😔"
        trend.append("Negative")
    else:
        emotion = "Neutral 😐"
        trend.append("Neutral")

    results.append({
        "sentence": str(sentence),
        "polarity": round(polarity,3),
        "emotion": emotion
    })

    total_polarity += polarity

if len(sentences) > 0:
    avg_polarity = total_polarity / len(sentences)
else:
    avg_polarity = 0

if avg_polarity > 0.3:
    overall_emotion = "Positive 😊"
elif avg_polarity < -0.3:
    overall_emotion = "Negative 😔"
else:
    overall_emotion = "Neutral 😐"

emotion_trend = " → ".join(trend)

output = {
    "overall_emotion": overall_emotion,
    "average_polarity": round(avg_polarity,3),
    "risk_level": risk_level,
    "emotion_trend": emotion_trend,
    "sentence_analysis": results
}

print(json.dumps(output))