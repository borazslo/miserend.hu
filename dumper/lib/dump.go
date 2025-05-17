package dumper

import (
	"database/sql"
	"fmt"
	"os"
	"os/exec"
)

func (d *Dumper) dumpDatabase() error {
	outFile, err := os.Create(DUMP_FILE)
	if err != nil {
		return err
	}
	defer outFile.Close()

	cmd := exec.Command("mariadb-dump", "--skip-ssl", "-h", d.Config.Connection.Host, "-u", d.Config.Connection.User, fmt.Sprintf("-p%s", d.Config.Connection.Password), d.Config.Connection.TempDB)
	cmd.Stdout = outFile
	cmd.Stderr = os.Stderr
	return cmd.Run()
}

func (d *Dumper) dropDatabase() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/", d.Config.Connection.User, d.Config.Connection.Password, d.Config.Connection.Host)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()
	return runSQL(db, fmt.Sprintf("DROP DATABASE `%s`", d.Config.Connection.TempDB))
}
