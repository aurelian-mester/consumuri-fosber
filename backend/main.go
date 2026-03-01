package main

import (
	"database/sql"
	"encoding/csv"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strings"

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
	http.HandleFunc("/api/upload-csv", uploadCSV)

	fmt.Println("✅ Backend Golang pornit pe http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func getDBConnection() (*sql.DB, error) {
	connStr := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s search_path=%s sslmode=disable",
		dbHost, dbPort, dbUser, dbPassword, dbName, schema)
	return sql.Open("postgres", connStr)
}

func initReportingSchema(db *sql.DB) error {
	_, err := db.Exec(`
		CREATE SCHEMA IF NOT EXISTS reporting;

		CREATE TABLE IF NOT EXISTS reporting.past_rolls (
			id SERIAL PRIMARY KEY,
			roll_discharging_time TIMESTAMP,
			roll_charging_time TIMESTAMP,
			roll_remaining_diameter INT,
			roll_remaining_length INT,
			paper_thickness INT,
			splicer INT,
			roll_paper VARCHAR(50),
			paper_grammage INT,
			roll_id VARCHAR(50),
			roll_width INT,
			roll_description VARCHAR(255),
			roll_core_diameter INT,
			order_setup_code VARCHAR(50),
			order_start_time TIMESTAMP
		);

		CREATE TABLE IF NOT EXISTS reporting.trace_rolls (
			id SERIAL PRIMARY KEY,
			shift_start_time TIMESTAMP,
			shift_id INT,
			order_setup_code VARCHAR(50),
			order_start_time TIMESTAMP,
			order_end_time TIMESTAMP,
			order_num_0 VARCHAR(50),
			order_num_1 VARCHAR(50),
			order_num_2 VARCHAR(50),
			splicer_name VARCHAR(50),
			roll_paper VARCHAR(50),
			paper_grammage INT,
			roll_id VARCHAR(50),
			meters INT
		);
	`)
	return err
}

