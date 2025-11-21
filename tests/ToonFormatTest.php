<?php

use Datasets\UserDTO;
use HelgeSverre\Toon\EncodeOptions;

describe('converts to TOON format using Trait', function () {
    it('using default encoding options', function () {
        $userDTO = new UserDTO(
            name: 'John Doe',
            age: 30,
            roles: ['admin', 'owner'],
        );

        $result = $userDTO->toToon();

        expect($result)->toBe("name: John Doe\nage: 30\nroles[2]: admin,owner");
    });

    it('using custom encoding options', function () {
        $userDTO = new UserDTO(
            name: 'John Doe',
            age: 30,
            roles: ['admin', 'owner'],
        );

        $options = new EncodeOptions(delimiter: '|');
        $result = $userDTO->toToon($options);

        expect($result)->toBe("name: John Doe\nage: 30\nroles[2|]: admin|owner");
    });
});
