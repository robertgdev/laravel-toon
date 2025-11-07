<?php

use Illuminate\Support\Facades\File;
use RobertGDev\LaravelToon\Console\ToonCommand;

describe('Artisan toon:convert Command', function () {
    beforeEach(function () {
        // Clean up test files
        $this->testDir = storage_path('framework/testing/toon');
        File::deleteDirectory($this->testDir);
        File::makeDirectory($this->testDir, 0755, true);
    });

    afterEach(function () {
        // Clean up after tests
        File::deleteDirectory($this->testDir);
    });

    it('is registered in artisan', function () {
        $commands = \Illuminate\Support\Facades\Artisan::all();
        
        expect($commands)->toHaveKey('toon:convert');
        expect($commands['toon:convert'])->toBeInstanceOf(ToonCommand::class);
    });

    it('encodes JSON to TOON by file extension', function () {
        $inputFile = $this->testDir . '/input.json';
        $outputFile = $this->testDir . '/output.toon';
        
        $jsonData = json_encode(['name' => 'Ada', 'age' => 30]);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
        ])->assertExitCode(0);
        
        expect(file_exists($outputFile))->toBeTrue();
        $content = file_get_contents($outputFile);
        expect($content)->toBe("name: Ada\nage: 30");
    });

    it('decodes TOON to JSON by file extension', function () {
        $inputFile = $this->testDir . '/input.toon';
        $outputFile = $this->testDir . '/output.json';
        
        file_put_contents($inputFile, "name: Ada\nage: 30");
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
        ])->assertExitCode(0);
        
        expect(file_exists($outputFile))->toBeTrue();
        $content = file_get_contents($outputFile);
        $data = json_decode($content, true);
        expect($data)->toBe(['name' => 'Ada', 'age' => 30]);
    });

    it('respects --encode flag', function () {
        $inputFile = $this->testDir . '/data.txt';
        $outputFile = $this->testDir . '/output.toon';
        
        file_put_contents($inputFile, json_encode(['test' => 'value']));
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--encode' => true,
        ])->assertExitCode(0);
        
        $content = file_get_contents($outputFile);
        expect($content)->toBe('test: value');
    });

    it('respects --decode flag', function () {
        $inputFile = $this->testDir . '/data.txt';
        $outputFile = $this->testDir . '/output.json';
        
        file_put_contents($inputFile, 'test: value');
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--decode' => true,
        ])->assertExitCode(0);
        
        $content = file_get_contents($outputFile);
        $data = json_decode($content, true);
        expect($data)->toBe(['test' => 'value']);
    });

    it('uses custom delimiter', function () {
        $inputFile = $this->testDir . '/input.json';
        $outputFile = $this->testDir . '/output.toon';
        
        $jsonData = json_encode(['items' => [1, 2, 3]]);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--delimiter' => "\t",
        ])->assertExitCode(0);
        
        $content = file_get_contents($outputFile);
        expect($content)->toBe("items[3\t]: 1\t2\t3");
    });

    it('uses custom indentation', function () {
        $inputFile = $this->testDir . '/input.json';
        $outputFile = $this->testDir . '/output.toon';
        
        $jsonData = json_encode(['user' => ['name' => 'Ada']]);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--indent' => 4,
        ])->assertExitCode(0);
        
        $content = file_get_contents($outputFile);
        expect($content)->toBe("user:\n    name: Ada"); // 4 spaces
    });

    it('uses length marker when specified', function () {
        $inputFile = $this->testDir . '/input.json';
        $outputFile = $this->testDir . '/output.toon';
        
        $jsonData = json_encode(['items' => [1, 2, 3]]);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--length-marker' => true,
        ])->assertExitCode(0);
        
        $content = file_get_contents($outputFile);
        expect($content)->toBe('items[#3]: 1,2,3');
    });

    it('outputs to stdout when no output file specified', function () {
        $inputFile = $this->testDir . '/input.json';
        
        $jsonData = json_encode(['test' => 'value']);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
        ])->expectsOutput('test: value')
          ->assertExitCode(0);
    });

    it('fails with invalid input file', function () {
        $this->artisan('toon:convert', [
            'input' => $this->testDir . '/nonexistent.json',
            '--output' => $this->testDir . '/output.toon',
        ])->assertExitCode(1);
    });

    it('fails with invalid delimiter', function () {
        $inputFile = $this->testDir . '/input.json';
        file_put_contents($inputFile, json_encode(['test' => 'value']));
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--delimiter' => ';',
        ])->assertExitCode(1);
    });

    it('handles --no-strict flag for decoding', function () {
        $inputFile = $this->testDir . '/input.toon';
        $outputFile = $this->testDir . '/output.json';
        
        // TOON with non-multiple indentation (would fail in strict mode)
        file_put_contents($inputFile, "a:\n   b: 1"); // 3 spaces
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
            '--no-strict' => true,
        ])->assertExitCode(0);
        
        expect(file_exists($outputFile))->toBeTrue();
    });

    it('shows statistics when --stats flag is used', function () {
        $inputFile = $this->testDir . '/input.json';
        
        $jsonData = json_encode(['name' => 'Ada', 'items' => [1, 2, 3]]);
        file_put_contents($inputFile, $jsonData);
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--stats' => true,
        ])->assertExitCode(0);
        
        // Note: Stats output verification would require checking actual output
    });

    it('handles complex nested structures', function () {
        $inputFile = $this->testDir . '/complex.json';
        $outputFile = $this->testDir . '/complex.toon';
        
        $data = [
            'user' => [
                'id' => 123,
                'profile' => [
                    'name' => 'Ada',
                    'tags' => ['coding', 'math']
                ]
            ],
            'items' => [
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second']
            ]
        ];
        
        file_put_contents($inputFile, json_encode($data));
        
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $outputFile,
        ])->assertExitCode(0);
        
        expect(file_exists($outputFile))->toBeTrue();
        $content = file_get_contents($outputFile);
        expect($content)->toContain('user:');
        expect($content)->toContain('items[2]{id,name}:');
    });

    it('handles round-trip conversion', function () {
        $inputFile = $this->testDir . '/original.json';
        $toonFile = $this->testDir . '/encoded.toon';
        $outputFile = $this->testDir . '/decoded.json';
        
        $originalData = ['name' => 'Ada', 'age' => 30, 'items' => [1, 2, 3]];
        file_put_contents($inputFile, json_encode($originalData));
        
        // Encode to TOON
        $this->artisan('toon:convert', [
            'input' => $inputFile,
            '--output' => $toonFile,
        ])->assertExitCode(0);
        
        // Decode back to JSON
        $this->artisan('toon:convert', [
            'input' => $toonFile,
            '--output' => $outputFile,
        ])->assertExitCode(0);
        
        $decodedData = json_decode(file_get_contents($outputFile), true);
        expect($decodedData)->toBe($originalData);
    });
});