func uploadCSV(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	if r.Method == http.MethodOptions {
		w.Header().Set("Access-Control-Allow-Methods", "POST")
		return
	}

	if r.Method != http.MethodPost {
		http.Error(w, `{"status":"error", "message":"Metoda nepermisa"}`, http.StatusMethodNotAllowed)
		return
	}

	// Array-ul în care vom stoca mesajele de consolă pentru a le trimite la Frontend
	var consoleLogs []string
	consoleLogs = append(consoleLogs, "> Autentificare în PostgreSQL cu succes.")

	err := r.ParseMultipartForm(50 << 20)
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Fisierele depasesc limita de dimensiune"}`, http.StatusBadRequest)
		return
	}

	fileType := r.FormValue("file_type")
	files := r.MultipartForm.File["csv_files"]
	if len(files) == 0 {
		http.Error(w, `{"status":"error", "message":"Nu a fost trimis niciun fisier"}`, http.StatusBadRequest)
		return
	}

	consoleLogs = append(consoleLogs, fmt.Sprintf("> Au fost recepționate %d fișiere de tipul '%s'.", len(files), fileType))

	db, err := getDBConnection()
	if err != nil {
		http.Error(w, `{"status":"error", "message":"Eroare conexiune DB"}`, http.StatusInternalServerError)
		return
	}
	defer db.Close()

	initReportingSchema(db)
	consoleLogs = append(consoleLogs, "> Schema 'reporting' verificată și pregătită.")

	totalInserted := 0
	filesProcessed := 0

	for _, fileHeader := range files {
		consoleLogs = append(consoleLogs, fmt.Sprintf("> Citire structură fișier: %s ...", fileHeader.Filename))
		
		file, err := fileHeader.Open()
		if err != nil {
			consoleLogs = append(consoleLogs, fmt.Sprintf("  [EROARE] Nu am putut deschide %s", fileHeader.Filename))
			continue
		}

		reader := csv.NewReader(file)
		reader.Comma = ';'
		reader.TrimLeadingSpace = true
		reader.FieldsPerRecord = -1

		records, err := reader.ReadAll()
		file.Close()
		
		if err != nil || len(records) < 2 {
			consoleLogs = append(consoleLogs, fmt.Sprintf("  [AVERTISMENT] %s este gol sau corupt. Se ignoră.", fileHeader.Filename))
			continue
		}

		headers := records[0]
		tx, err := db.Begin()
		if err != nil {
			continue
		}

		insertedCount := 0

		if fileType == "past" {
			stmt, err := tx.Prepare(`
				INSERT INTO reporting.past_rolls (
					roll_discharging_time, roll_charging_time, roll_remaining_diameter, 
					roll_remaining_length, paper_thickness, splicer, roll_paper, 
					paper_grammage, roll_id, roll_width, roll_description, 
					roll_core_diameter, order_setup_code, order_start_time
				) VALUES (
					NULLIF($1, '')::timestamp, NULLIF($2, '')::timestamp, NULLIF($3, '')::integer,
					NULLIF($4, '')::integer, NULLIF($5, '')::integer, NULLIF($6, '')::integer,
					$7, NULLIF($8, '')::integer, $9, NULLIF($10, '')::integer, $11, 
					NULLIF($12, '')::integer, $13, NULLIF($14, '')::timestamp
				)
			`)
			if err != nil { tx.Rollback(); continue }

			for i := 1; i < len(records); i++ {
				row := records[i]
				if len(row) < 13 { continue }
				
				ordersRaw := row[12]
				orders := strings.Split(ordersRaw, "|")
				
				for _, order := range orders {
					order = strings.TrimSpace(order)
					if order == "" { continue }
					
					parts := strings.Split(order, "$")
					setupCode := parts[0]
					startTime := ""
					if len(parts) > 1 { startTime = parts[1] }

					_, err := stmt.Exec(
						strings.TrimSpace(row[0]), strings.TrimSpace(row[1]), strings.TrimSpace(row[2]), 
						strings.TrimSpace(row[3]), strings.TrimSpace(row[4]), strings.TrimSpace(row[5]), 
						strings.TrimSpace(row[6]), strings.TrimSpace(row[7]), strings.TrimSpace(row[8]), 
						strings.TrimSpace(row[9]), strings.TrimSpace(row[10]), strings.TrimSpace(row[11]), 
						strings.TrimSpace(setupCode), strings.TrimSpace(startTime),
					)
					if err == nil { insertedCount++ }
				}
			}
			stmt.Close()

		} else if fileType == "trace" {
			stmt, err := tx.Prepare(`
				INSERT INTO reporting.trace_rolls (
					shift_start_time, shift_id, order_setup_code, order_start_time, 
					order_end_time, order_num_0, order_num_1, order_num_2, 
					splicer_name, roll_paper, paper_grammage, roll_id, meters
				) VALUES (
					NULLIF($1, '')::timestamp, NULLIF($2, '')::integer, $3, NULLIF($4, '')::timestamp,
					NULLIF($5, '')::timestamp, $6, $7, $8, $9, $10, NULLIF($11, '')::integer, $12, NULLIF($13, '')::integer
				)
			`)
			if err != nil { tx.Rollback(); continue }

			for i := 1; i < len(records); i++ {
				row := records[i]
				if len(row) < 8 { continue }

				for j := 8; j < len(row); j++ {
					if j >= len(headers) { continue }
					splicerName := headers[j]
					cell := strings.TrimSpace(row[j])
					if cell == "" { continue }

					rolls := strings.Split(cell, "|")
					for _, rData := range rolls {
						rData = strings.TrimSpace(rData)
						if rData == "" { continue }
						
						parts := strings.Split(rData, "$")
						paper := ""
						grammage := ""
						rollID := ""
						meters := ""

						if len(parts) > 0 { paper = parts[0] }
						if len(parts) > 1 { grammage = parts[1] }
						if len(parts) > 2 { rollID = parts[2] }
						if len(parts) > 3 { meters = parts[3] }

						_, err := stmt.Exec(
							strings.TrimSpace(row[0]), strings.TrimSpace(row[1]), strings.TrimSpace(row[2]),
							strings.TrimSpace(row[3]), strings.TrimSpace(row[4]), strings.TrimSpace(row[5]),
							strings.TrimSpace(row[6]), strings.TrimSpace(row[7]),
							strings.TrimSpace(splicerName), strings.TrimSpace(paper), strings.TrimSpace(grammage),
							strings.TrimSpace(rollID), strings.TrimSpace(meters),
						)
						if err == nil { insertedCount++ }
					}
				}
			}
			stmt.Close()
		}

		tx.Commit()
		consoleLogs = append(consoleLogs, fmt.Sprintf("  [OK] %s explodat în %d rânduri normalizate.", fileHeader.Filename, insertedCount))
		totalInserted += insertedCount
		filesProcessed++
	}

	consoleLogs = append(consoleLogs, "===============================================")
	consoleLogs = append(consoleLogs, fmt.Sprintf("> Finalizat. S-au inserat %d rânduri unice din %d fișiere valide.", totalInserted, filesProcessed))

	response := map[string]interface{}{
		"status":  "success",
		"message": "Fisiere procesate cu succes.",
		"logs":    consoleLogs, // Trimitem array-ul de log-uri la Frontend
	}
	json.NewEncoder(w).Encode(response)
}

