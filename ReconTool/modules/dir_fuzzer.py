import requests
import logging
from urllib.parse import urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

def fuzz_url(url, word):
    """Tek bir URL'i test eder."""
    try:
        target_url = f"{url.rstrip('/')}/{word}"
        response = requests.get(target_url, timeout=5, verify=False, allow_redirects=True, headers={'User-Agent': 'ReconFlow Tool'})
        if response.status_code in [200, 204, 301, 302, 307, 403]:
            return {"url": target_url, "status": response.status_code}
    except requests.RequestException:
        pass
    return None

def fuzz_directories(base_urls, wordlist_path, threads=10):
    """Verilen URL listesi üzerinde çoklu thread ile dizin taraması yapar."""
    found_endpoints = []

    try:
        with open(wordlist_path, 'r', encoding='utf-8') as f:
            words = [line.strip() for line in f]
    except FileNotFoundError:
        logging.error(f"[HATA] Wordlist bulunamadı: {wordlist_path}")
        return []
    
    with ThreadPoolExecutor(max_workers=threads) as executor:
        fuzz_tasks = {executor.submit(fuzz_url, base_url, word): word for base_url in base_urls for word in words}
        
        for future in as_completed(fuzz_tasks):
            result = future.result()
            if result:
                found_endpoints.append(result)

    return sorted(found_endpoints, key=lambda x: x['url'])