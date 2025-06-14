from flask import Flask, render_template, abort
import os
import json

app = Flask(__name__)
RESULTS_DIR = "results"

@app.route('/')
def index():
    """Ana sayfa, taranan domain'leri ve zaman damgalarını listeler."""
    scanned_folders = []
    try:
        all_dirs = [d for d in os.listdir(RESULTS_DIR) if os.path.isdir(os.path.join(RESULTS_DIR, d))]
        
        scanned_folders = sorted(
            all_dirs,
            key=lambda d: os.path.getmtime(os.path.join(RESULTS_DIR, d)),
            reverse=True
        )
    except FileNotFoundError:
        scanned_folders = []
        
    return render_template('index.html', folders=scanned_folders)


@app.route('/dashboard/<folder_name>')
def dashboard(folder_name):
    """Belirli bir tarama oturumunun sonuçlarını gösterir."""
    domain_path = os.path.join(RESULTS_DIR, folder_name)
    if not os.path.isdir(domain_path):
        return abort(404, description="Bu tarama sonucu bulunamadı.")
    
    domain = folder_name.split('_')[0]
    data = {"domain": domain, "folder_name": folder_name}

    def read_file(path, is_json=False):
        try:
            with open(path, 'r', encoding='utf-8') as f:
                return json.load(f) if is_json else [line.strip() for line in f]
        except (FileNotFoundError, json.JSONDecodeError):
            return []

    data['subdomains'] = read_file(os.path.join(domain_path, "subdomains_with_status.json"), is_json=True)
    data['endpoints'] = read_file(os.path.join(domain_path, "directory.json"), is_json=True)
    data['js_files'] = read_file(os.path.join(domain_path, "js_files.json"), is_json=True)
    
    return render_template('dashboard.html', data=data)

if __name__ == '__main__':
    app.run(debug=True, port=5555)