// RESTUL FUNCTIILOR VECHI
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

func addConsum(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost { return }
	var c Consum
	json.NewDecoder(r.Body).Decode(&c)
	db, err := getDBConnection()
	if err != nil { return }
	defer db.Close()
	query := `INSERT INTO consumuri_fosber.consum_role (schimb, tip_hartie, latime_rola, numar_rola, greutate_kg, metri_liniari, operator) VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id`
	var insertID int
	db.QueryRow(query, c.Schimb, c.TipHartie, c.LatimeRola, c.NumarRola, c.GreutateKg, c.MetriLiniari, c.Operator).Scan(&insertID)
	w.Write([]byte(fmt.Sprintf(`{"status":"success", "id": %d}`, insertID)))
}

func getCombinations(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Content-Type", "application/json")
	startDate := r.URL.Query().Get("start_date")
	endDate := r.URL.Query().Get("end_date")
	if startDate == "" || endDate == "" {
		json.NewEncoder(w).Encode(map[string]interface{}{"status": "success", "data": []interface{}{}})
		return
	}
	startDateTime := startDate + " 00:00:00"
	endDateTime := endDate + " 23:59:59"
	db, err := getDBConnection()
	if err != nil { return }
	defer db.Close()
	query := `
		SELECT
			m0_divisor, program_number, run_number, schedule_date, start_run, end_run,
			status, schedule_status, outs, sheets_scheduled, sheets_produced,
			good_sheets_produced, id, rollsize, schedule_meters, run_meters,
			waste_area, trim_mm, fosber_id, code, order_number, machine_code, machine_name,
			p.paper_number, p.paper
		FROM reporting.vran_combinations
		CROSS JOIN LATERAL (VALUES
			(1, paper1),
			(2, paper2),
			(3, paper3),
			(4, paper4),
			(5, paper5)
		) AS p(paper_number, paper)
		WHERE start_run >= $1 AND start_run <= $2
		  AND p.paper IS NOT NULL AND TRIM(p.paper) <> ''
		ORDER BY start_run DESC, id, p.paper_number
	`
	rows, err := db.Query(query, startDateTime, endDateTime)
	if err != nil { return }
	defer rows.Close()
	cols, _ := rows.Columns()
	var result []map[string]interface{}
	for rows.Next() {
		columns := make([]interface{}, len(cols))
		columnPointers := make([]interface{}, len(cols))
		for i := range columns { columnPointers[i] = &columns[i] }
		if err := rows.Scan(columnPointers...); err != nil { continue }
		m := make(map[string]interface{})
		for i, colName := range cols {
			val := columnPointers[i].(*interface{})
			if val == nil { m[colName] = nil; continue }
			b, ok := (*val).([]byte)
			if ok { m[colName] = string(b) } else { m[colName] = *val }
		}
		result = append(result, m)
	}
	json.NewEncoder(w).Encode(map[string]interface{}{"status": "success", "data": result})
}

