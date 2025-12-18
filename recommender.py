import warnings
warnings.filterwarnings("ignore")

import sys
import json
import pandas as pd
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import spacy


db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'job-profile', 
}

try:
    nlp = spacy.load("en_core_web_sm")
except:
    print(json.dumps({"error": "Model spacy belum didownload"}))
    sys.exit()

def preprocess(text):
    text = str(text).lower()
    doc = nlp(text)
    tokens = [token.lemma_ for token in doc if not token.is_stop and not token.is_punct]
    return " ".join(tokens)

def get_recommendations(user_gap_text):
    try:
        conn = mysql.connector.connect(**db_config)
        query = "SELECT id, title, description FROM trainings"
        df = pd.read_sql(query, conn)
        conn.close()

        if df.empty:
            return []

        df['content'] = df['title'] + " " + df['description']
        df['clean_content'] = df['content'].apply(preprocess)

        user_gap_clean = preprocess(user_gap_text)

        all_docs = [user_gap_clean] + df['clean_content'].tolist()

        tfidf = TfidfVectorizer()
        tfidf_matrix = tfidf.fit_transform(all_docs)

        cosine_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
        
        sim_scores = list(enumerate(cosine_sim[0]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

        recommendations = []
        for i, score in sim_scores[:3]:
            if score > 0.2: 
                recommendations.append({
                    'id': int(df.iloc[i]['id']),
                    'title': df.iloc[i]['title'],
                    'score': float(score) 
                })

        return recommendations

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) > 1:
        gap_input = sys.argv[1] 
        result = get_recommendations(gap_input)
        print(json.dumps(result)) 
    else:
        print(json.dumps([]))