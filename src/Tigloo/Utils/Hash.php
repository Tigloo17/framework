<?php
declare(strict_types=1);

namespace Tigloo\Utils;

use RuntimeException;

final class Hash {

    private $options = [];

    public function __construct()
    {
        $this->options = [
            'cost' => 13,
        ];
    }

    public function hash($value, array $options = [])
    {
        $options = $options += $this->options;
        if (! $hash = password_hash($value, PASSWORD_BCRYPT, $options)) {
            throw new RuntimeException('BCRYPT n\'est pas supporté', 500);
        }

        return $hash;
    }

    public function check($value, $hash)
    {
        if (password_get_info($hash)['algoName'] !== 'bcrypt') {
            throw new RuntimeException('Le password n\'est pas crypté en BCRYPT', 500);
        }

        return password_verify($value, $hash);
    }

    public function needsRehash($hash, array $options = [])
    {
        $options = $options += $this->options;
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
    }
}