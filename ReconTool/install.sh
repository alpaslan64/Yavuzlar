#!/bin/bash


GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' 

if [ "$EUID" -eq 0 ]; then
  echo -e "${RED}[HATA] Bu betiği root olarak çalıştırmayın! Gerekli yerlerde sudo ile şifreniz istenecektir.${NC}"
  exit 1
fi

echo -e "${GREEN}--- ReconFlow Kurulum Betiği Başlatıldı ---${NC}"

echo -e "\n${YELLOW}[ADIM 1/4] Sistem güncelleniyor ve temel bağımlılıklar (git, python3-pip) kuruluyor...${NC}"
sudo apt-get update && sudo apt-get install -y git python3-pip

if ! command -v go &> /dev/null
then
    echo -e "\n${YELLOW}[ADIM 2/4] Go programlama dili bulunamadı. Kuruluyor...${NC}"
    LATEST_GO=$(curl -s https://go.dev/VERSION?m=text)
    wget "https://golang.org/dl/$LATEST_GO.linux-amd64.tar.gz" -O go.tar.gz
    sudo rm -rf /usr/local/go && sudo tar -C /usr/local -xzf go.tar.gz
    rm go.tar.gz
    echo -e "${GREEN}Go başarıyla kuruldu.${NC}"
else
    echo -e "\n${GREEN}[ADIM 2/4] Go zaten kurulu. Bu adım atlanıyor.${NC}"
fi

echo -e "\n${YELLOW}[ADIM 3/4] Go için PATH ortam değişkenleri ayarlanıyor...${NC}"
SHELL_CONFIG_FILE=""
if [ -f "$HOME/.zshrc" ]; then
    SHELL_CONFIG_FILE="$HOME/.zshrc"
elif [ -f "$HOME/.bashrc" ]; then
    SHELL_CONFIG_FILE="$HOME/.bashrc"
else
    echo -e "${RED}Uygun kabuk yapılandırma dosyası (.bashrc veya .zshrc) bulunamadı. Lütfen manuel olarak PATH'i ayarlayın.${NC}"
fi

if [ -n "$SHELL_CONFIG_FILE" ]; then
    if ! grep -q 'export PATH=$PATH:/usr/local/go/bin' "$SHELL_CONFIG_FILE"; then
        echo 'export PATH=$PATH:/usr/local/go/bin' >> "$SHELL_CONFIG_FILE"
    fi
    if ! grep -q 'export PATH=$PATH:$HOME/go/bin' "$SHELL_CONFIG_FILE"; then
        echo 'export PATH=$PATH:$HOME/go/bin' >> "$SHELL_CONFIG_FILE"
    fi
    echo -e "${GREEN}PATH ayarlandı. Değişikliklerin etkili olması için yeni bir terminal açın veya 'source $SHELL_CONFIG_FILE' komutunu çalıştırın.${NC}"
fi

export PATH=$PATH:/usr/local/go/bin:$HOME/go/bin

echo -e "\n${YELLOW}[ADIM 4/4] Recon araçları (subfinder, amass, httpx) ve Python bağımlılıkları kuruluyor...${NC}"

go install -v github.com/projectdiscovery/subfinder/v2/cmd/subfinder@latest
go install -v github.com/owasp-amass/amass/v4/cmd/amass@master
go install -v github.com/projectdiscovery/httpx/v2/cmd/httpx@latest

pip install -r requirements.txt --break-system-packages

echo -e "\n${GREEN}--- Kurulum Tamamlandı! ---${NC}"
echo -e "Aracı kullanmaya başlamadan önce ${YELLOW}yeni bir terminal penceresi açmanız${NC} veya aşağıdaki komutu çalıştırmanız önerilir:"
echo -e "source $SHELL_CONFIG_FILE"
