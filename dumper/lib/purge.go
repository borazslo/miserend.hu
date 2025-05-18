package dumper

import (
	"database/sql"
	"fmt"
)

func (d *Dumper) purgeColumns() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/%s", d.Config.Connection.User, d.Config.Connection.Password, d.Config.Connection.Host, d.Config.Connection.TempDB)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()

	for table, columns := range d.Config.Purge.Columns {
		for _, column := range columns {
			query := fmt.Sprintf("UPDATE `%s` SET `%s` = \"\"", table, column)
			fmt.Println("[SQL]", query)
			if err := runSQL(db, query); err != nil {
				fmt.Printf("Error: %v\n", err)
			}
		}
	}
	return nil
}

func (d *Dumper) purgeTables() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/%s", d.Config.Connection.User, d.Config.Connection.Password, d.Config.Connection.Host, d.Config.Connection.TempDB)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()

	if len(d.Config.Purge.Tables) > 0 {
		for _, table := range d.Config.Purge.Tables {
			query := fmt.Sprintf("DELETE FROM `%s`", table)
			fmt.Println("[SQL]", query)
			if err := runSQL(db, query); err != nil {
				fmt.Printf("Table bulk delete error (%s): %v\n", table, err)
			}
		}
	}
	return nil
}
