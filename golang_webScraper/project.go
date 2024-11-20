package main

import (
	"fmt"
	"net/http"
	"os"
	"strings"
	"time"

	"github.com/PuerkitoBio/goquery"
)

func main() {
	args := os.Args

	if len(args) == 1 {
		help()
		return
	}
	if len(args) == 2 {
		switch args[1] {
		case "-h":
			help()
		case "-1":
			thehackernewscom()
		case "-2":
			dovizcom()
		case "-3":
			usakbeltr()
		case "-4":
			os.Exit(0)
		}

	} else {
		fmt.Println("Please choose an option! Use '-h' for help.")
	}

}

func help() {
	fmt.Printf(`
Usage:
  go run project.go [OPTION]

Options:
  -1 Takes news headlines, dates and descriptions from "TheHackerNews.com"
  -2 Gets exchange rates, values ​​and increase rates from "doviz.com"
  -3 Gets news headlines, dates and descriptions from "usak.bel.tr"
  -4 Exits the program.
  -h Shows the help menu.
`)
}

func thehackernewscom() {
	res, _ := http.Get("https://thehackernews.com/")
	if res.StatusCode != 200 {
		fmt.Println("Error", res.StatusCode)
		return
	}

	title := []string{}
	date := []string{}
	desc := []string{}

	doc, _ := goquery.NewDocumentFromReader(res.Body)

	doc.Find("h2.home-title").Each(func(i int, selection *goquery.Selection) {
		title = append(title, strings.TrimSpace(selection.Text()))
	})

	doc.Find("span.h-datetime").Each(func(i int, selection *goquery.Selection) {
		rawDate := strings.TrimSpace(selection.Text())
		cleanDate := rawDate[3:]
		date = append(date, cleanDate)

	})

	doc.Find("div.home-desc").Each(func(i int, selection *goquery.Selection) {
		desc = append(desc, strings.TrimSpace(selection.Text()))
	})

	for i := 0; i < len(title) && i < len(date) && i < len(desc); i++ {
		output := fmt.Sprintf("Number: %d\nTitle: %s\nDate: %s\nDescription: %s\n---------------------------\n", i+1, title[i], date[i], desc[i])
		fmt.Print(output)
		fileWrite(output, "thehackernews.com")
	}

}

func dovizcom() {
	res, _ := http.Get("https://www.doviz.com/")
	if res.StatusCode != 200 {
		fmt.Println("Error", res.StatusCode)
		return
	}

	unit := []string{}
	value := []string{}
	rate := []string{}

	doc, _ := goquery.NewDocumentFromReader(res.Body)

	doc.Find("span.name").Each(func(i int, selection *goquery.Selection) {
		unit = append(unit, strings.TrimSpace(selection.Text()))
	})

	doc.Find("span.value").Each(func(i int, selection *goquery.Selection) {
		value = append(value, strings.TrimSpace(selection.Text()))
	})

	doc.Find("div.change-rate").Each(func(i int, selection *goquery.Selection) {
		rate = append(rate, strings.TrimSpace(selection.Text()))
	})

	for i := 0; i < len(unit) && i < len(value) && i < len(rate); i++ {
		output := fmt.Sprintf("Unit: %s\nValue: %s %s\nRate: %s\n-----------------\n", unit[i], value[i], "TL", rate[i])
		fmt.Print(output)
		fileWrite(output, "doviz.com")
	}
}

func usakbeltr() {
	res, _ := http.Get("https://usak.bel.tr/haber-kategori/tum-haberler")
	if res.StatusCode != 200 {
		fmt.Println("Error", res.StatusCode)
		return
	}

	title := []string{}
	date := []string{}
	desc := []string{}

	doc, _ := goquery.NewDocumentFromReader(res.Body)

	doc.Find("h4.title").Each(func(i int, selection *goquery.Selection) {
		title = append(title, strings.TrimSpace(selection.Text()))
	})

	doc.Find("p.tarih").Each(func(i int, selection *goquery.Selection) {
		date = append(date, strings.TrimSpace(selection.Text()))
	})

	doc.Find("p.icerik").Each(func(i int, selection *goquery.Selection) {
		desc = append(desc, strings.TrimSpace(selection.Text()))
	})

	for i := 0; i < len(title) && i < len(date) && i < len(desc); i++ {
		output := fmt.Sprintf("Number: %d\nTitle: %s\nDate: %s\nDescription: %s\n---------------------------\n", i+1, title[i], date[i], desc[i])
		fmt.Print(output)
		fileWrite(output, "usakbel.tr")
	}
}

func fileWrite(data string, link string) {
	dir := "./outputs"
	if _, err := os.Stat(dir); os.IsNotExist(err) {
		err := os.Mkdir(dir, 0755)
		if err != nil {
			fmt.Println("Folder could not created", err)
			return
		}
	}

	currentTime := time.Now()
	fileName := fmt.Sprintf("%s/%s - %02d.%02d.%02d - %02d.%02d.%04d.txt",
		dir,
		link,
		currentTime.Hour(),
		currentTime.Minute(),
		currentTime.Second(),
		currentTime.Day(),
		currentTime.Month(),
		currentTime.Year())

	file, err := os.OpenFile(fileName, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		fmt.Println("File could not be opened:", err)
		return
	}
	defer file.Close()

	_, err = file.WriteString(data)
	if err != nil {
		fmt.Println("Could not write to file", err)
	}
}
