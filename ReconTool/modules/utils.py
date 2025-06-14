import os
import json
import logging
import sys

logging.basicConfig(level=logging.INFO, format='[%(levelname)s] %(message)s', stream=sys.stdout)

def create_results_dir(dir_name):
    """Her tarama oturumu için sonuçların saklanacağı bir klasör oluşturur."""
    path = os.path.join("results", dir_name)
    os.makedirs(path, exist_ok=True)
    return path

def save_list_to_file(filepath, data_list):
    """Bir listeyi dosyaya satır satır yazar."""
    with open(filepath, 'w', encoding='utf-8') as f:
        for item in sorted(data_list):
            f.write(f"{item}\n")
    logging.info(f"[{os.path.basename(os.path.dirname(filepath))}] {len(data_list)} öğe '{os.path.basename(filepath)}' dosyasına kaydedildi.")

def save_json_to_file(filepath, data):
    """Python dict/list yapısını JSON dosyasına yazar."""
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)
    logging.info(f"[{os.path.basename(os.path.dirname(filepath))}] JSON verisi '{os.path.basename(filepath)}' dosyasına kaydedildi.")