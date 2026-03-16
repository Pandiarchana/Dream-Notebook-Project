import sys
import mysql.connector
import google.generativeai as genai

# 1. Setup Gemini (Use your key from Google AI Studio)
genai.configure(api_key="AIzaSyC6IM-OVI5UVngHYcxDxjje1IS2OARl6kE")
model = genai.GenerativeModel('gemini-1.5-flash')


def run_analysis():
    # Get the dream text passed from PHP
    if len(sys.argv) < 3:
        return

    dream_id = sys.argv[1]
    dream_text = sys.argv[2]

    try:
        # 2. Ask Gemini for the emotion
        prompt = f"Analyze the emotion of this dream in one word (e.g., Happy, Sad, Anxious, Fearful): {dream_text}"
        response = model.generate_content(prompt)
        emotion = response.text.strip()

        # 3. Connect to your MySQL Workbench
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="Archana@27",
            database="dream_notebook"
        )
        cursor = db.cursor()

        # 4. Insert the emotion into your 'categories' table
        query = "INSERT INTO categories (entry_id, category_name) VALUES (%s, %s)"
        cursor.execute(query, (dream_id, emotion))

        db.commit()
        print(f"Success: Detected {emotion}")

    except Exception as e:
        print(f"Error: {str(e)}")


if __name__ == "__main__":
    run_analysis()