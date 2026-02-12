import warnings
warnings.filterwarnings("ignore")

import sys
import json
import pandas as pd
import mysql.connector
import re
from sklearn.feature_extraction.text import TfidfVectorizer # KEMBALI KE TF-IDF
from sklearn.metrics.pairwise import cosine_similarity

# === KONFIGURASI DATABASE ===
db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'job-profile', 
}

# === PERBAIKAN FATAL DISINI ===
# HAPUS kata-kata penting (management, learning, dll) dari daftar ini.
# Hanya hapus kata sambung dan kata sifat umum.
STOPWORDS = {
    # Kata Sambung & Preposisi
    'introduction', 'to', 'the', 'and', 'of', 'for', 'in', 'on', 'with', 'at', 'by', 'from',
    'dan', 'dari', 'untuk', 'dengan', 'ke', 'di', 'pada',
    
    # Kata Sifat Umum (Level)
    'basic', 'advanced', 'intermediate', 'fundamental', 'essential',
    'dasar', 'lanjutan', 'tingkat', 'menengah', 'pengenalan',
    
    # Kata Benda Generik (Format)
    'training', 'pelatihan', 'course', 'kursus', 'workshop', 'seminar',
    'online', 'offline', 'class', 'kelas', 'program', 'certification'
}

def preprocess(text):
    if not text:
        return ""
    
    text = str(text).lower()
    
    # 1. Handling Simbol Khusus
    text = text.replace('&', ' and ')
    text = text.replace('/', ' ')
    text = text.replace('-', ' ')
    
    # 2. Hapus karakter aneh (sisakan huruf & angka)
    text = re.sub(r'[^a-z0-9\s]', ' ', text)
    
    # 3. Hapus Stopwords
    tokens = text.split()
    # Hanya hapus jika kata tersebut BENAR-BENAR ada di stopwords
    tokens = [t for t in tokens if t not in STOPWORDS]
    
    return " ".join(tokens)

def get_recommendations(user_gap_text):
    try:
        conn = mysql.connector.connect(**db_config)
        
        # Ambil kolom yang relevan
        query = "SELECT id, title, competency_name, objective FROM trainings"
        df = pd.read_sql(query, conn)
        conn.close()

        if df.empty:
            return []

        df.fillna('', inplace=True)

        # Gabungkan teks untuk pencarian
        df['content'] = (
            df['title'] + " " + 
            df['competency_name'] + " " + 
            df['objective']
        )
        
        df['clean_content'] = df['content'].apply(preprocess)
        user_gap_clean = preprocess(user_gap_text)

        # Debugging: Jika input user jadi kosong (misal user input "Basic Training"), 
        # jangan return kosong, tapi pakai raw inputnya saja biar tetap ada hasil.
        if not user_gap_clean.strip():
            user_gap_clean = user_gap_text.lower()

        all_docs = [user_gap_clean] + df['clean_content'].tolist()

        # === TF-IDF TUNING ===
        # ngram_range=(1, 2): Baca "Project" dan "Project Management"
        # min_df=1: Kata yang muncul sekali pun tetap dihitung
        tfidf = TfidfVectorizer(ngram_range=(1, 2), min_df=1)
        
        tfidf_matrix = tfidf.fit_transform(all_docs)

        cosine_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
        
        sim_scores = list(enumerate(cosine_sim[0]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

        recommendations = []
        for i, score in sim_scores[:10]: # Ambil Top 6
            
            # Threshold Rendah (0.05) untuk TF-IDF itu wajar karena pembobotannya ketat.
            # Ini akan memfilter "noise" seperti kata "Development" yang muncul di judul tambang.
            if score > 0.05: 
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