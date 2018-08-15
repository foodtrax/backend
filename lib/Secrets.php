<?php

/**
 * Read the secrets file
 *
 * @author Christopher Bitler
 */
class Secrets
{
    /**
     * Read the secrets from the secret file
     *
     * @return array The secret keys with their values
     */
    public function readSecrets()
    {
        $contents = file_get_contents('SECRETS');
        $secrets = [];
        foreach (explode(PHP_EOL, $contents) as $line) {
            if (!empty($line)) {
                $lineParts = explode("=", $line, 2);
                $key = $lineParts[0];
                $value = $lineParts[1];
                $secrets[$key] = $value;
            }
        }

        return $secrets;
    }
}