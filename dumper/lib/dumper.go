package dumper

import (
	"fmt"
)

type Dumper struct {
	Config *Config
}

const CONFIG_FILE = "config.yaml"
const DUMP_FILE = "/app/01-dump.sql"

func NewDumper(config *Config) *Dumper {
	return &Dumper{
		Config: config,
	}
}

func Run() {

	fmt.Println("[*] Load configuration...")
	config, err := loadConfig()
	if err != nil {
		panic(err)
	}

	d := NewDumper(config)

	fmt.Println("[*] Clone database...")
	if err := d.cloneDatabase(); err != nil {
		panic(err)
	}

	fmt.Println("[*] Delete column data...")
	if err := d.purgeColumns(); err != nil {
		panic(err)
	}

	fmt.Println("[*] Delete table data...")
	if err := d.purgeTables(); err != nil {
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

	fmt.Println("[✓] Done! Dump file:", DUMP_FILE)
}
