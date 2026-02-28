package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	_ "github.com/lib/pq"
)

// Constantele pentru baza de date
const (
	dbHost     = "172.16.75.97"
	dbPort     = 5432
	dbUser     = "biusr"
	dbPassword = "5324"
	dbName     = "dwh"
	schema     = "consumuri_fosber" // Schema implicită
)

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
	http.HandleFunc("/api/add-consum", addConsum)
	http.HandleFunc("/api/combinations", getCombinations)

	fmt.Println("✅ Backend Golang pornit pe http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func getDBConnection() (*sql.DB, error) {
	connStr := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s search_path=%s sslmode=disable",
		dbHost, dbPort, dbUser, dbPassword, dbName, schema)
	return sql.Open("postgres", connStr)
}

func testDBConnection(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")

	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare conexiune DB"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

	var dbTime string
	err = db.QueryRow("SELECT NOW()").Scan(&dbTime)
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare interogare"}`, http.StatusInternalServerError)
		return
	}

	w.Write([]byte(fmt.Sprintf(`{"status":"success", "db_time":"%s"}`, dbTime)))
}

func addConsum(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, `{"status":"error", "message":"Metoda nepermisa"}`, http.StatusMethodNotAllowed)
		return
	}

	var c Consum
	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Date invalide"}`, http.StatusBadRequest)
		return
	}

	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare conexiune DB"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

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

	response := fmt.Sprintf(`{"status":"success", "message":"Consum adaugat cu succes!", "id": %d}`, insertID)
	w.Write([]byte(response))
}

// ==========================================
// 3. Extragerea FILTRATA din ss02.combinations
// ==========================================
func getCombinations(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")

	// Preluăm datele de filtrare din URL
	startDate := r.URL.Query().Get("start_date")
	endDate := r.URL.Query().Get("end_date")

	// Dacă nu avem ambele date, returnăm un array gol (nu afișăm nimic)
	if startDate == "" || endDate == "" {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"status": "success",
			"data":   []interface{}{}, 
		})
		return
	}

	// Adăugăm orele pentru a acoperi zilele complete
	startDateTime := startDate + " 00:00:00"
	endDateTime := endDate + " 23:59:59"

	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare conexiune DB"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

	// Query-ul acum filtrează EXACT pe StartRun folosind parametrii siguri ($1, $2) și aduce toate înregistrările
	query := `SELECT * FROM ss02.combinations WHERE "StartRun" >= $1 AND "StartRun" <= $2 ORDER BY "StartRun" DESC`
	
	rows, err := db.Query(query, startDateTime, endDateTime)
	if err != nil {
		log.Printf("Eroare Query: %v", err)
		http.Error(w, `{"status":"error", "message":"Eroare citire date"}`, http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	cols, _ := rows.Columns()
	var result []map[string]interface{}

	for rows.Next() {
		columns := make([]interface{}, len(cols))
		columnPointers := make([]interface{}, len(cols))
		for i := range columns {
			columnPointers[i] = &columns[i]
		}

		if err := rows.Scan(columnPointers...); err != nil {
			continue
		}

		m := make(map[string]interface{})
		for i, colName := range cols {
			val := columnPointers[i].(*interface{})
			if val == nil {
				m[colName] = nil
				continue
			}
			
			b, ok := (*val).([]byte)
			if ok {
				m[colName] = string(b)
			} else {
				m[colName] = *val
			}
		}
		result = append(result, m)
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"status": "success",
		"data":   result,
	})
}

