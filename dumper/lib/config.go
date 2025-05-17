package dumper

import (
	"os"

	"path/filepath"

	"gopkg.in/yaml.v3"
)

type PurgeConfig struct {
	Columns map[string][]string `yaml:"columns"`
	Tables  []string            `yaml:"tables"`
}

type ConnectionConfig struct {
	User     string `yaml:"user"`
	Password string `yaml:"password"`
	Host     string `yaml:"host"`

	SourceDB string `yaml:"source_db"`
	TempDB   string `yaml:"temp_db"`
}

type Config struct {
	Purge      PurgeConfig      `yaml:"purge"`
	Connection ConnectionConfig `yaml:"connection"`
}

func loadConfig() (*Config, error) {
	absPath, err := filepath.Abs(CONFIG_FILE)
	if err != nil {
		return nil, err
	}

	file, err := os.Open(absPath)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	var config Config
	decoder := yaml.NewDecoder(file)
	if err := decoder.Decode(&config); err != nil {
		return nil, err
	}
	return &config, nil
}
