import subprocess
import logging
import os
import re
from concurrent.futures import ThreadPoolExecutor
import select

home_dir = os.path.expanduser('~')
SUBFINDER_PATH = os.path.join(home_dir, "go/bin/subfinder")
AMASS_PATH = os.path.join(home_dir, "go/bin/amass")

def stream_command_output(command_list, domain):
    """
    Bir komutu başlatır ve çıktısını anlık olarak (satır satır) okur.
    Bu, uzun süren komutların takılmasını engeller ve ara sonuçları almamızı sağlar.
    """
    tool_name = os.path.basename(command_list[0])
    found_lines = []

    if not os.path.exists(command_list[0]):
        return []

    try:
        logging.info(f"[{domain}] {tool_name} ile anlık okuma modunda tarama başlatıldı...")
        process = subprocess.Popen(
            command_list,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            encoding='utf-8'
        )

        while True:
            reads = [process.stdout.fileno()]
            ret = select.select(reads, [], [], 1.0) 

            if ret[0]: 
                line = process.stdout.readline()
                if line:
                    found_lines.append(line.strip())
            
            if process.poll() is not None:
                break
        
        for line in process.stdout.readlines():
            found_lines.append(line.strip())
            
        logging.info(f"[{domain}] {tool_name} taraması tamamlandı.")
        return found_lines

    except Exception as e:
        logging.error(f"[{domain}] {tool_name} çalıştırılırken hata oluştu: {e}")
        return []


def parse_amass_output(raw_output, domain):
    clean_subdomains = set()
    domain_regex = re.compile(r'([a-zA-Z0-9\-\.]+\.' + re.escape(domain) + r')')
    full_text = "\n".join(raw_output)
    found_matches = domain_regex.findall(full_text)
    clean_subdomains.update(found_matches)
    return clean_subdomains

def run_subdomain_discovery(domain):
    all_found_subdomains = set()
    
    logging.info(f"[{domain}] Subfinder ile tarama başlatıldı...")
    subfinder_cmd = [SUBFINDER_PATH, "-d", domain, "-silent"]
    if os.path.exists(SUBFINDER_PATH):
        try:
            result = subprocess.run(subfinder_cmd, check=True, capture_output=True, text=True, timeout=300)
            subfinder_results = [line.strip() for line in result.stdout.strip().split('\n') if line.strip()]
            logging.info(f"[{domain}] Subfinder {len(subfinder_results)} subdomain buldu.")
            all_found_subdomains.update(subfinder_results)
        except Exception as e:
            logging.warning(f"[{domain}] Subfinder çalıştırılırken bir sorun oluştu: {e}")
    
    amass_cmd = [AMASS_PATH, "enum", "-passive", "-d", domain, "-timeout", "3"]  
    amass_raw_results = stream_command_output(amass_cmd, domain)
    
    if amass_raw_results:
        amass_parsed_results = parse_amass_output(amass_raw_results, domain)
        logging.info(f"[{domain}] Amass {len(amass_parsed_results)} subdomain buldu.")
        all_found_subdomains.update(amass_parsed_results)

    return list(all_found_subdomains)