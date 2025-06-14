import requests
import logging
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

def get_js_from_url(url):
    """Tek bir URL'den JS dosyalarını çeker."""
    found_js = set()
    try:
        response = requests.get(url, timeout=10, verify=False, allow_redirects=True, headers={'User-Agent': 'ReconFlow Tool'})
        soup = BeautifulSoup(response.text, 'html.parser')
        
        for script_tag in soup.find_all('script', src=True):
            src = script_tag.get('src')
            if src and src.endswith('.js'):
                full_url = urljoin(url, src)
                found_js.add(full_url)
    except requests.RequestException as e:
        logging.warning(f"JS dosyası aranırken {url} adresine ulaşılamadı: {e}")
    return found_js

def find_js_files(urls_to_scan, threads=20):
    """Verilen URL listesinde çoklu thread ile JS dosyalarını arar."""
    domain = urlparse(urls_to_scan[0]).netloc
    logging.info(f"[{domain}] {len(urls_to_scan)} URL üzerinde JS dosyası taraması başlatıldı.")
    all_js_files = set()

    with ThreadPoolExecutor(max_workers=threads) as executor:
        future_to_url = {executor.submit(get_js_from_url, url): url for url in urls_to_scan}
        
        for future in as_completed(future_to_url):
            try:
                result = future.result()
                if result:
                    all_js_files.update(result)
            except Exception as e:
                logging.error(f"JS dosyası işlenirken bir hata oluştu: {e}")

    return sorted(list(all_js_files))