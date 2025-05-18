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
	Purge      PurgeConfig
	Connection ConnectionConfig
}

type ConfigFile struct {
	Purge PurgeConfig `yaml:"purge"`
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

	var config_file ConfigFile
	decoder := yaml.NewDecoder(file)
	if err := decoder.Decode(&config_file); err != nil {
		return nil, err
	}
	return &Config{
		Purge: config_file.Purge,
		Connection: ConnectionConfig{
			User:     os.Getenv("USER"),
			Password: os.Getenv("PASSWORD"),
			Host:     os.Getenv("HOST"),
			SourceDB: os.Getenv("SOURCE_DB"),
			TempDB:   os.Getenv("TEMP_DB"),
		},
	}, nil
}
