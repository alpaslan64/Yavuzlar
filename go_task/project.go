package main

import (
	"fmt"
	"os"
	"time"
)

var adminUsername = "admin"
var adminPassword = "admin"

var username = []string{"musteri1", "musteri2", "musteri3", "musteri4", "musteri5"}
var passwords = []string{"12345", "12345", "12345", "12345", "12345"}

func main() {
	createLogFiles()
	logFunc("Program çalıştırıldı")
	login()
	logFunc("Program kapandı")
}

var usernameInput, passwordInput string

func login() {
	var role int
	fmt.Print("\033[H\033[2J")
	fmt.Println("Hoşgeldiniz!!")
	fmt.Println("0: Admin Girişi")
	fmt.Println("1: Müşteri Girişi")
	fmt.Print("Rolünüzü seçin: ")
	fmt.Scan(&role)

	if role == 0 {
		adminCheck()
	} else if role == 1 {
		customerCheck()
	} else if role != 0 && role != 1 {
		fmt.Println("Geçersiz rol seçimi!")
		logFunc("Geçersiz rol seçildi")
		login()
	}

}

func adminCheck() {
	for {
		//fmt.Print("\033[H\033[2J")
		fmt.Print("Admin Kullanıcı Adı: ")
		fmt.Scan(&usernameInput)
		fmt.Print("Admin Şifre: ")
		fmt.Scan(&passwordInput)

		if usernameInput == adminUsername && passwordInput == adminPassword {
			logFunc("Admin giriş yaptı")
			fmt.Println("\nHoş geldiniz admin")
			admin()
			return
		} else {
			logFunc("Admin için hatalı giriş")
			fmt.Println("Kullanıcı adı veya şifre hatalı!")

		}
	}
}

func customerCheck() {
	for {
		//fmt.Print("\033[H\033[2J")
		fmt.Print("Müşteri Kullanıcı Adı: ")
		fmt.Scan(&usernameInput)
		fmt.Print("Müşteri Şifre: ")
		fmt.Scan(&passwordInput)

		for i := 0; i < len(username); i++ {
			if username[i] == usernameInput && passwords[i] == passwordInput {
				logFunc(usernameInput + " giriş yaptı")
				fmt.Println("\nHoş geldiniz " + usernameInput)
				customer()
				return
			}
		}
		logFunc("Müşteri için hatalı giriş")
		fmt.Println("Kullanıcı adı veya şifre hatalı!")
	}
}

func admin() {
	for {
		//fmt.Print("\033[H\033[2J")
		fmt.Println("\nAdmin Menüsü:")
		fmt.Println("a- Müşteri ekleme")
		fmt.Println("b- Müşteri Silme")
		fmt.Println("c- Log Listeleme")
		fmt.Println("q- Çıkış")
		var secim string
		fmt.Print("Seçim yapınız: ")
		fmt.Scan(&secim)
		if secim == "a" {
			fmt.Println("Müşteri ekleme seçildi\n")
			logFunc("Müşteri ekleme seçildi")
			addCustomer()
		} else if secim == "b" {
			fmt.Println("Müşteri silme seçildi\n")
			logFunc("Müşteri silme seçildi")
			deleteCustomer()
		} else if secim == "c" {
			fmt.Println("Log listeleme seçildi\n")
			logFunc("Log listeleme seçildi")
			showLog()
		} else if secim == "q" {
			fmt.Println("Çıkış Yapılıyor..")
			logFunc("Admin çıkış yaptı")
			login()
		} else {
			fmt.Println("Geçersiz Seçim!!")
			logFunc("Geçersiz Seçim")
			admin()
		}

	}
}

func addCustomer() {
	var newUsername, newPassword string
	fmt.Println("Geri dönmek için 'q' girin!")
	fmt.Print("Yeni kullanıcı adını girin:")
	fmt.Scan(&newUsername)
	if newUsername == "q" {
		logFunc("Müşteri eklemekten vazgeçildi")
		return
	}
	fmt.Print("Yeni kullanıcının şifresini girin: ")
	fmt.Scan(&newPassword)
	if newPassword == "q" {
		return
	}

	username = append(username, newUsername)
	passwords = append(passwords, newPassword)

	logFunc("Yeni müşteri eklendi: " + newUsername)
	fmt.Println("Yeni müşteri eklendi: " + newUsername)

}

