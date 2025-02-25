<?php 

namespace AVB\DevelopmentFramework\Core;

class Logger
{
    const COLOR_RESET = "\033[0m";
    const COLOR_RED = "\033[31m";
    const COLOR_GREEN = "\033[32m";
    const COLOR_YELLOW = "\033[33m";
    const COLOR_BLUE = "\033[34m";
    const COLOR_MAGENTA = "\033[35m";
    const COLOR_CYAN = "\033[36m";
    const COLOR_WHITE = "\033[37m";

    public function log(string $message, string $level = 'default', bool $showTimestamp = true)
    {
        // Map log levels to colors
        $colorMap = [
            'default' => self::COLOR_WHITE,
            'info' => self::COLOR_BLUE,
            'warning' => self::COLOR_YELLOW,
            'error' => self::COLOR_RED,
            'success' => self::COLOR_GREEN,
        ];

        // Format log message with color
        $logMessage = $colorMap[$level] ?? self::COLOR_RESET;
        $logMessage .= $level != 'default' ? '[' . strtoupper($level) . ']' : '';
        if ($showTimestamp) {
            $logMessage .= '[' . date('m-d-Y H:i:s') . '] ';
        }
        $logMessage .= $message . self::COLOR_RESET . PHP_EOL;

        // Log message to command line
        fwrite(STDOUT, $logMessage);
    }

    public function table(array $data)
    {
        if (empty($data)) {
            return;
        }

        // Get column names
        $columns = array_keys(reset($data));

        // Determine column widths
        $columnWidths = [];
        foreach ($columns as $column) {
            $columnWidths[$column] = max(array_map('strlen', array_column($data, $column))) + 2;
        }

        // Output a line break before the table
        $this->log('', 'default', false);

        // Output header row
        $this->log(implode(' | ', $columns), 'default', false);

        // Output separator row
        $this->log(str_repeat('-', array_sum($columnWidths) + count($columns) - 1), 'default', false);

        // Output data rows
        foreach ($data as $row) {
            $rowData = [];
            foreach ($columns as $column) {
                $rowData[] = str_pad($row[$column], $columnWidths[$column]);
            }
            $this->log(implode(' | ', $rowData), 'default', false);
        }

        // Output a line break after the table
        $this->log('', 'default', false);
    }
}