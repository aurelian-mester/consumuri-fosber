package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	_ "github.com/lib/pq"
)

const (
	dbHost     = "172.16.75.97"
	dbPort     = 5432
	dbUser     = "biusr"
	dbPassword = "5324"
	dbName     = "dwh"
	schema     = "consumuri_fosber"
)

// Structura care mapează datele primite din formularul PHP
type Consum struct {
	Schimb       int     `json:"schimb"`
	TipHartie    string  `json:"tip_hartie"`
	LatimeRola   int     `json:"latime_rola"`
	NumarRola    string  `json:"numar_rola"`
	GreutateKg   float64 `json:"greutate_kg"`
	MetriLiniari int     `json:"metri_liniari"`
	Operator     string  `json:"operator"`
}

func main() {
	http.HandleFunc("/api/test-db", testDBConnection)
	http.HandleFunc("/api/add-consum", addConsum) // Ruta noua pentru inserare

	fmt.Println("✅ Backend Golang pornit pe http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func getDBConnection() (*sql.DB, error) {
	connStr := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s search_path=%s sslmode=disable",
		dbHost, dbPort, dbUser, dbPassword, dbName, schema)
	return sql.Open("postgres", connStr)
}

func addConsum(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, `{"status":"error", "message":"Metoda nepermisa"}`, http.StatusMethodNotAllowed)
		return
	}

	// 1. Decodăm JSON-ul primit de la PHP
	var c Consum
	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Date invalide"}`, http.StatusBadRequest)
		return
	}

	// 2. Ne conectăm la baza de date
	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare conexiune DB"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

	// 3. Inserăm datele în tabelul consum_role
	query := `INSERT INTO consumuri_fosber.consum_role 
		(schimb, tip_hartie, latime_rola, numar_rola, greutate_kg, metri_liniari, operator) 
		VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id`

	var insertID int
	err = db.QueryRow(query, c.Schimb, c.TipHartie, c.LatimeRola, c.NumarRola, c.GreutateKg, c.MetriLiniari, c.Operator).Scan(&insertID)
	
	if err != nil {
		log.Printf("Eroare la inserare: %v", err)
		http.Error(w, `{"status":"error", "message":"Eroare la salvarea in baza de date"}`, http.StatusInternalServerError)
		return
	}

	// 4. Returnăm succes
	response := fmt.Sprintf(`{"status":"success", "message":"Consum adaugat cu succes!", "id": %d}`, insertID)
	w.Write([]byte(response))
}

func testDBConnection(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")

	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

	var dbTime string
	db.QueryRow("SELECT NOW()").Scan(&dbTime)
	w.Write([]byte(fmt.Sprintf(`{"status":"success", "db_time":"%s"}`, dbTime)))
}

