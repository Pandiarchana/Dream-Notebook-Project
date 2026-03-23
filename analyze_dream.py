import sys
import os
import mysql.connector
import google.generativeai as genai
from dotenv import load_dotenv

# 1. Load configuration from .env file
load_dotenv()
api_key = os.getenv("GEMINI_API_KEY")
db_pass = os.getenv("DB_PASS")

# 2. Setup Gemini AI
genai.configure(api_key=api_key)
model = genai.GenerativeModel('gemini-1.5-flash')


def run_analysis():
    # Check if PHP sent the dream data (dream_id and dream_text)
    if len(sys.argv) < 3:
        print("Error: Missing arguments from PHP.")
        return

    dream_id = sys.argv[1]
    dream_text = sys.argv[2]

    try:
        # 3. Ask Gemini to identify the emotion
        prompt = f"Analyze this dream and return ONLY a one-word emotion (e.g., Happy, Anxious, Fearful, Calm): {dream_text}"
        response = model.generate_content(prompt)
        emotion = response.text.strip()

        # 4. Connect to MySQL and save the emotion
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password=db_pass,
            database="dream_notebook"
        )

        cursor = db.cursor()

        # Insert into categories table (matches your Workbench structure)
        query = "INSERT INTO categories (entry_id, category_name) VALUES (%s, %s)"
        cursor.execute(query, (dream_id, emotion))

        db.commit()
        cursor.close()
        db.close()

        print(f"Success: Analysis saved as {emotion}")

    except Exception as e:
        print(f"Error occurred: {e}")


if __name__ == "__main__":
    run_analysis()