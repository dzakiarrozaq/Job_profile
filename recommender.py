import warnings
warnings.filterwarnings("ignore")

import sys
import json
import pandas as pd
import mysql.connector
import re
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# === KONFIGURASI DATABASE ===
db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'job-profile-2', 
}

STOPWORDS = {
    'introduction', 'to', 'the', 'and', 'of', 'for', 'in', 'on', 'with', 'at', 'by', 'from',
    'dan', 'dari', 'untuk', 'dengan', 'ke', 'di', 'pada',
    'basic', 'advanced', 'intermediate', 'fundamental', 'essential',
    'dasar', 'lanjutan', 'tingkat', 'menengah', 'pengenalan',
    'training', 'pelatihan', 'course', 'kursus', 'workshop', 'seminar',
    'online', 'offline', 'class', 'kelas', 'program', 'certification'
}

def preprocess(text):
    if not text:
        return ""
    
    text = str(text).lower()
    text = text.replace('&', ' and ')
    text = text.replace('/', ' ')
    text = text.replace('-', ' ')
    text = re.sub(r'[^a-z0-9\s]', ' ', text)
    
    tokens = text.split()
    tokens = [t for t in tokens if t not in STOPWORDS]
    
    return " ".join(tokens)

def get_recommendations(user_gap_text):
    try:
        conn = mysql.connector.connect(**db_config)
        query = "SELECT id, title, competency_name, objective FROM trainings"
        df = pd.read_sql(query, conn)
        conn.close()

        if df.empty:
            return []

        df.fillna('', inplace=True)

        # === PERBAIKAN 1: TEXT BOOSTING (PEMBOBOTAN) ===
        # Kita kalikan/gandakan kolom title (3x) dan competency (2x) 
        # agar TF-IDF menganggap kata di judul jauh lebih penting daripada kata di objective.
        df['content'] = (
            df['title'] + " " + df['title'] + " " + df['title'] + " " + 
            df['competency_name'] + " " + df['competency_name'] + " " + 
            df['objective']
        )
        
        df['clean_content'] = df['content'].apply(preprocess)
        user_gap_clean = preprocess(user_gap_text)

        if not user_gap_clean.strip():
            user_gap_clean = user_gap_text.lower()

        all_docs = [user_gap_clean] + df['clean_content'].tolist()

        # === PERBAIKAN 2: SUBLINEAR TF ===
        # Tambahkan sublinear_tf=True. Ini akan menormalkan frekuensi kata.
        # Sangat bagus untuk mengatasi dokumen (objective) yang terlalu panjang 
        # agar tidak menutupi dokumen pendek.
        tfidf = TfidfVectorizer(ngram_range=(1, 2), min_df=1, sublinear_tf=True)
        
        tfidf_matrix = tfidf.fit_transform(all_docs)
        cosine_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
        
        sim_scores = list(enumerate(cosine_sim[0]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

        recommendations = []
        for i, score in sim_scores[:10]:
            
            # === PERBAIKAN 3: TURUNKAN THRESHOLD ===
            # Turunkan menjadi 0.01 atau 0.005. 
            # Input pendek vs Dokumen panjang secara natural skor Cosine-nya pasti kecil.
            # Selama nilainya > 0.01, berarti ada kecocokan kata yang valid.
            if score > 0.01: 
                recommendations.append({
                    'id': int(df.iloc[i]['id']),
                    'title': df.iloc[i]['title'],
                    'description': df.iloc[i]['objective'], 
                    'score': round(float(score), 4) 
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