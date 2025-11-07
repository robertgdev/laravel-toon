<?php

use Illuminate\Support\Facades\File;

describe('Configuration', function () {
    it('has default configuration values', function () {
        expect(config('toon.encode.indent'))->toBe(2);
        expect(config('toon.encode.delimiter'))->toBe(',');
        expect(config('toon.encode.lengthMarker'))->toBe(false);
        expect(config('toon.decode.indent'))->toBe(2);
        expect(config('toon.decode.strict'))->toBe(true);
        expect(config('toon.decode.objectsAsStdClass'))->toBe(false);
    });

    it('can be overridden via config', function () {
        config(['toon.encode.indent' => 4]);
        config(['toon.encode.delimiter' => "\t"]);
        config(['toon.encode.lengthMarker' => '#']);
        
        expect(config('toon.encode.indent'))->toBe(4);
        expect(config('toon.encode.delimiter'))->toBe("\t");
        expect(config('toon.encode.lengthMarker'))->toBe('#');
    });

    it('supports environment variable configuration', function () {
        // Simulate environment variables
        putenv('TOON_ENCODE_INDENT=4');
        putenv('TOON_ENCODE_DELIMITER=|');
        putenv('TOON_DECODE_STRICT=false');
        
        // In a real Laravel app, these would be loaded via config files
        // This test demonstrates the pattern
        $indent = env('TOON_ENCODE_INDENT', 2);
        $delimiter = env('TOON_ENCODE_DELIMITER', ',');
        $strict = env('TOON_DECODE_STRICT', true);
        
        expect($indent)->toBe('4');
        expect($delimiter)->toBe('|');
        expect($strict)->toBe(false); // env() returns false, not string 'false'
        
        // Clean up
        putenv('TOON_ENCODE_INDENT');
        putenv('TOON_ENCODE_DELIMITER');
        putenv('TOON_DECODE_STRICT');
    });
});

describe('Configuration Publishing', function () {
    it('can publish configuration file', function () {
        $configPath = config_path('toon.php');
        
        // Clean up if exists
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
        
        $this->artisan('vendor:publish', [
            '--tag' => 'toon-config',
            '--force' => true,
        ])->assertExitCode(0);
        
        expect(File::exists($configPath))->toBeTrue();
        
        // Verify published file has correct structure
        $config = include $configPath;
        expect($config)->toBeArray();
        expect($config)->toHaveKey('encode');
        expect($config)->toHaveKey('decode');
        expect($config['encode'])->toHaveKey('indent');
        expect($config['decode'])->toHaveKey('objectsAsStdClass');
        
        // Clean up
        File::delete($configPath);
    });
});

describe('Service Provider Features', function () {
    it('registers Toon service as singleton', function () {
        $instance1 = app('toon');
        $instance2 = app('toon');
        
        expect($instance1)->toBe($instance2);
    });

    it('registers ToonCommand when in console', function () {
        expect($this->app->runningInConsole())->toBeTrue();
        
        $commands = \Illuminate\Support\Facades\Artisan::all();
        expect($commands)->toHaveKey('toon:convert');
    });

    it('merges package configuration', function () {
        // Configuration should be available even without publishing
        expect(config('toon'))->not->toBeNull();
        expect(config('toon.encode'))->toBeArray();
        expect(config('toon.decode'))->toBeArray();
    });
});

describe('Toon Service with Config', function () {
    it('applies config defaults when encoding', function () {
        config(['toon.encode.delimiter' => '|']);
        
        $toon = app('toon');
        $data = ['items' => ['a', 'b', 'c']];
        $encoded = $toon->encode($data);
        
        expect($encoded)->toBe('items[3|]: a|b|c');
    });

    it('applies config defaults when decoding', function () {
        config(['toon.decode.objectsAsStdClass' => true]);
        
        $toon = app('toon');
        $toonStr = "name: Ada";
        $decoded = $toon->decode($toonStr);
        
        expect($decoded)->toBeInstanceOf(StdClass::class);
        expect($decoded->name)->toBe('Ada');
    });

    it('allows overriding config with explicit options', function () {
        config(['toon.encode.indent' => 2]);
        
        $toon = app('toon');
        $data = ['user' => ['name' => 'Ada']];
        
        // Override with 4-space indent
        $options = new \RobertGDev\Toon\Types\EncodeOptions(indent: 4);
        $encoded = $toon->encode($data, $options);
        
        expect($encoded)->toBe("user:\n    name: Ada");
    });
});