func deleteCustomer() {
	var deleteUser string
	fmt.Println("Müşteriler;")
	for i := 0; i < len(username); i++ {
		fmt.Println("-" + username[i])
	}
	fmt.Println("Geri dönmek için 'q' girin")
	fmt.Print("Silinecek müşterinin kullanıcı adını girin: ")
	fmt.Scan(&deleteUser)
	if deleteUser == "q" {
		logFunc("Müşteri silmekten vazgeçildi")
		return
	}
	for i := 0; i < len(username); i++ {
		if username[i] == deleteUser {
			username = append(username[:i], username[i+1:]...)
			passwords = append(passwords[:i], passwords[i+1:]...)

			logFunc("Müşteri silindi: " + deleteUser)
			fmt.Println("Müşteri silindi: " + deleteUser)
		}
	}
}

func showLog() {
	logs, err := os.ReadFile("log.txt")
	if err != nil {
		fmt.Println("Log dosyası açılamadı:", err)
		return
	}
	logFunc("Log listelendi")
	fmt.Println("Log Kayıtları:")
	fmt.Print(string(logs))
}

func customer() {
	for {
		//fmt.Print("\033[H\033[2J")
		fmt.Println("\nMüşteri Menüsü:")
		fmt.Println("a- Profili görüntüle")
		fmt.Println("b- Şifreyi değiştirme")
		fmt.Println("q- Çıkış")
		var secim string
		fmt.Scan(&secim)
		fmt.Print("Seçim Yapınız: ")
		if secim == "a" {
			fmt.Println("Profili görüntüle seçildi\n")
			logFunc("Profili görüntüle seçildi")
			showProfile()
		} else if secim == "b" {
			fmt.Println("Şifreyi değiştirme seçildi\n")
			logFunc("Şifreyi değiştirme seçildi")
			changePassword()
		} else if secim == "q" {
			fmt.Println("Çıkış Yapılıyor..")
			logFunc(usernameInput + " çıkış yaptı")
			login()
		} else {
			fmt.Println("Geçersiz Seçim!!")
			logFunc("Geçersiz Seçim")
			customer()
		}

	}
}

func showProfile() {
	logFunc(usernameInput + "profilini görüntüledi")
	fmt.Println("Mevcut kullanıcı: " + usernameInput + ":" + passwordInput)
}

func changePassword() {
	var oldPassword, newPassword string
	for {
		fmt.Println("Geri gelmek için 'q' girin")
		fmt.Print("Eski şifreyi girin: ")
		fmt.Scan(&oldPassword)
		if oldPassword == "q" {
			logFunc(usernameInput + " şifre değiştirmekten vazgeçti")
			return
		} else if passwordInput == oldPassword {
			fmt.Print("Yeni şifreni gir: ")
			fmt.Scan(&newPassword)
			for i := 0; i < len(username); i++ {
				if username[i] == usernameInput {
					passwords[i] = newPassword
					logFunc("Şifre değiştirildi: " + usernameInput + ":" + newPassword)
					fmt.Println("Şifre değiştirildi: " + usernameInput)
					return
				}
			}
		} else {
			logFunc("Şifre uyuşmazlığı yaşandı")
			fmt.Println("Şifre uyuşmazlığı!! Lütfen tekrar deneyin.")
		}
	}
}

func createLogFiles() {
	if _, err := os.Stat("log.txt"); os.IsNotExist(err) {
		_, err = os.Create("log.txt")
		if err != nil {
			fmt.Println("Log dosyası oluşturulamadı:", err)
		}
	}
}

func logFunc(message string) {
	file, err := os.OpenFile("log.txt", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		fmt.Println("Log dosyasına yazılamadı:", err)
		return
	}
	defer file.Close()

	timeStamp := time.Now().Format("15:04:05 - 02/01/2006")
	_, err = file.WriteString(fmt.Sprintf("%s: %s\n", timeStamp, message))
	if err != nil {
		fmt.Println("Mesaj log dosyasına yazılamadı:", err)
	}
}
