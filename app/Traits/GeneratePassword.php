<?php

namespace App\Traits;

trait GeneratePassword
{

    /**
     * Checks if the given password matches a previously computed encoded password, thus validating authentication.
     * The encoded password is usually taken from the password column in the database.
     *
     * @param string $rawPassword The password to validate.
     * @param string $encodedPassword The encoded password hash, which includes salt prefix.
     * @param int $saltPrefixLength The length of the salt prefix of the encoded password. Defaults to 8.
     *
     * @return boolean
     */
    public function matches($rawPassword, $encodedPassword, $saltPrefixLength = 8)
    {
        $decodedDigest = pack('H*', $encodedPassword);
        $salt = substr($decodedDigest, 0, $saltPrefixLength);
        $computedDigest = $this->digest($rawPassword, $salt);
        return $decodedDigest === $computedDigest;
    }

    /**
     * Encodes a password in the same way as Spring Security's StandardPasswordEncoder with default settings.
     *
     * @param $rawPassword string The password to encode.
     * @param $salt string Optional salt used for encoding the password.
     *              For test-proven results use a random length 8 ASCII string. If not specified it will be auto-provided.
     * @return string The encoded password, ready to be stored in the database.
     */
    public function encode($rawPassword, $salt = null)
    {
        if (is_null($salt)) {
            $salt = openssl_random_pseudo_bytes(8);
        }
        $computedDigest = $this->digest($rawPassword, $salt);
        return bin2hex($computedDigest);
    }

    private function digest($rawPassword, $salt)
    {
        $utf8RawPassword = mb_convert_encoding($rawPassword, "UTF-8");
        $digest = $this->sha256Hash($utf8RawPassword, $salt);
        return $salt . $digest;
    }

    private function sha256Hash($password, $salt)
    {
        $password = $salt . $password;
        for ($j = 0; $j < HASH_ITERATIONS; $j++) {
            $password = hash(HASH_ALGORITHM, $password, true);
        }
        return $password ? substr($password, 0, HASH_LENGTH) : $password;
    }
}
