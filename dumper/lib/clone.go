package dumper

import (
	"database/sql"
	"fmt"
	"os/exec"
)

func (d *Dumper) cloneDatabase() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/", d.Config.Connection.User, d.Config.Connection.Password, d.Config.Connection.Host)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()

	if err := runSQL(db, fmt.Sprintf("DROP DATABASE IF EXISTS `%s`", d.Config.Connection.TempDB)); err != nil {
		return err
	}
	if err := runSQL(db, fmt.Sprintf("CREATE DATABASE `%s`", d.Config.Connection.TempDB)); err != nil {
		return err
	}

	dumpCmd := exec.Command("mariadb-dump", "--skip-ssl", "-h", d.Config.Connection.Host, "-u", d.Config.Connection.User, fmt.Sprintf("-p%s", d.Config.Connection.Password), d.Config.Connection.SourceDB)

	restoreCmd := exec.Command("mysql", "--skip-ssl", "-h", d.Config.Connection.Host, "-u", d.Config.Connection.User, fmt.Sprintf("-p%s", d.Config.Connection.Password), d.Config.Connection.TempDB)

	reader, err := dumpCmd.StdoutPipe()
	if err != nil {
		return err
	}
	restoreCmd.Stdin = reader

	if err := dumpCmd.Start(); err != nil {
		return err
	}
	if err := restoreCmd.Start(); err != nil {
		return err
	}

	if err := dumpCmd.Wait(); err != nil {
		fmt.Println(err)
		return err
	}
	return restoreCmd.Wait()
}
