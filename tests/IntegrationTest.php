<?php

use RobertGDev\LaravelToon\Facades\Toon;
use RobertGDev\LaravelToon\Toon as ToonService;
use HelgeSverre\Toon\EncodeOptions;
use HelgeSverre\Toon\DecodeOptions;

describe('Laravel Integration', function () {
    it('registers Toon service in container', function () {
        $toon = app('toon');
        
        expect($toon)->toBeInstanceOf(ToonService::class);
    });

    it('provides Toon facade', function () {
        $data = ['name' => 'Ada', 'age' => 30];
        $encoded = Toon::encode($data);
        
        expect($encoded)->toBe("name: Ada\nage: 30");
    });

    it('facade can decode TOON format', function () {
        $toon = "name: Ada\nage: 30";
        $decoded = Toon::decode($toon);
        
        expect($decoded)->toBe(['name' => 'Ada', 'age' => 30]);
    });
});

describe('Configuration Integration', function () {
    it('uses configured encode defaults', function () {
        config(['toon.encode.indent' => 4]);
        config(['toon.encode.delimiter' => "\t"]);
        
        $toon = app('toon');
        $data = [
            'user' => ['name' => 'Ada'],
            'items' => [1, 2, 3]
        ];
        
        $encoded = $toon->encode($data);
        
        expect($encoded)->toContain("items[3\t]: 1\t2\t3");
        expect($encoded)->toContain("user:\n    name: Ada"); // 4 spaces
    });

    it('uses configured decode defaults', function () {
        config(['toon.decode.strict' => false]);
        
        $toon = app('toon');
        $encoded = "user:\n  name: Ada";
        $decoded = $toon->decode($encoded);
        
        expect($decoded)->toBe(['user' => ['name' => 'Ada']]);
    });

    it('allows per-call option overrides', function () {
        config(['toon.encode.indent' => 2]);
        
        $toon = app('toon');
        $data = ['user' => ['name' => 'Ada']];
        
        // Override with custom options
        $options = new EncodeOptions(indent: 4);
        $encoded = $toon->encode($data, $options);
        
        expect($encoded)->toBe("user:\n    name: Ada"); // 4 spaces, not 2
    });

    it('handles length marker configuration', function () {
        config(['toon.encode.lengthMarker' => '#']);
        
        $toon = app('toon');
        $data = ['items' => [1, 2, 3]];
        $encoded = $toon->encode($data);
        
        expect($encoded)->toBe('items[#3]: 1,2,3');
    });

    it('handles false length marker configuration', function () {
        config(['toon.encode.lengthMarker' => false]);
        
        $toon = app('toon');
        $data = ['items' => [1, 2, 3]];
        $encoded = $toon->encode($data);
        
        expect($encoded)->toBe('items[3]: 1,2,3');
    });
});

describe('Service Provider', function () {
    it('merges default configuration', function () {
        expect(config('toon.encode.indent'))->toBe(2);
        expect(config('toon.encode.delimiter'))->toBe(',');
        expect(config('toon.decode.strict'))->toBe(true);
    });

    it('registers console command when running in console', function () {
        $commands = \Illuminate\Support\Facades\Artisan::all();
        
        expect($commands)->toHaveKey('toon:convert');
    });
});

describe('Facade Functionality', function () {
    it('encodes StdClass objects', function () {
        $obj = new StdClass();
        $obj->name = 'Ada';
        $obj->age = 30;
        
        $encoded = Toon::encode($obj);
        
        expect($encoded)->toBe("name: Ada\nage: 30");
    });

    it('decodes to associative array', function () {
        $toon = "name: Ada\nage: 30";
        $decoded = Toon::decode($toon);
        
        expect($decoded)->toBe(['name' => 'Ada', 'age' => 30]);
    });

    it('supports round-trips with associative arrays', function () {
        $original = ['name' => 'Ada', 'items' => [1, 2, 3]];
        
        $encoded = Toon::encode($original);
        $decoded = Toon::decode($encoded);
        $reEncoded = Toon::encode($decoded);
        
        expect($reEncoded)->toBe($encoded);
        expect($decoded)->toBe($original);
    });
});