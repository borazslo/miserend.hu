package main

import (
	"database/sql"
	"fmt"
	"os"
	"os/exec"

	_ "github.com/go-sql-driver/mysql"
	"gopkg.in/yaml.v3"
)

type Schema struct {
	Columns map[string][]string `yaml:"columns"`
	Tables  []string            `yaml:"tables"`
}

type Dumper struct {
	User     string
	Password string
	Host     string

	SourceDB string
	TempDB   string

	YamlPath string
	DumpFile string
}

func NewDumper() *Dumper {
	return &Dumper{
		User:     os.Getenv("DB_USER"),
		Password: os.Getenv("DB_PASSWORD"),
		Host:     os.Getenv("DB_HOST"),
		SourceDB: os.Getenv("DB_SOURCE"),
		TempDB:   os.Getenv("DB_TEMP"),
		YamlPath: os.Getenv("YAML_PATH"),
		DumpFile: os.Getenv("DUMP_FILE"),
	}
}

func (d *Dumper) loadSchema() (*Schema, error) {
	file, err := os.Open(d.YamlPath)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	var schema Schema
	decoder := yaml.NewDecoder(file)
	if err := decoder.Decode(&schema); err != nil {
		return nil, err
	}
	return &schema, nil
}

func runSQL(db *sql.DB, query string) error {
	_, err := db.Exec(query)
	return err
}

func (d *Dumper) cloneDatabase() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/", d.User, d.Password, d.Host)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()

	if err := runSQL(db, fmt.Sprintf("DROP DATABASE IF EXISTS `%s`", d.TempDB)); err != nil {
		return err
	}
	if err := runSQL(db, fmt.Sprintf("CREATE DATABASE `%s`", d.TempDB)); err != nil {
		return err
	}

	dumpCmd := exec.Command("mysqldump", "--skip-ssl", "-h", d.Host, "-u", d.User, fmt.Sprintf("-p%s", d.Password), d.SourceDB)
	fmt.Println(dumpCmd.String())

	restoreCmd := exec.Command("mysql", "--skip-ssl", "-h", d.Host, "-u", d.User, fmt.Sprintf("-p%s", d.Password), d.TempDB)

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

func (d *Dumper) purgeColumns(schema *Schema) error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/%s", d.User, d.Password, d.Host, d.TempDB)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()

	for table, columns := range schema.Columns {
		for _, column := range columns {
			query := fmt.Sprintf("UPDATE `%s` SET `%s` = \"\"", table, column)
			fmt.Println("[SQL]", query)
			if err := runSQL(db, query); err != nil {
				fmt.Printf("Hiba: %v\n", err)
			}
		}
	}
	return nil
}

func (d *Dumper) dumpDatabase() error {
	outFile, err := os.Create(d.DumpFile)
	if err != nil {
		return err
	}
	defer outFile.Close()

	cmd := exec.Command("mysqldump", "--skip-ssl", "-h", d.Host, "-u", d.User, fmt.Sprintf("-p%s", d.Password), d.TempDB)
	cmd.Stdout = outFile
	cmd.Stderr = os.Stderr
	return cmd.Run()
}

func (d *Dumper) dropDatabase() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s)/", d.User, d.Password, d.Host)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return err
	}
	defer db.Close()
	return runSQL(db, fmt.Sprintf("DROP DATABASE `%s`", d.TempDB))
}

func main() {
	d := NewDumper()

	fmt.Println("[*] YAML loading...")
	schema, err := d.loadSchema()
	if err != nil {
		panic(err)
	}

	fmt.Println("[*] Clone database...")
	if err := d.cloneDatabase(); err != nil {
		panic(err)
	}

	fmt.Println("[*] Delete column data...")
	if err := d.purgeColumns(schema); err != nil {
		panic(err)
	}

	fmt.Println("[*] Create dump...")
	if err := d.dumpDatabase(); err != nil {
		panic(err)
	}

	fmt.Println("[*] Delete cloned dump...")
	if err := d.dropDatabase(); err != nil {
		panic(err)
	}

	fmt.Println("[✓] Done! Dump file:", d.DumpFile)
}
