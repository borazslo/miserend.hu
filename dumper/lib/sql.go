package dumper

import (
	"database/sql"

	_ "github.com/go-sql-driver/mysql"
)

func runSQL(db *sql.DB, query string) error {
	_, err := db.Exec(query)
	return err
}
