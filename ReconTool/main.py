import argparse
import os
import sys
import logging
import json
import tempfile
import subprocess
from datetime import datetime
from urllib.parse import urlparse
from concurrent.futures import ThreadPoolExecutor, as_completed

from modules import utils
from modules.subdomain_finder import run_subdomain_discovery
from modules.js_parser import find_js_files
from modules.dir_fuzzer import fuzz_directories

home_dir = os.path.expanduser('~')
HTTPX_PATH = os.path.join(home_dir, "go/bin/httpx")

def get_subdomains_with_status(subdomains_set, domain):
    if not subdomains_set: return []
    utils.logging.info(f"[{domain}] {len(subdomains_set)} subdomain için durum kodları alınıyor...")
    with tempfile.NamedTemporaryFile(mode='w+', delete=False, suffix=".txt", encoding='utf-8') as temp_file:
        temp_file.writelines(f"{sub}\n" for sub in subdomains_set)
        filepath = temp_file.name
    command = [
        HTTPX_PATH, "-l", filepath,
        "-silent", "-status-code", "-content-length", "-title", "-json",
        "-threads", "100"
    ]
    results_with_status = []
    try:
        if not os.path.exists(command[0]):
             logging.error(f"Komut bulunamadı: {command[0]}")
             return []
        result = subprocess.run(command, capture_output=True, text=True, check=True, encoding='utf-8')
        for line in result.stdout.strip().split('\n'):
            if line:
                try:
                    data = json.loads(line)
                    results_with_status.append({
                        "url": data.get("url", "").replace("https://", "").replace("http://", ""),
                        "status_code": data.get("status_code", 0),
                        "title": data.get("title", ""),
                        "content_length": data.get("content_length", 0)
                    })
                except json.JSONDecodeError: pass
        utils.logging.info(f"[{domain}] {len(results_with_status)} subdomain için durum bilgisi alındı.")
    except subprocess.CalledProcessError as e:
        utils.logging.error(f"[{domain}] httpx çalıştırılırken hata oluştu: {e.stderr.strip()}")
    finally:
        os.remove(filepath)
    return results_with_status

def run_subdomain_scan(domain, results_path):
    utils.logging.info(f"[{domain}] Kapsamlı subdomain keşfi başlatıldı...")
    
    try:
        all_subdomains = set(run_subdomain_discovery(domain))

        if not all_subdomains:
            return "Subdomain: Hiçbir şey bulunamadı."
            
        utils.logging.info(f"[{domain}] Toplamda {len(all_subdomains)} benzersiz subdomain bulundu.")

        subdomains_with_status = get_subdomains_with_status(all_subdomains, domain)
        if subdomains_with_status:
            utils.save_json_to_file(os.path.join(results_path, "subdomains_with_status.json"), subdomains_with_status)
            live_hosts_200 = [f"https://{item['url']}" for item in subdomains_with_status if item.get('status_code') == 200]
            utils.save_list_to_file(os.path.join(results_path, "live_hosts_200.txt"), live_hosts_200)

        return f"Subdomain: {len(all_subdomains)} adet bulundu."
    
    except Exception as e:
        utils.logging.error(f"[{domain}] Subdomain taramasında beklenmedik ana hata: {e}")
        return "Subdomain: Hata."


def run_dir_fuzz(target_url, wordlist, results_path, threads):
    domain = urlparse(target_url).netloc
    utils.logging.info(f"[{domain}] Dizin tarama ({threads} thread) başlatıldı...")
    
    endpoints = fuzz_directories([target_url], wordlist, threads)
    
    if not endpoints:
        return "Dizin Tarama: Hiçbir endpoint bulunamadı."

    utils.save_json_to_file(os.path.join(results_path, "directory.json"), endpoints)
    
    raw_urls = [item['url'] for item in endpoints]
    utils.save_list_to_file(os.path.join(results_path, "directories_raw.txt"), raw_urls)

    return f"Dizin Tarama: {len(endpoints)} adet bulundu."

def run_js_scan(target_url, results_path):
    domain = urlparse(target_url).netloc
    utils.logging.info(f"[{domain}] JS dosyası arama başlatıldı...")
    js_files = find_js_files([target_url])
    utils.save_json_to_file(os.path.join(results_path, "js_files.json"), js_files)
    return f"JS Tarama: {len(js_files)} adet bulundu."

def process_target(target_url, args):
    domain = urlparse(target_url).netloc
    timestamp = datetime.now().strftime("%H-%M-%S__%d-%m-%Y")
    results_dir_name = f"{domain}_{timestamp}"
    results_path = utils.create_results_dir(results_dir_name)
    utils.logging.info(f"'{domain}' için tarama başlatıldı. Sonuçlar: {results_dir_name}")
    with ThreadPoolExecutor(max_workers=3) as executor:
        futures = []
        if args.sub:
            futures.append(executor.submit(run_subdomain_scan, domain, results_path))
        if args.dir:
            futures.append(executor.submit(run_dir_fuzz, target_url, args.wordlist, results_path, args.threads))
        if args.js:
            futures.append(executor.submit(run_js_scan, target_url, results_path))
        utils.logging.info(f"[{domain}] {len(futures)} adet görev başlatıldı. Tamamlanmaları bekleniyor...")
        for future in as_completed(futures):
            try:
                result = future.result()
                if args.debug:
                    utils.logging.info(f"[{domain}] (DEBUG) Görev Sonucu: {result}")
            except Exception as exc:
                utils.logging.error(f"[{domain}] Bir görev hata ile sonuçlandı: {exc}")
    utils.logging.info(f"'{domain}' için tüm tarama görevleri tamamlandı.")

def main():
    parser = argparse.ArgumentParser(description="Eş Zamanlı OSINT & Recon Aracı (Linux Odaklı)")
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-u", "--url", help="Tek bir hedef URL")
    group.add_argument("-l", "--list", help="Hedef domain listesini içeren dosya")
    parser.add_argument("-sub", action="store_true", help="Subdomain keşfini etkinleştir")
    parser.add_argument("-dir", action="store_true", help="Dizin taramasını etkinleştirir")
    parser.add_argument("-js", action="store_true", help="JS dosyası aramasını etkinleştirir")
    parser.add_argument("-w", "--wordlist", default="wordlists/common.txt", help="Dizin taraması için wordlist yolu")
    parser.add_argument("-t", "--threads", type=int, default=10, help="Dizin taraması için thread sayısı")
    parser.add_argument("--debug", action="store_true", help="Detaylı hata ayıklama çıktılarını gösterir")
    args = parser.parse_args()
    if not any([args.sub, args.dir, args.js]):
        parser.print_help()
        sys.exit("\n[HATA] Lütfen en az bir tarama türü seçin.")
    if not args.debug:
        for handler in logging.root.handlers[:]: logging.root.removeHandler(handler)
        logging.basicConfig(level=logging.INFO, format='%(message)s', stream=sys.stdout)
    if sys.platform == "win32":
        utils.logging.warning("\n[UYARI] Bu araç Linux için tasarlanmıştır. WSL içinde çalıştırın.\n")
    if args.url:
        process_target(args.url if args.url.startswith('http') else f"https://{args.url}", args)
    elif args.list:
        try:
            with open(args.list, 'r') as f: targets = [line.strip() for line in f if line.strip()]
            for target in targets: process_target(target if target.startswith('http') else f"https://{target}", args)
        except FileNotFoundError: sys.exit(f"[HATA] Liste dosyası bulunamadı: {args.list}")
    utils.logging.info("\n[***] Tüm işlemler tamamlandı! [***]")

if __name__ == "__main__":
    main()