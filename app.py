from flask import Flask, request, jsonify
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import re
import warnings

warnings.filterwarnings("ignore")

app = Flask(__name__)

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
    text = text.replace('&', ' and ').replace('/', ' ').replace('-', ' ')
    text = re.sub(r'[^a-z0-9\s]', ' ', text)
    tokens = text.split()
    tokens = [t for t in tokens if t not in STOPWORDS]
    return " ".join(tokens)

# INI ADALAH ENDPOINT API KITA
@app.route('/recommend', methods=['POST'])
def recommend():
    try:
        # Menangkap data yang dikirim oleh Laravel
        data = request.json
        user_gap_text = data.get('gap_text', '')
        trainings = data.get('trainings', []) # Berisi list dari Laravel

        if not user_gap_text or not trainings:
            return jsonify([])

        # Jadikan DataFrame
        df = pd.DataFrame(trainings)
        df.fillna('', inplace=True)

        # Text Boosting
        df['content'] = (
            df['title'].astype(str) + " " + df['title'].astype(str) + " " + df['title'].astype(str) + " " + 
            df['competency_name'].astype(str) + " " + df['competency_name'].astype(str) + " " + 
            df['objective'].astype(str)
        )
        
        df['clean_content'] = df['content'].apply(preprocess)
        user_gap_clean = preprocess(user_gap_text)

        if not user_gap_clean.strip():
            user_gap_clean = user_gap_text.lower()

        all_docs = [user_gap_clean] + df['clean_content'].tolist()

        # Proses TF-IDF
        tfidf = TfidfVectorizer(ngram_range=(1, 2), min_df=1, sublinear_tf=True)
        tfidf_matrix = tfidf.fit_transform(all_docs)
        cosine_sim = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
        
        sim_scores = list(enumerate(cosine_sim[0]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

        recommendations = []
        for i, score in sim_scores[:10]:
            if score > 0.01: 
                recommendations.append({
                    'id': int(df.iloc[i]['id']),
                    'score': round(float(score), 4) 
                })

        return jsonify(recommendations)

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # Jalankan server API di port 5000
    app.run(debug=True, host='0.0.0.0', port=5000)