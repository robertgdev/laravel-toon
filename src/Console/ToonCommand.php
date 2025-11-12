<?php

namespace  RobertGDev\LaravelToon\Console;

use Illuminate\Console\Command;
use HelgeSverre\Toon\Toon;
use HelgeSverre\Toon\DecodeOptions;
use HelgeSverre\Toon\EncodeOptions;

class ToonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toon:convert
                            {input : Input file path}
                            {--o|output= : Output file path}
                            {--e|encode : Encode JSON to TOON (auto-detected by default)}
                            {--d|decode : Decode TOON to JSON (auto-detected by default)}
                            {--delimiter=, : Delimiter for arrays: comma (,), tab (\t), or pipe (|)}
                            {--indent=2 : Indentation size}
                            {--length-marker : Use length marker (#) for arrays}
                            {--strict : Enable strict mode for decoding (default: true)}
                            {--no-strict : Disable strict mode for decoding}
                            {--stats : Show token statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert between JSON and TOON formats';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $input = $this->argument('input');
        if (!is_string($input)) {
            $this->error('Input argument is required and must be a string');
            return self::FAILURE;
        }

        $output = $this->option('output');
        $output = is_string($output) || $output === null ? $output : null;

        // Parse and validate indent
        $indentOption = $this->option('indent');
        $indent = is_numeric($indentOption) ? (int)$indentOption : 2;
        if ($indent < 0) {
            $this->error("Invalid indent value: {$indent}");
            return self::FAILURE;
        }

        // Validate delimiter
        $delimiter = $this->option('delimiter');
        if (!is_string($delimiter)) {
            $delimiter = ',';
        }
        $validDelimiters = [',', "\t", '|'];
        if (!in_array($delimiter, $validDelimiters, true)) {
            $this->error("Invalid delimiter \"{$delimiter}\". Valid delimiters are: comma (,), tab (\\t), pipe (|)");
            return self::FAILURE;
        }

        $encode = (bool)$this->option('encode');
        $decode = (bool)$this->option('decode');

        $mode = $this->detectMode($input, $encode, $decode);

        try {
            if ($mode === 'encode') {
                $lengthMarker = (bool)$this->option('length-marker');
                $stats        = (bool)$this->option('stats');

                $this->encodeToToon($input, $output, $delimiter, $indent, $lengthMarker, $stats);
            } else {
                $this->decodeToJson($input, $output, $indent, !$this->option('no-strict'));
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Detect conversion mode based on flags and file extension.
     */
    private function detectMode(?string $inputFile, bool $encodeFlag, bool $decodeFlag): string
    {
        // Explicit flags take precedence
        if ($encodeFlag) {
            return 'encode';
        }
        if ($decodeFlag) {
            return 'decode';
        }

        // Auto-detect based on file extension
        if ($inputFile !== null && str_ends_with($inputFile, '.json')) {
            return 'encode';
        }
        if ($inputFile !== null && str_ends_with($inputFile, '.toon')) {
            return 'decode';
        }

        // Default to encode
        return 'encode';
    }

    /**
     * Encode JSON to TOON format.
     */
    private function encodeToToon(
        string $input,
        ?string $output,
        string $delimiter,
        int $indent,
        bool $lengthMarker,
        bool $printStats
    ): void {
        if (!file_exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $jsonContent = file_get_contents($input);
        if ($jsonContent === false) {
            throw new \RuntimeException("Failed to read input file: {$input}");
        }
        
        try {
            $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Failed to parse JSON: {$e->getMessage()}");
        }

        $encodeOptions = new EncodeOptions(
            indent: $indent,
            delimiter: $delimiter,
            lengthMarker: $lengthMarker ? '#' : false
        );

        $toonOutput = Toon::encode($data, $encodeOptions);

        if ($output) {
            file_put_contents($output, $toonOutput);
            $relativeInput = $this->getRelativePath($input);
            $relativeOutput = $this->getRelativePath($output);
            $this->info("✓ Encoded `{$relativeInput}` → `{$relativeOutput}`");
        } else {
            $this->line($toonOutput);
        }

        if ($printStats) {
            $jsonTokens = $this->estimateTokenCount($jsonContent);
            $toonTokens = $this->estimateTokenCount($toonOutput);
            $diff = $jsonTokens - $toonTokens;
            $percent = number_format(($diff / $jsonTokens) * 100, 1);

            $this->newLine();
            $this->info("Token estimates: ~{$jsonTokens} (JSON) → ~{$toonTokens} (TOON)");
            $this->info("✓ Saved ~{$diff} tokens (-{$percent}%)");
        }
    }

    /**
     * Decode TOON to JSON format.
     */
    private function decodeToJson(
        string $input,
        ?string $output,
        int $indent,
        bool $strict
    ): void {
        if (!file_exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $toonContent = file_get_contents($input);
        if ($toonContent === false) {
            throw new \RuntimeException("Failed to read input file: {$input}");
        }

        try {
            $decodeOptions = new DecodeOptions(
                indent: $indent,
                strict: $strict
            );
            $data = Toon::decode($toonContent, $decodeOptions);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to decode TOON: {$e->getMessage()}");
        }

        $jsonOutput = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE, 512);
        if ($jsonOutput === false) {
            throw new \RuntimeException("Failed to encode JSON output");
        }

        if ($output) {
            file_put_contents($output, $jsonOutput);
            $relativeInput = $this->getRelativePath($input);
            $relativeOutput = $this->getRelativePath($output);
            $this->info("✓ Decoded `{$relativeInput}` → `{$relativeOutput}`");
        } else {
            $this->line($jsonOutput);
        }
    }

    /**
     * Get relative path from current working directory.
     */
    private function getRelativePath(string $path): string
    {
        $cwd = getcwd();
        if ($cwd !== false && str_starts_with($path, $cwd)) {
            return substr($path, strlen($cwd) + 1);
        }
        return $path;
    }

    /**
     * Estimate token count for a string (simple approximation).
     * This is a simplified version - for production use, consider using a proper tokenizer.
     */
    private function estimateTokenCount(string $text): int
    {
        // Simple approximation: ~4 characters per token on average
        // This is a rough estimate similar to what tokenx does
        return (int) ceil(strlen($text) / 4);
    }
}